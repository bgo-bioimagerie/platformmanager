<?php

require_once 'Framework/Model.php';
require_once 'Modules/invoices/Model/InvoiceModel.php';

require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BookingTranslator.php';


/**
 * Class defining the SyColorCode model
 *
 * @author Sylvain Prigent
 */
class BookingInvoice extends InvoiceModel {


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

        $sql = "SELECT id FROM bk_calendar_entry WHERE responsible_id=? AND start_time>=? AND start_time<=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resp, $startPeriodeTime, $endPeriodeTime, $id_space));
        if ( $req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang) {

        // get all resources
        $modelClient = new ClClient();
        $LABpricingid = $modelClient->getPricingID($id_space, $id_resp);

        $modelResouces = new ResourceInfo();
        $resources = $modelResouces->getBySpace($id_space);

        //echo "LABpricingid = " . $LABpricingid . "<br/>";
        // get the pricing
        $timePrices = $this->getUnitTimePricesForEachResource($resources, $LABpricingid, $id_resp, $id_space);
        //echo "pass 1<br/>";
        $packagesPrices = $this->getUnitPackagePricesForEachResource($id_space, $resources, $LABpricingid, $id_resp);


        // get all the reservations for each resources
        $total_ht = 0;
        $modelCal = new BkCalendarEntry();
        $content = array();
        $content["count"] = array();
        $modelPackage = new BkPackage();
        foreach ($resources as $res) {
            $reservations = $modelCal->getUnpricedReservations($id_space, $beginPeriod, $endPeriod, $res["id"], $id_resp);

            // get all packages
            $userPackages = array();
            foreach ($packagesPrices[$res["id"]] as $p) {
                $userPackages[$p["id"]] = 0;
            }

            $userTime = array();
            //print_r($reservations);
            $userTime["nb_hours_day"] = 0;
            $userTime["nb_hours_night"] = 0;
            $userTime["nb_hours_we"] = 0;
            foreach ($reservations as $reservation) {

                // count: day night we, packages
                if ($reservation["package_id"] > 0) {
                    $userPackages[$reservation["package_id"]] ++;
                } else {
                    if(!$timePrices || !array_key_exists($res['id'], $timePrices)) {
                        Configuration::getLogger()->debug("[booking][invoice] calculate error, no timePrices", ["timePrices" => $timePrices, "reservation" => $reservation]);
                        throw new PfmParamException("No time pricing defined!", 500);
                    }
                    $resaDayNightWe = $this->calculateTimeResDayNightWe($reservation, $timePrices[$res["id"]]);
                    $userTime["nb_hours_day"] += $resaDayNightWe["nb_hours_day"];
                    $userTime["nb_hours_night"] += $resaDayNightWe["nb_hours_night"];
                    $userTime["nb_hours_we"] += $resaDayNightWe["nb_hours_we"];
                }

                $modelCal->setReservationInvoice($id_space, $reservation["id"], $invoice_id);
            }
            // fill content
            $resourceCount = array();
            if (count($reservations) > 0) {
                //echo "<br/> user time day = " . $userTime["nb_hours_day"] . "<br/>";

                $resourceCount["resource"] = $res["id"];

                if ($userTime["nb_hours_day"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::Day($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_day"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_day"];

                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;

                }
                if ($userTime["nb_hours_night"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::night($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_night"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_night"];

                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;

                }
                if ($userTime["nb_hours_we"] > 0) {
                    $resourceCount["label"] = $res["name"] . " " . BookingTranslator::WE($lang);
                    $resourceCount["quantity"] = $userTime["nb_hours_we"];
                    $resourceCount["unitprice"] = $timePrices[$res["id"]]["price_we"];

                    $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                    $content["count"][] = $resourceCount;

                }
                foreach ($packagesPrices[$res["id"]] as $p) {
                    if ($userPackages[$p["id"]] > 0) {

                        $resourceCount["label"] = $res["name"] . " " . $modelPackage->getName($id_space, $p["id"] );
                        $resourceCount["quantity"] = $userPackages[$p["id"]];
                        $resourceCount["unitprice"] = $p["price"];

                        $total_ht += floatval($resourceCount["quantity"]) * floatval($resourceCount["unitprice"]);
                        $content["count"][] = $resourceCount;
                    }
                }


            }


            //echo "<br/> content: $content <br/>";
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

                //echo "user = " . $user[0] . ", resource = " . $resource[0] . "<br/>";

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

    protected function getUnitTimePricesForEachResource($resources, $LABpricingid, $id_cient, $id_space) {

        // get the pricing informations
        $pricingModel = new BkNightWE();
        $pricingInfo = $pricingModel->getPricing($LABpricingid, $id_space);

        if(! array_key_exists('tarif_unique', $pricingInfo)){return Array();}
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

            $pday = $modelRessourcePricingOwner->getDayPrice($id_space, $resource["id"], $id_cient);
            if ($pday >= 0) {
                $timePrices[$resource["id"]]["price_day"] = $pday;
            } else {
                $timePrices[$resource["id"]]["price_day"] = $modelRessourcePricing->getDayPrice($id_space, $resource["id"], $LABpricingid); //Tarif jour pour l'utilisateur selectionne
            }

            $pnight = $modelRessourcePricingOwner->getNightPrice($id_space, $resource["id"], $id_cient);
            if ($pnight >= 0) {
                $timePrices[$resource["id"]]["price_night"] = $pnight;
            } else {
                $timePrices[$resource["id"]]["price_night"] = $modelRessourcePricing->getNightPrice($id_space, $resource["id"], $LABpricingid); //Tarif nuit pour l'utilisateur selectionne
            }

            $pwe = $modelRessourcePricingOwner->getWePrice($id_space, $resource["id"], $id_cient);
            if ($pwe >= 0) {
                $timePrices[$resource["id"]]["price_we"] = $pwe;
            } else {
                $timePrices[$resource["id"]]["price_we"] = $modelRessourcePricing->getWePrice($id_space, $resource["id"], $LABpricingid);  //Tarif w-e pour l'utilisateur selectionne
            }
        }
        return $timePrices;
    }

    protected function getUnitPackagePricesForEachResource($id_space, $resources, $LABpricingid, $id_unit) {

        // calculate the reservations for each equipments
        $packagesPrices = array();
        $modelPackage = new BkPackage();
        $modelPrice = new BkPrice();
        $modelPriceOwner = new BkOwnerPrice();
        foreach ($resources as $resource) {
            // get the packages prices
            $packages = $modelPackage->getByResource($id_space, $resource["id"]);

            $pricesPackages = array();
            for ($i = 0; $i < count($packages); $i++) {
                $price = $modelPriceOwner->getPackagePrice($id_space, $packages[$i]["id"], $resource["id"], $id_unit);
                if ($price >= 0) {
                    $packages[$i]["price"] = $price;
                } else {
                    $packages[$i]["price"] = $modelPrice->getPackagePrice($id_space, $packages[$i]["id"], $resource["id"], $LABpricingid);
                }
                $pricesPackages[] = $packages[$i];
            }
            $packagesPrices[$resource["id"]] = $pricesPackages;
        }

        //print_r($packagesPrices);
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
            throw new PfmParamException("No time pricing defined!", 500);
        }
        $we_array = $timePrices["we_array"];
        $night_start = $timePrices['night_start'];
        $night_end = $timePrices['night_end'];

        $searchDate_start = $reservation["start_time"];
        $searchDate_end = $reservation["end_time"];

        // calulate pricing
        if ($timePrices["tarif_unique"] > 0) { // unique pricing
            $nb_hours_day = ($searchDate_end - $searchDate_start);
        } else {
            $gap = 60;
            $timeStep = $searchDate_start;
            while ($timeStep <= $searchDate_end) {
                // test if pricing is we
                if (in_array(date("N", $timeStep), $we_array) && in_array(date("N", $timeStep + $gap), $we_array)) {  // we pricing
                    $nb_hours_we += $gap;
                } else {
                    $H = date("H", $timeStep);

                    if ($H >= $night_end && $H < $night_start) { // price day
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

        return $resaDayNightWe;
    }

}
