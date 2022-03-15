<?php

require_once 'Framework/Model.php';
require_once 'Framework/Errors.php';
require_once 'Modules/invoices/Model/InvoiceModel.php';

require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';

require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BookinginvoiceTranslator.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreTranslator.php';


/**
 * Manage invoices for bookings
 *
 * @author Sylvain Prigent
 */
class BookingInvoice extends InvoiceModel {

    public static string $INVOICES_BOOKING_ALL = 'invoices_booking_all';
    public static string $INVOICES_BOOKING_CLIENT = 'invoices_booking_client';


    public function hasActivity($id_space, $beginPeriod, $endPeriod, $id_resp){
        if($beginPeriod == "") {
            throw new PfmParamException("invalid begin period");
        }
        if($endPeriod == "") {
            throw new PfmParamException("invalid end period");
        }
        $beginArray = explode("-", $beginPeriod);
        $startPeriodeTime = mktime(0, 0, 0, $beginArray[1], $beginArray[2], $beginArray[0]);

        $endArray = explode("-", $endPeriod);
        $endPeriodeTime = mktime(0, 0, 0, $endArray[1], $endArray[2], $endArray[0]);

        $sql = "SELECT id FROM bk_calendar_entry WHERE responsible_id=? AND start_time>=? AND start_time<=? AND deleted=0 AND id_space=? AND invoice_id=0";
        $req = $this->runRequest($sql, array($id_resp, $startPeriodeTime, $endPeriodeTime, $id_space));
        if ( $req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function invoiceAll($id_space, $beginPeriod, $endPeriod, $id_user, $lang='en') {
        $modelClient = new ClClient();
        $resps = $modelClient->getAll($id_space);
        
        $doBill = false;
        foreach ($resps as $resp) {
            
            $found = $this->invoiceClient($id_space, $beginPeriod, $endPeriod, intval($resp['id']), $id_user, $lang);
            if($found) {
                $doBill = true;
            }
        }
        if(!$doBill) {
            Configuration::getLogger()->debug('[invoice][booking][all] nothing to do');
            return false;
        }
        return true;
    }

    public function invoiceClient($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang='en') {
        Configuration::getLogger()->debug('[invoice][booking][all] create invoice', ['client' => $id_client, 'space' => $id_space]);
        $modelCal = new BkCalendarEntry();
        $found = $modelCal->hasResponsibleEntry($id_space, $id_client, $beginPeriod, $endPeriod);
        if(!$found) {
            return false;
        }

        $modelInvoice = new InInvoice();
        $number = $modelInvoice->getNextNumber($id_space);
        $module = "booking";
        $controller = "Bookinginvoice";
        $date_generated = date("Y-m-d", time());
        $id_resp = intval($id_client);
        $invoice_id = $modelInvoice->addInvoice($module, $controller, $id_space, 'in progress', $date_generated, $id_resp, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setTitle($id_space, $invoice_id, BookingTranslator::MAD($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));
        $modelInvoice->setEditedBy($id_space, $invoice_id, $id_user);
        try {
            $contentAll = $this->invoice($id_space, $beginPeriod, $endPeriod, $id_client, $invoice_id, $lang);
        } catch(Exception $e) {
            $modelInvoice->setNumber($id_space, $invoice_id, 'error');
            throw $e;
        }
        $modelInvoiceItem = new InInvoiceItem();
        $content = '';
        foreach($contentAll['count'] as $c) {
            $content .= $c['content'];
        }
        $details = BookinginvoiceTranslator::Details($lang) . "=" . "bookinginvoicedetail/" . $id_space . "/" . $invoice_id;
        $modelInvoiceItem->setItem($id_space, 0, $invoice_id, $module, $controller, $content, $details, $contentAll['total_ht']);
        $modelInvoice->setTotal($id_space, $invoice_id, $contentAll['total_ht']);
        $modelInvoice->setNumber($id_space, $invoice_id, $number);
        return true;
    }

    /**
     * Generate an invoice for the chosen period.
     * 
     * To be noticed:
     * For each reservation, calculate its price (using $this->calculateTimeResDayNightWe()).
     * 2 general cases:
     * - resource booked price depends on duration, then it costs Unit price * reservation duration (in hours)
     * - resource booked price depends on a quantity of elements, then it costs Unit price * nb elements (quantity)
     * 
     * Unit prices can depend on the period booked (day, night, week-end)
     * 
     * Specific case:
     * - if a reservation depending on quantity of elements is stradding 2 types of period (i.e. night and day),
     * then applies a ratio.
     * example:
     * for a reservation covering 2 night hours and 6 day hours => nightRatio = 0.25 and dayRatio = 0.75
     * if nightPrice = 20 / element and dayPrice = 10 / element,
     * total price = nbElements * (nightPrice * nightRatio) + nbElements * (dayPrice * dayRatio)
     * 
     */
    public function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang) {
        // get all resources
        Configuration::getLogger()->debug('[invoice][booking] create invoice', ['client' => $id_resp, 'space' => $id_space]);

        $modelClient = new ClClient();
        $LABpricingid = $modelClient->getPricingID($id_space, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] get pricing for client', ['client' => $id_resp, 'id' => $LABpricingid]);
        $modelResouces = new ResourceInfo();
        $resources = $modelResouces->getBySpace($id_space);

        // get the pricing
        $timePrices = $this->getUnitTimePricesForEachResource($id_space, $resources, $LABpricingid, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] time prices', ['client' => $id_resp, 'id' => $LABpricingid, 'prices' => $timePrices]);
        $packagesPrices = $this->getUnitPackagePricesForEachResource($id_space, $resources, $LABpricingid, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] packages prices', ['client' => $id_resp, 'id' => $LABpricingid, 'prices' => $timePrices]);

        // get all the reservations for each resources
        $content = ['count' => []];
        $total_ht = 0;
        $modelCal = new BkCalendarEntry();
        $bkCalQuantitiesModel = new BkCalQuantities();
        $modelPackage = new BkPackage();
        foreach ($resources as $res) {
            $reservations = $modelCal->getUnpricedReservations($id_space, $beginPeriod, $endPeriod, $res["id"], $id_resp);

            // get list of quantities
            $calQuantities = $bkCalQuantitiesModel->calQuantitiesByResource($id_space, $res["id"]);
            $calQuantities = ($calQuantities != null) ? $calQuantities : [];

            // tell if there's an invoicing unit for this resource amongst quantities and get its ID
            $isInvoicingUnit = false;
            $calQuantityId = "";
            foreach ($calQuantities as $calQuantity) {
                if ($calQuantity["is_invoicing_unit"] && intval($calQuantity["is_invoicing_unit"]) === 1) {
                    $calQuantityId = $calQuantity["id"];
                    $isInvoicingUnit = true;
                }
            }

            // get all packages
            $userPackages = array();
            foreach ($packagesPrices[$res["id"]] as $p) {
                $userPackages[$p["id"]] = 0;
            }

            $userTime = array();
            $userTime["nb_hours_day"] = 0;
            $userTime["nb_hours_night"] = 0;
            $userTime["nb_hours_we"] = 0;
            $userTime["ratio_bookings_day"] = 0;
            $userTime["ratio_bookings_night"] = 0;
            $userTime["ratio_bookings_we"] = 0;

            $userTime["dayQte"] = 0;
            $userTime["nightQte"] = 0;
            $userTime["weQte"] = 0;

            // TODO: not used for the moment, memory impact to consider depending number of elements
            // $userDetails = [];

            $totalQte = 0; // $totalQte = total number of items booked

            foreach ($reservations as $reservation) {

                // count: day night we, packages
                if ($reservation["package_id"] > 0) {
                    $userPackages[$reservation["package_id"]] ++;
                    /*
                    $userDetails[] = [
                        'id' => $reservation['id'],
                        'start_time' => $reservation['start_time'],
                        'end_time' => $reservation['end_time'],
                        'nb_hours_day' => 1,
                        'nb_hours_night' => 0,
                        'nb_hours_we' => 0,
                        'resource' => $reservation['resource_id'],
                        'user' => $reservation['recipient_id']
                    ];
                    */
                } else {
                    if(!$timePrices || !array_key_exists($res['id'], $timePrices)) {
                        Configuration::getLogger()->debug("[booking][invoice] calculate error, no timePrices", ["timePrices" => $timePrices, "reservation" => $reservation]);
                        throw new PfmParamException("No time pricing defined!");
                    }
                    $slots = $modelCal->computeDuration($id_space, $reservation);
                    $resaDayNightWe = $slots['hours'];
                    // $resaDayNightWe = $this->calculateTimeResDayNightWe($reservation, $timePrices[$res["id"]]);
                    Configuration::getLogger()->debug('[invoice][booking] night and week ends', ['resource' => $res['id'], 'count' => $resaDayNightWe]);

                    if ($isInvoicingUnit) {
                        if ($reservation["quantities"] && $reservation["quantities"] != null) {
                            // varchar formatted like "$mandatory=$quantity;" in bk_calendar_entry
                            // get number of resources booked
                            $strToFind = strval($calQuantityId) . "=";
                            $lastPos = 0;
                            $positions = array();
                            while(($lastPos = strpos($reservation["quantities"], $strToFind, $lastPos))!==false) {
                                $positions[] = $lastPos;
                                $lastPos = $lastPos + strlen($strToFind);
                            }
                            $foundStr = substr($reservation["quantities"], $positions[0]);
                            $qte = intval(explode("=", $foundStr)[1]);
                        } else {
                            $qte = 0;
                        }
                        $totalQte += $qte;

                        // get ratios of this reservation quantity to invoice at night, day or we price
                        $tmpDayQte = $qte * $resaDayNightWe["ratio_bookings_day"];
                        $tmpNightQte = $qte * $resaDayNightWe["ratio_bookings_night"];
                        $tmpWeQte = $qte * $resaDayNightWe["ratio_bookings_we"];

                        $userTime["dayQte"] += $tmpDayQte;
                        $userTime["nightQte"] += $tmpNightQte;
                        $userTime["weQte"] += $tmpWeQte;
                        /*
                        $userDetails[] = [
                            'id' => $reservation['id'],
                            'start_time' => $reservation['start_time'],
                            'end_time' => $reservation['end_time'],
                            'nb_hours_day' => $tmpDayQte,
                            'nb_hours_night' => $tmpNightQte,
                            'nb_hours_we' => $tmpWeQte,
                            'resource' => $reservation['resource_id'],
                            'user' => $reservation['recipient_id']
                        ];
                        */
                        
                    } else {
                        $userTime["nb_hours_day"] += $resaDayNightWe["nb_hours_day"];
                        $userTime["nb_hours_night"] += $resaDayNightWe["nb_hours_night"];
                        $userTime["nb_hours_we"] += $resaDayNightWe["nb_hours_we"];
                        /*
                        $userDetails[] = [
                            'id' => $reservation['id'],
                            'start_time' => $reservation['start_time'],
                            'end_time' => $reservation['end_time'],
                            'nb_hours_day' => $resaDayNightWe["nb_hours_day"],
                            'nb_hours_night' => $resaDayNightWe["nb_hours_night"],
                            'nb_hours_we' => $resaDayNightWe["nb_hours_we"],
                            'resource' => $reservation['resource_id'],
                            'user' => $reservation['recipient_id']
                        ];
                        */
                    }                    
                }

                $modelCal->setReservationInvoice($id_space, $reservation["id"], $invoice_id);
            }

            // fill content
            $resourceCount = array();
            if (count($reservations) > 0) {
                $resourceCount["resource"] = $res["id"];
                if ($userTime["nb_hours_day"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::Day($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_day"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_day"];
                    $resourceCount["content"] = $res["id"] . "_day=" . $resourceCount['quantity'] . "=" . $resourceCount['unitprice'] . ";";
                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;

                }
                if ($userTime["nb_hours_night"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::night($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_night"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_night"];
                    $resourceCount["content"] = $res["id"] . "_night=" . $resourceCount['quantity'] . "=" . $resourceCount['unitprice'] . ";";

                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;

                }
                if ($userTime["nb_hours_we"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::WE($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_we"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_we"];
                    $resourceCount["content"] = $res["id"] . "_we=" . $resourceCount['quantity'] . "=" . $resourceCount['unitprice'] . ";";

                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;
                }
                /*
                foreach ($userDetails as $resaDetails) {
                    $content["details"][] = $resaDetails;
                }
                */
                
                foreach ($packagesPrices[$res["id"]] as $p) {
                    if ($userPackages[$p["id"]] > 0) {

                        $resourceCount["label"] = $res["name"] . " " . $modelPackage->getName($id_space, $p["id"] );
                        $resourceCount["quantity"] = $userPackages[$p["id"]];
                        $resourceCount["unitprice"] = $p["price"];
                        $resourceCount["content"] = $res["id"] . "_pk_" . $p['id'] .'=' . $resourceCount['quantity'] . "=" . $resourceCount['unitprice'] . ";";
                        $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                        $content["count"][] = $resourceCount;

                    }
                }

                // manage quantity
                if ($totalQte > 0) {
                    if ($userTime["dayQte"] > 0) {
                        $dayQte = $userTime["dayQte"];
                        $resourceCount["label"] = $res["name"] . " " . BookingTranslator::Day($lang);
                        $resourceCount["quantity"] = $dayQte;
                        $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_day"];
                        $resourceCount["content"] = $res["id"] . "_day=" . $resourceCount["quantity"] . "=" . $resourceCount["unitprice"] . ";";
                        $total_ht += floatval($dayQte) * floatval($timePrices[$res["id"]]["price_day"]);
                        $content["count"][] = $resourceCount;
                    }
                    if ($userTime["nightQte"] > 0) {
                        $nightQte = $userTime["nightQte"];
                        $resourceCount["label"] = $res["name"] . " " . BookingTranslator::night($lang);
                        $resourceCount["quantity"] = $nightQte;
                        $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_night"];
                        $resourceCount["content"] = $res["id"] . "_night=" . $resourceCount["quantity"] . "=" . $resourceCount["unitprice"] . ";";
                        $total_ht += floatval($nightQte) * floatval($timePrices[$res["id"]]["price_night"]);
                        $content["count"][] = $resourceCount;
                    }
                    if ($userTime["weQte"] > 0) {
                        $weQte = $userTime["weQte"];
                        $resourceCount["label"] = $res["name"] . " " . BookingTranslator::WE($lang);
                        $resourceCount["quantity"] = $weQte;
                        $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_we"];
                        $resourceCount["content"] = $res["id"] . "_we=" . $resourceCount["quantity"] . "=" . $resourceCount["unitprice"] . ";";
                        $total_ht += floatval($weQte) * floatval($timePrices[$res["id"]]["price_we"]);
                        $content["count"][] = $resourceCount;
                    }
                }
            }

        }
        $content["total_ht"] = $total_ht;

        return $content;


    }

    public function delete($id_space, $id_invoice){
        // get items
        require_once 'Modules/booking/Model/BkCalendarEntry.php';
        $model = new BkCalendarEntry();
        $services = $model->getInvoiceEntries($id_space, $id_invoice);
        foreach ($services as $s) {
            $model->setReservationInvoice($id_space, $s["id"], 0);
        }
    }

    public function details($id_space, $invoice_id, $lang){

        // all users in the invoice
        $sql = "SELECT DISTINCT recipient_id FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        $users = $this->runRequest($sql, array($invoice_id, $id_space))->fetchAll();

        // all resources in the invoice
        $sqlr = "SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        $resources = $this->runRequest($sqlr, array($invoice_id, $id_space))->fetchAll();



        $modelResource = new ResourceInfo();
        $modelUser = new CoreUser();

        $data = array();
        $data["title"] = BookingTranslator::MAD($lang);
        $data["header"] = array(
            "label" => BookingTranslator::Resource($lang),
            "user" => CoreTranslator::User($lang),
            "date" => CoreTranslator::Date($lang),
            "quantity" => BookingTranslator::Quantities($lang)
        );
        $data["content"] = array();

        foreach($resources as $resource){
            $resouceName = $modelResource->getName($id_space, $resource[0]);

            foreach($users as $user){
                $sql = "SELECT * FROM bk_calendar_entry WHERE invoice_id=? AND recipient_id=? AND resource_id=? AND deleted=0 AND id_space=? ORDER BY start_time ASC";
                $resreq = $this->runRequest($sql, array($invoice_id, $user[0], $resource[0], $id_space));
                if ($resreq->rowCount() > 0){
                    $reservations = $resreq->fetchAll();
                    $time = 0;
                    foreach($reservations as $res){
                        $time += $res["end_time"] - $res["start_time"];
                    }
                    $data["content"][] = array(
                        "label" => $resouceName,
                        "user" => $modelUser->getUserFUllName($user[0]),
                        "date" => CoreTranslator::dateFromEn(date("Y-m-d", $reservations[0]["start_time"]), $lang) . " - " .
                                  CoreTranslator::dateFromEn(date("Y-m-d", $reservations[count($reservations)-1]["start_time"]), $lang),
                        "quantity" => $time/3600
                        );
                }


            }
        }
        return $data;
    }

    protected function getUnitTimePricesForEachResource(int $id_space, array $resources, ?int $LABpricingid, $id_client) {
        // get the pricing informations
        $pricingModel = new BkNightWE();
        $pricingInfo = $pricingModel->getPricing($LABpricingid, $id_space);
        if (!empty($pricingInfo)) {
            $tarif_unique = $pricingInfo['tarif_unique'];
            $tarif_nuit = $pricingInfo['tarif_night'];
            $tarif_we = $pricingInfo['tarif_we'];
            $night_start = $pricingInfo['night_start'];
            $night_end = $pricingInfo['night_end'];
            $we_array1 = explode(",", $pricingInfo['choice_we']);
            $we_array = array();
            for ($s = 0; $s < count($we_array1); $s++) {
                if ($we_array1[$s] > 0) {
                    $we_array[] = $s + 1;
                }
            }
        } else {
            // Set default values for invoice generation
            $tarif_unique = 1;
            $tarif_nuit = 0;
            $tarif_we = 0;
            $night_start = 19;
            $night_end = 8;
            $we_array = array(0, 0, 0, 0, 0, 1, 1);

            // Insert default values in bk_nightwe table
            $bkNightWeModel = new BkNightWE();
            $we_char = "";
            foreach ($we_array as $day) {
                $we_char .= $day . ",";
            }
            $we_char = substr($we_char, 0, -1);
            $bkNightWeModel->addPricing(
                $LABpricingid,
                $id_space,
                $tarif_unique,
                $tarif_nuit,
                $night_start,
                $night_end,
                $tarif_we,
                $we_char
            );
            $we_array = [6, 7];
        }

        $timePrices = array();
        $modelRessourcePricing = new BkPrice();
        $modelRessourcePricingOwner = new BkOwnerPrice();
        foreach ($resources as $resource) {
            // get the time prices

            $timePrices[$resource["id"]]["tarif_unique"] = $tarif_unique;
            $timePrices[$resource["id"]]["tarif_night"] = $tarif_nuit;
            $timePrices[$resource["id"]]["tarif_we"] = $tarif_we;
            $timePrices[$resource["id"]]["night_end"] = $night_end;
            $timePrices[$resource["id"]]["night_start"] = $night_start;
            $timePrices[$resource["id"]]["we_array"] = $we_array;

            $pday = $modelRessourcePricingOwner->getDayPrice($id_space ,$resource["id"], $id_client);
            if ($pday >= 0) {
                $timePrices[$resource["id"]]["price_day"] = $pday;
            } else {
                $timePrices[$resource["id"]]["price_day"] = $modelRessourcePricing->getDayPrice($id_space, $resource["id"], $LABpricingid); //Tarif jour pour l'utilisateur selectionne
            }

            $pnight = $modelRessourcePricingOwner->getNightPrice($id_space, $resource["id"], $id_client);
            if ($pnight >= 0) {
                $timePrices[$resource["id"]]["price_night"] = $pnight;
            } else {
                $timePrices[$resource["id"]]["price_night"] = $modelRessourcePricing->getNightPrice($id_space, $resource["id"], $LABpricingid); //Tarif nuit pour l'utilisateur selectionne
            }

            $pwe = $modelRessourcePricingOwner->getWePrice($id_space, $resource["id"], $id_client);
            if ($pwe >= 0) {
                $timePrices[$resource["id"]]["price_we"] = $pwe;
            } else {
                $timePrices[$resource["id"]]["price_we"] = $modelRessourcePricing->getWePrice($id_space, $resource["id"], $LABpricingid);  //Tarif w-e pour l'utilisateur selectionne
            }
        }
        return $timePrices;
    }

    protected function getUnitPackagePricesForEachResource($id_space, $resources, $LABpricingid, $id_client) {

        // calculate the reservations for each equipments
        $packagesPrices = array();
        $modelPackage = new BkPackage();
        $modelPrice = new BkPrice();
        $modelPriceOwner = new BkOwnerPrice();
        foreach ($resources as $resource) {
            // get the packages prices
            $packages = $modelPackage->getByResource($id_space ,$resource["id"]);

            $pricesPackages = array();
            for ($i = 0; $i < count($packages); $i++) {
                $price = $modelPriceOwner->getPackagePrice($id_space, $packages[$i]["id"], $resource["id"], $id_client);
                if ($price >= 0) {
                    $packages[$i]["price"] = $price;
                } else {
                    $packages[$i]["price"] = $modelPrice->getPackagePrice($id_space, $packages[$i]["id"], $resource["id"], $LABpricingid);
                }
                $pricesPackages[] = $packages[$i];
            }
            $packagesPrices[$resource["id"]] = $pricesPackages;
        }
        return $packagesPrices;
    }

     protected function calculateTimeResDayNightWe($reservation, $timePrices) {

        // initialize output
        $nb_hours_day = 0;
        $nb_hours_night = 0;
        $nb_hours_we = 0;

        // extract some variables
        if(!$timePrices) {
            Configuration::getLogger()->debug("[booking][invoice] calculate error, no timePrices", ["timePrices" => $timePrices, "reservation" => $reservation]);
            throw new PfmParamException("No time pricing defined!");
        }
        $we_array = $timePrices["we_array"];
        $night_start = $timePrices['night_start'];
        $night_end = $timePrices['night_end'];

        $searchDate_start = $reservation["start_time"];
        $searchDate_end = $reservation["end_time"];

        // calulate pricing
        if (intval($timePrices["tarif_unique"]) > 0) { // unique pricing
            $nb_hours_day = ($searchDate_end - $searchDate_start);
        } else {
            $gap = 60;
            $timeStep = $searchDate_start;
            while ($timeStep <= $searchDate_end) {
                // test if pricing is we
                if ($timePrices['tarif_we']  && in_array(date("N", $timeStep), $we_array) && in_array(date("N", $timeStep + $gap), $we_array)) {  // we pricing
                    $nb_hours_we += $gap;
                } else {
                    $H = date("H", $timeStep);

                    if (!$timePrices['tarif_night'] || ($H >= $night_end && $H < $night_start)) { // price day
                        $nb_hours_day += $gap;
                    } else { // price night
                        $nb_hours_night += $gap;
                    }
                }
                $timeStep += $gap;
            }
        }

        $resaDayNightWe["nb_hours_day"] = round($nb_hours_day / 3600, 1);
        $resaDayNightWe["nb_hours_night"] = round($nb_hours_night / 3600, 1);
        $resaDayNightWe["nb_hours_we"] = round($nb_hours_we / 3600, 1);

        // manage cases where a booking is between day and night hours => get a ratio
        $totalHours = $nb_hours_day + $nb_hours_night + $nb_hours_we;
        $resaDayNightWe["ratio_bookings_day"] = round($nb_hours_day / $totalHours, 2);
        $resaDayNightWe["ratio_bookings_night"] = round($nb_hours_night / $totalHours, 2);
        $resaDayNightWe["ratio_bookings_we"] = round($nb_hours_we / $totalHours, 2);

        return $resaDayNightWe;
    }

}
