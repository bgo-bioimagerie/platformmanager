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
class BookingInvoice extends InvoiceModel
{
    public static string $INVOICES_BOOKING_ALL = 'invoices_booking_all';
    public static string $INVOICES_BOOKING_CLIENT = 'invoices_booking_client';


    public function hasActivity($idSpace, $beginPeriod, $endPeriod, $id_resp)
    {
        if ($beginPeriod == "") {
            throw new PfmParamException("invalid begin period");
        }
        if ($endPeriod == "") {
            throw new PfmParamException("invalid end period");
        }
        $beginArray = explode("-", $beginPeriod);
        $startPeriodeTime = mktime(0, 0, 0, $beginArray[1], $beginArray[2], $beginArray[0]);

        $endArray = explode("-", $endPeriod);
        $endPeriodeTime = mktime(0, 0, 0, $endArray[1], $endArray[2], $endArray[0]);

        $sql = "SELECT id FROM bk_calendar_entry WHERE responsible_id=? AND start_time>=? AND start_time<=? AND deleted=0 AND id_space=? AND invoice_id=0";
        $req = $this->runRequest($sql, array($id_resp, $startPeriodeTime, $endPeriodeTime, $idSpace));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function invoiceAll($idSpace, $beginPeriod, $endPeriod, $idUser, $lang='en')
    {
        $modelClient = new ClClient();
        $resps = $modelClient->getAll($idSpace);

        $doBill = false;
        foreach ($resps as $resp) {
            $found = $this->invoiceClient($idSpace, $beginPeriod, $endPeriod, intval($resp['id']), $idUser, $lang);
            if ($found) {
                $doBill = true;
            }
        }
        if (!$doBill) {
            Configuration::getLogger()->debug('[invoice][booking][all] nothing to do');
            return false;
        }
        return true;
    }

    public function invoiceClient($idSpace, $beginPeriod, $endPeriod, $id_client, $idUser, $lang='en')
    {
        Configuration::getLogger()->debug('[invoice][booking][all] create invoice', ['client' => $id_client, 'space' => $idSpace]);
        $modelCal = new BkCalendarEntry();
        $found = $modelCal->hasResponsibleEntry($idSpace, $id_client, $beginPeriod, $endPeriod);
        if (!$found) {
            return false;
        }

        $modelInvoice = new InInvoice();
        $number = $modelInvoice->getNextNumber($idSpace);
        $module = "booking";
        $controller = "Bookinginvoice";
        $date_generated = date("Y-m-d", time());
        $id_resp = intval($id_client);
        $invoice_id = $modelInvoice->addInvoice($module, $controller, $idSpace, 'in progress', $date_generated, $id_resp, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setTitle($idSpace, $invoice_id, BookingTranslator::MAD($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));
        $modelInvoice->setEditedBy($idSpace, $invoice_id, $idUser);
        try {
            $contentAll = $this->invoice($idSpace, $beginPeriod, $endPeriod, $id_client, $invoice_id, $lang);
        } catch(Exception $e) {
            $modelInvoice->setNumber($idSpace, $invoice_id, 'error');
            throw $e;
        }
        $modelInvoiceItem = new InInvoiceItem();
        $content = '';
        foreach ($contentAll['count'] as $c) {
            $content .= $c['content'];
        }
        $details = BookinginvoiceTranslator::Details($lang) . "=" . "bookinginvoicedetail/" . $idSpace . "/" . $invoice_id;
        $modelInvoiceItem->setItem($idSpace, 0, $invoice_id, $module, $controller, $content, $details, $contentAll['total_ht']);
        $modelInvoice->setTotal($idSpace, $invoice_id, $contentAll['total_ht']);
        $modelInvoice->setNumber($idSpace, $invoice_id, $number);
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
    public function invoice($idSpace, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang)
    {
        // get all resources
        Configuration::getLogger()->debug('[invoice][booking] create invoice', ['client' => $id_resp, 'space' => $idSpace]);

        $modelClient = new ClClient();
        $LABpricingid = $modelClient->getPricingID($idSpace, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] get pricing for client', ['client' => $id_resp, 'id' => $LABpricingid]);
        $modelResources = new ResourceInfo();
        $resources = $modelResources->getBySpace($idSpace);

        // get the pricing
        $timePrices = $this->getUnitTimePricesForEachResource($idSpace, $resources, $LABpricingid, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] time prices', ['client' => $id_resp, 'id' => $LABpricingid, 'prices' => $timePrices]);
        $packagesPrices = $this->getUnitPackagePricesForEachResource($idSpace, $resources, $LABpricingid, $id_resp);
        Configuration::getLogger()->debug('[invoice][booking] packages prices', ['client' => $id_resp, 'id' => $LABpricingid, 'prices' => $timePrices]);

        // get all the reservations for each resources
        $content = ['count' => []];
        $total_ht = 0;
        $modelCal = new BkCalendarEntry();
        $bkCalQuantitiesModel = new BkCalQuantities();
        $modelPackage = new BkPackage();
        foreach ($resources as $res) {
            $reservations = $modelCal->getUnpricedReservations($idSpace, $beginPeriod, $endPeriod, $res["id"], $id_resp);

            // get list of quantities sorted by deleted (ASC), then by id (desc)
            $allCalQuantities = $bkCalQuantitiesModel->getByResource($idSpace, $res["id"], include_deleted:true, sort:true);
            // get invoicable ones
            $invoicableCalQtes = array_filter($allCalQuantities, function ($calQte) {
                return $calQte["is_invoicing_unit"] == 1;
            });

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
                $resaIsInvoicingUnit = false;
                $resaQuantityId = "";
                /*
                 * For a reservation with invoicing units:
                 * if there is a non deleted invoicing unit for this resource and it is used in this reservation, this is the one which will be used: it replaces the deleted one.
                 * * if a deleted invoicing unit is used and there is no current invoicing unit used in this reservation, then the deleted one will be used
                 * if there are more than 1 deleted invoicing unit used in this reservation: use the last created one (max id)
                 */
                if ($reservation["quantities"] && $reservation["quantities"] != null) {
                    // Genrate an array of quantity's ids used in this reservation
                    $resaQtes = explode(";", $reservation["quantities"]);
                    array_pop($resaQtes);
                    $resaQteIds = array_map(function ($qte) {
                        return explode("=", $qte)[0];
                    }, $resaQtes);


                    // Is one of them amongst the deleted invoicable quantities?
                    // If there are more than one, raises an exception
                    foreach ($invoicableCalQtes as $invoicableQte) {
                        if (in_array($invoicableQte['id'], $resaQteIds)) {
                            $resaQuantityId = $invoicableQte['id'];
                            $resaIsInvoicingUnit = true;
                            break;
                        }
                    }
                }

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
                    if (!$timePrices || !array_key_exists($res['id'], $timePrices)) {
                        Configuration::getLogger()->debug("[booking][invoice] calculate error, no timePrices", ["timePrices" => $timePrices, "reservation" => $reservation]);
                        throw new PfmParamException("No time pricing defined!");
                    }
                    $slots = $modelCal->computeDuration($idSpace, $reservation);
                    $resaDayNightWe = $slots['hours'];
                    Configuration::getLogger()->debug('[invoice][booking] night and week ends', ['resource' => $res['id'], 'count' => $resaDayNightWe]);

                    if ($resaIsInvoicingUnit) {
                        if ($reservation["quantities"] && $reservation["quantities"] != null) {
                            // varchar formatted like "$mandatory=$quantity;" in bk_calendar_entry
                            // get number of resources booked
                            $strToFind = strval($resaQuantityId) . "=";

                            $lastPos = 0;
                            $positions = array();
                            while (($lastPos = strpos($reservation["quantities"], $strToFind, $lastPos))!==false) {
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

                $modelCal->setReservationInvoice($idSpace, $reservation["id"], $invoice_id);
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
                        $resourceCount["label"] = $res["name"] . " " . $modelPackage->getName($idSpace, $p["id"]);
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

    public function delete($idSpace, $id_invoice)
    {
        // get items
        require_once 'Modules/booking/Model/BkCalendarEntry.php';
        $model = new BkCalendarEntry();
        $services = $model->getInvoiceEntries($idSpace, $id_invoice);
        foreach ($services as $s) {
            $model->setReservationInvoice($idSpace, $s["id"], 0);
        }
    }

    public function details($idSpace, $invoice_id, $lang)
    {
        $sql = 'SELECT MIN(bk_calendar_entry.start_time) as dstart, MAX(bk_calendar_entry.start_time) as dend, SUM(bk_calendar_entry.end_time-bk_calendar_entry.start_time) as duration, re_info.name as label, core_users.name, core_users.firstname FROM bk_calendar_entry
        INNER JOIN re_info ON re_info.id=bk_calendar_entry.resource_id
        INNER JOIN core_users ON core_users.id=bk_calendar_entry.recipient_id
        WHERE bk_calendar_entry.invoice_id=?
        AND bk_calendar_entry.id_space=? AND bk_calendar_entry.deleted=0
        GROUP BY re_info.name, core_users.name, core_users.firstname
        ORDER BY re_info.name ASC';
        $res = $this->runRequest($sql, [$invoice_id, $idSpace]);

        $data = array();
        $data["title"] = BookingTranslator::MAD($lang);
        $data["header"] = array(
            "label" => BookingTranslator::Resource($lang),
            "user" => CoreTranslator::User($lang),
            "date" => CoreTranslator::Date($lang),
            "quantity" => BookingTranslator::Quantities($lang)
        );
        $data["content"] = array();
        while ($details = $res->fetch()) {
            $data["content"][] = array(
                "label" => $details['label'],
                "user" => $details['name'].' '.$details['firstname'],
                "date" => CoreTranslator::dateFromEn(date("Y-m-d", $details['dstart']), $lang) . " - " .
                          CoreTranslator::dateFromEn(date("Y-m-d", $details['dend']), $lang),
                "quantity" => $details['duration']/3600
                );
        }
        return $data;

        /*
        // all users in the invoice
        $sql = "SELECT DISTINCT recipient_id FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        $users = $this->runRequest($sql, array($invoice_id, $idSpace))->fetchAll();

        // all resources in the invoice
        $sqlr = "SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        $resources = $this->runRequest($sqlr, array($invoice_id, $idSpace))->fetchAll();



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
            $resouceName = $modelResource->getName($idSpace, $resource[0]);

            foreach($users as $user){
                $sql = "SELECT * FROM bk_calendar_entry WHERE invoice_id=? AND recipient_id=? AND resource_id=? AND deleted=0 AND id_space=? ORDER BY start_time ASC";
                $resreq = $this->runRequest($sql, array($invoice_id, $user[0], $resource[0], $idSpace));
                if ($resreq->rowCount() > 0){
                    $reservations = $resreq->fetchAll();
                    $time = 0;
                    foreach($reservations as $res){
                        $time += $res["end_time"] - $res["start_time"];
                    }
                    $data["content"][] = array(
                        "label" => $resouceName,
                        "user" => $modelUser->getUserFullName($user[0]),
                        "date" => CoreTranslator::dateFromEn(date("Y-m-d", $reservations[0]["start_time"]), $lang) . " - " .
                                  CoreTranslator::dateFromEn(date("Y-m-d", $reservations[count($reservations)-1]["start_time"]), $lang),
                        "quantity" => $time/3600
                        );
                }


            }
        }
        return $data;
        */
    }

    protected function getUnitTimePricesForEachResource(int $idSpace, array $resources, ?int $LABpricingid, $id_client)
    {
        // get the pricing informations
        $pricingModel = new BkNightWE();
        $pricingInfo = $pricingModel->getPricing($LABpricingid, $idSpace);
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
                $idSpace,
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

            $pday = $modelRessourcePricingOwner->getDayPrice($idSpace, $resource["id"], $id_client);
            if ($pday >= 0) {
                $timePrices[$resource["id"]]["price_day"] = $pday;
            } else {
                $timePrices[$resource["id"]]["price_day"] = $modelRessourcePricing->getDayPrice($idSpace, $resource["id"], $LABpricingid); //Tarif jour pour l'utilisateur selectionne
            }

            $pnight = $modelRessourcePricingOwner->getNightPrice($idSpace, $resource["id"], $id_client);
            if ($pnight >= 0) {
                $timePrices[$resource["id"]]["price_night"] = $pnight;
            } else {
                $timePrices[$resource["id"]]["price_night"] = $modelRessourcePricing->getNightPrice($idSpace, $resource["id"], $LABpricingid); //Tarif nuit pour l'utilisateur selectionne
            }

            $pwe = $modelRessourcePricingOwner->getWePrice($idSpace, $resource["id"], $id_client);
            if ($pwe >= 0) {
                $timePrices[$resource["id"]]["price_we"] = $pwe;
            } else {
                $timePrices[$resource["id"]]["price_we"] = $modelRessourcePricing->getWePrice($idSpace, $resource["id"], $LABpricingid);  //Tarif w-e pour l'utilisateur selectionne
            }
        }
        return $timePrices;
    }

    protected function getUnitPackagePricesForEachResource($idSpace, $resources, $LABpricingid, $id_client)
    {
        // calculate the reservations for each equipments
        $packagesPrices = array();
        $modelPackage = new BkPackage();
        $modelPrice = new BkPrice();
        $modelPriceOwner = new BkOwnerPrice();
        foreach ($resources as $resource) {
            // get the packages prices
            $packages = $modelPackage->getByResource($idSpace, $resource["id"], include_deleted:true);

            $pricesPackages = array();
            for ($i = 0; $i < count($packages); $i++) {
                $price = $modelPriceOwner->getPackagePrice($idSpace, $packages[$i]["id_package"], $resource["id"], $id_client);
                if ($price >= 0) {
                    $packages[$i]["price"] = $price;
                } else {
                    $packages[$i]["price"] = $modelPrice->getPackagePrice($idSpace, $packages[$i]["id_package"], $resource["id"], $LABpricingid);
                }
                $pricesPackages[] = $packages[$i];
            }
            $packagesPrices[$resource["id"]] = $pricesPackages;
        }
        return $packagesPrices;
    }
}
