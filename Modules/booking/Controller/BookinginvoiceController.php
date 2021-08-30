<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InInvoice.php';

require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/booking/Model/BookinginvoiceTranslator.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookinginvoiceController extends InvoiceAbstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "invoices";
    }

    
    /**
     * @deprecated
     */
    public function updateResaResponsiblesAction($id_space){
        
        require_once 'Modules/booking/Model/BkCalendarEntry.php';
        $modelCalentry = new BkCalendarEntry();
        $modelCalentry->updateNullResponsibles($id_space);
        echo "done";
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $formByProjects = $this->createAllForm($id_space, $lang);

        if ($formByProjects->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $this->invoiceAll($id_space, $beginPeriod, $endPeriod);
            $this->redirect("invoices/" . $id_space);
            return;
        }

        $formByPeriod = $this->createByPeriodForm($id_space, $lang);

        if ($formByPeriod->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");

            if ($id_resp != 0) {
                $this->invoice($id_space, $beginPeriod, $endPeriod, $id_resp);
                $this->redirect("invoices/" . $id_space);
                return;
            }
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang,
            "formByProjects" => $formByProjects->getHtml($lang),
            "formByPeriod" => $formByPeriod->getHtml($lang)));
    }

    public function editAction($id_space, $id_invoice, $pdf = 0) {

        require_once 'Modules/booking/Model/BkPackage.php';
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_space, $id_invoice);

        // generate pdf
        if ($pdf == 1) {
            $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }
        if ($pdf == 2) {
            $this->generatePDFInvoiceDetails($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }

        // unparse details
        $detailsData = array();
        if (!empty($id_items) > 0) {
            $item = $modelInvoiceItem->getItem($id_space, $id_items[0]["id"]);
            $details = $item["details"];
            
            $detailsArray = explode(";", $details);
            foreach ($detailsArray as $de) {
                $d = explode("=", $de);
                if (count($d) == 2) {
                    $detailsData[] = $d;
                }
            }
        }

        // create edit form
        $idItem = 0;
        if (!empty($id_items)) {
            $idItem = $id_items[0]["id"];
        }
        $form = $this->editForm($idItem, $id_space, $id_invoice, $lang);
        if ($form->check()) {
            $total_ht = 0;
            $id_services = $this->request->getParameter("id_service");
            $id_services = is_array($id_services) ? $id_services : array($id_services);
            $quantity = $this->request->getParameter("quantity");
            $unit_price = $this->request->getParameter("unit_price");
            $content = "";
            for ($i = 0; $i < count($id_services); $i++) {
                $content .= $id_services[$i] . "=" . $quantity[$i] . "=" . $unit_price[$i] . ";";
                $total_ht += $quantity[$i] * $unit_price[$i];
            }
            if (!empty($id_items) > 0) {
                $modelInvoiceItem->editItemContent($id_space, $id_items[0]["id"], $content, $total_ht);
            }
            // apply discount
            $discount = $form->getParameter("discount");
            $total_ht = (1 - floatval($discount) / 100) * $total_ht;

            $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($id_space, $id_invoice, $discount);
            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($id_space)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);

            $this->redirect("bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/O");
            return;
        }

        // render
        $this->render(array("id_space" => $id_space, "lang" => $lang, "invoice" => $invoice, "details" => $detailsData, "htmlForm" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id_invoice) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);
        if(!$invoice){
            Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id_invoice]);
            throw new PfmAuthException('access denied for this resource', 403);
        }

        // get items
        require_once 'Modules/booking/Model/BkCalendarEntry.php';
        $model = new BkCalendarEntry();
        $services = $model->getInvoiceEntries($id_space, $id_invoice);
        foreach ($services as $s) {
            $model->setReservationInvoice($id_space, $s["id"], 0);
        }
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang) {

        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $modelClient = new ClClient();
        
        $invoiceInfo = $modelInvoice->get($id_space ,$id_invoice);

        $clientInfo = $modelClient->get($id_space ,$invoiceInfo["id_responsible"]);
        $id_belonging = $clientInfo["pricing"];
        
        $item = $modelInvoiceItem->getItem($id_space, $id_item);
        $contentArray = explode(";", $item["content"]);
        $total = 0;
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                $itemQuantities[] = $data[1];
                $itemPrices[] = $data[2];
                $total += $data[1] * $data[2];
            }
        }

        $listResources = $this->getResourcesList($id_space, $id_belonging, $lang);
        $formAdd = new FormAdd($this->request, "editinvoiceorderformadd");
        $formAdd->addSelect("id_service", ResourcesTranslator::Resource($lang), $listResources["names"], $listResources["ids"], $itemServices);
        $formAdd->addNumber("quantity", InvoicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addFloat("unit_price", InvoicesTranslator::UnitPrice($lang), $itemPrices);
        //$formAdd->addHidden("id_item", $itemIds);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceorderform");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->addExternalButton(InvoicesTranslator::GeneratePdfDetails($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/2", "danger", true);
        $form->setButtonsWidth(4, 8);
        $form->setFormAdd($formAdd);

        $discount = $modelInvoice->getDiscount($id_space ,$id_invoice);
        $form->addText("discount", BookinginvoiceTranslator::Discount($lang), false, $discount);


        $total = (1 - floatval($discount) / 100) * $total;
        $form->addText("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        return $form;
    }

    protected function getResourcesList($id_space, $id_belonging, $lang) {
        $modelResource = new ResourceInfo();
        $modelNightWe = new BkNightWE();

        $modelPackages = new BkPackage();
        $resources = $modelResource->getBySpace($id_space);
        $ids = array();
        $names = array();
        foreach ($resources as $r) {
            $ids[] = $r["id"] . "_day";
            if (!$modelNightWe->isNight($id_space, $id_belonging) && !$modelNightWe->isWe($id_space ,$id_belonging)) {
                $names[] = $r["name"];
            } else {
                $names[] = $r["name"] . " " . BookinginvoiceTranslator::Day($lang);
            }
            if ($modelNightWe->isNight($id_space, $id_belonging)) {
                $ids[] = $r["id"] . "_night";
                $names[] = $r["name"] . " " . BookinginvoiceTranslator::night($lang);
            }
            if ($modelNightWe->isWe($id_space, $id_belonging)) {
                $ids[] = $r["id"] . "_we";
                $names[] = $r["name"] . " " . BookinginvoiceTranslator::WE($lang);
            }
            $packages = $modelPackages->getByResource($id_space, $r["id"]);
            foreach ($packages as $p) {
                $ids[] = $r["id"] . "_pk_" . $p["id"];
                $names[] = $r["name"] . " " . $p["name"];
            }
        }

        return array("names" => $names, "ids" => $ids);
    }

    protected function createAllForm($id_space, $lang) {
        $form = new Form($this->request, "BookingInvoiceAllForm");
        $form->addSeparator(InvoicesTranslator::Invoice_All($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), false, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), false, $this->request->getParameterNoException("period_end"));

        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoice/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang) {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(InvoicesTranslator::Invoice_Responsible($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), false, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), false, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClient = new ClClient();
        $resps = $modelClient->getForList($id_space);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);
        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoice/" . $id_space);
        return $form;
    }

    protected function invoiceAll($id_space, $beginPeriod, $endPeriod) {

        require_once 'Modules/booking/Model/BkPackage.php';
        require_once 'Modules/booking/Model/BkCalendarEntry.php';

        $modelCal = new BkCalendarEntry();
        $modelInvoice = new InInvoice();
        
        $modelClient = new ClClient();
        $resps = $modelClient->getAll($id_space);
        
        $number = "";
        foreach ($resps as $resp) {
            $billIt = $modelCal->hasResponsibleEntry($id_space, $resp["id"], $beginPeriod, $endPeriod);
            if ($billIt) {
                $number = $modelInvoice->getNextNumber($number);
                $this->invoice($id_space, $beginPeriod, $endPeriod, $resp["id"], $number);
            }
        }
    }

    /**
     * Generate an invoice for the chosen period.
     * 
     * To be noticed:
     * For each reservation, calculate the price. 
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
    protected function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $number = "") {
        $lang = $this->getLanguage();

        require_once 'Modules/booking/Model/BkPackage.php';
        require_once 'Modules/booking/Model/BkCalendarEntry.php';

        // get all resources
        $modelClient = new ClCLient();
        $LABpricingid = $modelClient->getPricingID($id_space, $id_resp);
        $modelResouces = new ResourceInfo();
        $resources = $modelResouces->getBySpace($id_space);

        // get the pricing
        $timePrices = $this->getUnitTimePricesForEachResource($id_space, $resources, $LABpricingid, $id_resp);
        $packagesPrices = $this->getUnitPackagePricesForEachResource($id_space, $resources, $LABpricingid, $id_resp);
        
        // add the invoice to the database
        $modelInvoice = new InInvoice();
        $number = ($number != "") ?: $modelInvoice->getNextNumber();

        $module = "booking";
        $controller = "Bookinginvoice";
        $date_generated = date("Y-m-d", time());
        $invoice_id = $modelInvoice->addInvoice($module, $controller, $id_space, $number, $date_generated, $id_resp, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($id_space, $invoice_id, $_SESSION["id_user"]);
        $modelInvoice->setTitle($id_space, $invoice_id, "MAD: période du " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " au " . CoreTranslator::dateFromEn($endPeriod, $lang));

        // get all the reservations for each resources
        $content = "";
        $total_ht = 0;
        $modelCal = new BkCalendarEntry();
        $bkCalQuantitiesModel = new BkCalQuantities();
        foreach ($resources as $res) {
            // get reservations
            $reservations = $modelCal->getUnpricedReservations($id_space, $beginPeriod, $endPeriod, $res["id"], $id_resp);
            
            // get list of quantities
            $calQuantities = $bkCalQuantitiesModel->calQuantitiesByResource($id_space, $res["id"]);
            $calQuantities = ($calQuantities !== null) ?: [];

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
            $totalQte = 0; // $totalQte = total number of items booked

            foreach ($reservations as $reservation) {
                // count: day night we, packages, quantity
                if ($reservation["package_id"] > 0) {
                    $userPackages[$reservation["package_id"]] ++;
                } else {
                    $resaDayNightWe = $this->calculateTimeResDayNightWe($reservation, $timePrices[$res["id"]]);
                    
                    if ($isInvoicingUnit) {
                        if ($reservation["quantities"] && $reservation["quantities"] != null) {
                            // TODO: secure that (try catch ?)
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
                        $userTime["ratio_bookings_day"] += $resaDayNightWe["ratio_bookings_day"];
                        $userTime["ratio_bookings_night"] += $resaDayNightWe["ratio_bookings_night"];
                        $userTime["ratio_bookings_we"] += $resaDayNightWe["ratio_bookings_we"];
                    } else {
                        $userTime["nb_hours_day"] += $resaDayNightWe["nb_hours_day"];
                        $userTime["nb_hours_night"] += $resaDayNightWe["nb_hours_night"];
                        $userTime["nb_hours_we"] += $resaDayNightWe["nb_hours_we"];
                    }
                }
                // Record that an invoice was generated for this reservation (so that we can't re-invoice if existing)
                $modelCal->setReservationInvoice($id_space, $reservation["id"], $invoice_id);
            }

            // fill content
            if (count($reservations) > 0) {
                if ($userTime["nb_hours_day"] > 0) {
                    $content .= $res["id"] . "_day=" . $userTime["nb_hours_day"] . "=" . $timePrices[$res["id"]]["price_day"] . ";";
                    $total_ht += floatval($userTime["nb_hours_day"]) * floatval($timePrices[$res["id"]]["price_day"]);
                }
                if ($userTime["nb_hours_night"] > 0) {
                    $content .= $res["id"] . "_night=" . $userTime["nb_hours_night"] . "=" . $timePrices[$res["id"]]["price_night"] . ";";
                    $total_ht += floatval($userTime["nb_hours_night"]) * floatval($timePrices[$res["id"]]["price_night"]);
                }
                if ($userTime["nb_hours_we"] > 0) {
                    $content .= $res["id"] . "_we=" . $userTime["nb_hours_we"] . "=" . $timePrices[$res["id"]]["price_we"] . ";";
                    $total_ht += floatval($userTime["nb_hours_we"]) * floatval($timePrices[$res["id"]]["price_we"]);
                }
                foreach ($packagesPrices[$res["id"]] as $p) {
                    if ($userPackages[$p["id"]] > 0) {
                        $content .= $res["id"] . "_pk_" . $p["id"] . "=" . $userPackages[$p["id"]] . "=" . $p["price"] . ";";
                        $total_ht += floatval($userPackages[$p["id"]]) * floatval($p["price"]);
                    }
                }
                // manage quantity
                if ($totalQte > 0) {
                    if ($userTime["ratio_bookings_day"] > 0) {
                        $dayQte = $totalQte * $userTime["ratio_bookings_day"];
                        $content .= $res["id"] . "_day=" . $dayQte . "=" . $timePrices[$res["id"]]["price_day"] . ";";
                        $total_ht += floatval($dayQte) * floatval($timePrices[$res["id"]]["price_day"]);
                    }
                    if ($userTime["ratio_bookings_night"] > 0) {
                        $nightQte = $totalQte * $userTime["ratio_bookings_night"];
                        $content .= $res["id"] . "_night=" . $nightQte . "=" . $timePrices[$res["id"]]["price_night"] . ";";
                        $total_ht += floatval($nightQte) * floatval($timePrices[$res["id"]]["price_night"]);
                    }
                    if ($userTime["ratio_bookings_we"] > 0) {
                        $weQte = $totalQte * $userTime["ratio_bookings_we"];
                        $content .= $res["id"] . "_we=" . $weQte . "=" . $timePrices[$res["id"]]["price_we"] . ";";
                        $total_ht += floatval($weQte) * floatval($timePrices[$res["id"]]["price_we"]);
                    }
                }
            }
        }

        // details
        $details = BookinginvoiceTranslator::Details($lang) . "=" . "bookinginvoicedetail/" . $id_space . "/" . $invoice_id;

        // add the invoice content
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->setItem($id_space, 0, $invoice_id, $module, $controller, $content, $details, $total_ht);

        $modelInvoice->setTotal($id_space, $invoice_id, $total_ht);

        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($invoice_id)]
        ]);
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

    protected function getUnitTimePricesForEachResource($id_space, $resources, $LABpricingid, $id_cient) {
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

            $pday = $modelRessourcePricingOwner->getDayPrice($id_space ,$resource["id"], $id_cient);
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

    // TODO: comment here
    protected function calculateTimeResDayNightWe($reservation, $timePrices) {

        // initialize output
        $nb_hours_day = 0;
        $nb_hours_night = 0;
        $nb_hours_we = 0;

        // extract some variables
        $we_array = $timePrices["we_array"];
        $night_start = $timePrices['night_start'];
        $night_end = $timePrices['night_end'];

        $searchDate_start = $reservation["start_time"];
        $searchDate_end = $reservation["end_time"];

        // calulate pricing
        if (intval($timePrices["tarif_unique"]) === 1) { // unique pricing
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

        // manage cases where a booking is between day and night hours => get a ratio
        $totalHours = $nb_hours_day + $nb_hours_night + $nb_hours_we;
        $resaDayNightWe["ratio_bookings_day"] = round($nb_hours_day / $totalHours, 2);
        $resaDayNightWe["ratio_bookings_night"] = round($nb_hours_night / $totalHours, 2);
        $resaDayNightWe["ratio_bookings_we"] = round($nb_hours_we / $totalHours, 2);
        return $resaDayNightWe;
    }

    // @bug refers to EcUnit
    protected function invoiceProjects($id_space, $id_projects, $id_unit, $id_resp) {

        // add invoice
        //echo "add invoice <br/>";
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $module = "services";
        $controller = "servicesinvoiceproject";
        $number = $modelInvoice->getNextNumber();
        $id_invoice = $modelInvoice->addInvoice($id_space, $module, $controller, $number, date("Y-m-d", time()), $id_unit, $id_resp);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $_SESSION["id_user"]);

        // parse content
        //echo "parse content <br/>";
        $modelUnit = new EcUnit();
        $id_belonging = $modelUnit->getBelonging($id_unit);
        $total_ht = 0;
        $modelProject = new SeProject();
        $addedServices = array();
        $addedServicesCount = array();
        $addedServicesPrice = array();
        $modelPrice = new SePrice();
        foreach ($id_projects as $id_proj) {
            $services = $modelProject->getNoInvoicesServices($id_space, $id_proj);
            //print_r($services);
            for ($i = 0; $i < count($services); $i++) {
                $quantity = 0;
                $modelProject->setServiceInvoice($id_space, $services[$i]["id"], $id_invoice);
                if (!in_array($services[$i]["id_service"], $addedServices)) {
                    $addedServices[] = $services[$i]["id_service"];
                    $quantity = floatval($services[$i]["quantity"]);
                    $price = $modelPrice->getPrice($id_space, $services[$i]["id_service"], $id_belonging);
                    $addedServicesCount[] = $quantity;
                    $addedServicesPrice[] = $price;
                    $total_ht += $quantity * $price;
                } else {
                    $key = array_search($services[$i]["id_service"], $addedServices);
                    $quantity = floatval($services[$i]["quantity"]);
                    $addedServicesCount[$key] += $quantity;
                    $total_ht += $quantity * $addedServicesPrice[$key];
                }
            }
        }

        $content = "";
        for ($i = 0; $i < count($addedServices); $i++) {
            $content .= $addedServices[$i] . "=" . $addedServicesCount[$i] . "=" . $addedServicesPrice[$i] . ";";
        }
        // get details
        //echo "get details <br/>";
        $details = "";
        foreach ($id_projects as $id_proj) {
            $name = $modelProject->getName($id_space, $id_proj);
            $details .= $name[0] . "=" . "servicesprojectedit/" . $id_space . "/" . $id_proj . ";";
        }
        //echo "set item <br/>";
        // set invoice itmems
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);
    }

    protected function invoiceTable($id_space, $invoice, $id_item, $lang) {
        $table = "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 52%\">" . InvoicesTranslator::Designation($lang) . "</th>
                        <th style=\"width: 14%\">" . InvoicesTranslator::Quantity($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::UnitPrice($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::Price_HT($lang) . "</th>
                    </tr>
                </table>
        ";

        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; border-collapse: collapse; background: #F7F7F7; text-align: center; font-size: 10pt;\">";
        $content = $this->unparseContent($id_space, $id_item, $lang);
        //print_r($invoice);
        $total = 0;
        foreach ($content as $d) {
            if( $d[2] > 0 ){
                $table .= "<tr>";
                $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d[0] . "</td>";
                $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format($d[1], 2, ',', ' ') . "</td>";
                $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d[2], 2, ',', ' ') . " &euro;</td>";
                $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d[1] * $d[2], 2, ',', ' ') . " &euro;</td>";
                $table .= "</tr>";
                $total += $d[1] * $d[2];
            }
        }
        $discount = floatval($invoice["discount"]);
        if ($discount > 0) {
            $total = (1 - $discount / 100) * $total;
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . InvoicesTranslator::Discount($lang) . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . 1 . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";

        return array("table" => $table, "total" => $total);
    }

    protected function generatePDFInvoiceDetails($id_space, $invoice, $id_item, $lang) {

        $tabledata = $this->invoiceTable($id_space, $invoice, $id_item, $lang);
        $table = $tabledata["table"];
        $total = $tabledata["total"];
        $details = $this->detailsTable($id_space, $invoice["id"], $lang);

        $modelClient = new ClClient();
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $resp = $modelClient->getContactName($id_space, $invoice["id_responsible"]);
        $useTTC = true;

        $this->genreratePDF($id_space, $invoice["number"], CoreTranslator::dateFromEn($invoice["date_generated"], $lang), $unit, $resp, $adress, $table, $total, $useTTC, $details);
    }

    protected function generatePDFInvoice($id_space, $invoice, $id_item, $lang) {

        $tabledata = $this->invoiceTable($id_space, $invoice, $id_item, $lang);
        $table = $tabledata["table"];
        $total = $tabledata["total"];

        $modelClient = new ClClient();
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $resp = $modelClient->getContactName($id_space, $invoice["id_responsible"]);
        
        $useTTC = true;
        $this->genreratePDF($id_space, $invoice["number"], CoreTranslator::dateFromEn($invoice["date_generated"], $lang), $unit, $resp, $adress, $table, $total, $useTTC);
    }

    protected function unparseContent($id_space, $id_item, $lang) {

        $modelResources = new ResourceInfo();
        $modelPackage = new BkPackage();

        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_space, $id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $name = "";
                $idArray = explode("_", $data[0]);
                if (count($idArray) == 2) {
                    $idRes = $idArray[0];
                    $idDay = $idArray[1];
                    $name = $modelResources->getName($id_space, $idRes);
                    if ($idDay == "day") {
                        $name .= " " . BookinginvoiceTranslator::Day($lang);
                    } else if ($idDay == "night") {
                        $name .= " " . BookinginvoiceTranslator::night($lang);
                    } else if ($idDay == "we") {
                        $name .= " " . BookinginvoiceTranslator::WE($lang);
                    }
                } else if (count($idArray) == 3) {
                    $name = $modelResources->getName($id_space, $idArray[0]);
                    $name .= " " . $modelPackage->getName($id_space, $idArray[2]);
                }

                $contentList[] = array($name, $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    protected function detailsTable($id_space, $id_invoice, $lang) {
        $data = $this->detailsData($id_space, $id_invoice);

        $table = BookinginvoiceTranslator::Details($lang) . "<br/>";
        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 30%\">" . BookinginvoiceTranslator::Resource($lang) . "</th>
                        <th style=\"width: 50%\">" . BookinginvoiceTranslator::Recipient($lang) . "</th>
                        <th style=\"width: 20%\">" . InvoicesTranslator::Quantity($lang) . "</th>
                    </tr>
                </table>
        ";

        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; border-collapse: collapse; background: #F7F7F7; text-align: center; font-size: 10pt;\">";

        foreach ($data as $d) {
            $table .= "<tr>";
            $table .= "<td style=\"width: 30%; text-align: left; border: solid 1px black;\">" . $d['resource'] . "</td>";
            $table .= "<td style=\"width: 50%; border: solid 1px black;\">" . $d['user'] . "</td>";
            $table .= "<td style=\"width: 20%; text-align: right; border: solid 1px black;\">" . $d['time'] . "</td>";
            $table .= "</tr>";
        }

        $table .= "</table>";
        return $table;
    }

    public function detailsData($id_space, $id_invoice) {
        require_once 'Modules/booking/Model/BkCalendarEntry.php';
        require_once 'Modules/booking/Model/BkPackage.php';

        $modelCalEntry = new BkCalendarEntry();
        $modelInvoice = new InInvoice();
        $modelPackage = new BkPackage();
        $modelResource = new ResourceInfo();
        $modelUser = new CoreUser();


        $resources = $modelCalEntry->getResourcesForInvoice($id_space, $id_invoice);
        $data = array();
        foreach ($resources as $r) {
            //print_r($r);
            $users = $modelCalEntry->getResourcesUsersForInvoice($id_space, $r['resource_id'], $id_invoice);
            foreach ($users as $user) {
                //print_r($user);
                $resas = $modelCalEntry->getResourcesUserResaForInvoice($id_space, $r['resource_id'], $user['recipient_id'], $id_invoice);
                $time = 0;
                for ($i = 0; $i < count($resas); $i++) {

                    $time += floatval($resas[$i]['end_time']) - floatval($resas[$i]['start_time']);
                }
                $data[] = array('resource' => $modelResource->getName($id_space, $r['resource_id']), 'user' => $modelUser->getUserFUllName($user['recipient_id']), 'time' => round($time / 3600, 1));
            }
        }
        return $data;
    }

    public function detailsAction($id_space, $id_invoice) {

        $data = $this->detailsData($id_space, $id_invoice);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->get($id_space, $id_invoice);

        $table = new TableView();
        $table->setTitle(BookinginvoiceTranslator::Details($lang) . ": " . $invoiceInfo["number"], 3);

        $headers = array("resource" => BookinginvoiceTranslator::Resource($lang),
            "user" => BookinginvoiceTranslator::Recipient($lang),
            "time" => BookinginvoiceTranslator::Duration($lang)
        );

        $tableHtml = $table->view($data, $headers);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "tableHtml" => $tableHtml));
    }

}
