<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Events.php';
require_once 'Modules/core/Model/CoreVirtual.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InInvoice.php';

require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BookingInvoice.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/booking/Model/BookinginvoiceTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookinginvoiceController extends InvoiceAbstractController
{
    /**
     * @deprecated
     */
    public function updateResaResponsiblesAction($id_space)
    {
        $modelCalentry = new BkCalendarEntry();
        $modelCalentry->updateNullResponsibles($id_space);
        echo "done";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formByProjects = $this->createAllForm($id_space, $lang);
        if ($formByProjects->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);

            $this->invoiceAll($id_space, $beginPeriod, $endPeriod);
            return $this->redirect("invoices/" . $id_space);
        }

        $formByPeriod = $this->createByPeriodForm($id_space, $lang);
        if ($formByPeriod->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            if ($id_resp != 0) {
                $this->invoice($id_space, $beginPeriod, $endPeriod, $id_resp);
                return $this->redirect("invoices/" . $id_space);
            }
        }

        return $this->render(array("id_space" => $id_space, "lang" => $lang,
            "formByProjects" => $formByProjects->getHtml($lang),
            "formByPeriod" => $formByPeriod->getHtml($lang)));
    }

    public function editAction($id_space, $id_invoice, $pdf = 0)
    {
        require_once 'Modules/booking/Model/BkPackage.php';
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_space, $id_invoice);

        // generate pdf
        if ($pdf == 1) {
            return $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
        }
        if ($pdf == 2) {
            return $this->generatePDFInvoiceDetails($id_space, $invoice, $id_items[0]["id"], $lang);
        }

        // unparse details
        $detailsData = array();
        if (!empty($id_items)) {
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
            $quantity = $this->request->getParameter("quantity");
            $unit_price = $this->request->getParameter("unit_price");
            $content = "";
            $id_services = is_array($id_services) ? $id_services : [];
            for ($i = 0; $i < count($id_services); $i++) {
                $content .= $id_services[$i] . "=" . $quantity[$i] . "=" . $unit_price[$i] . ";";
                $total_ht += floatval($quantity[$i]) * floatval($unit_price[$i]);
            }
            if (!empty($id_items)) {
                $modelInvoiceItem->editItemContent($id_space, $id_items[0]["id"], $content, $total_ht);
            }
            // apply discount
            $discount = $form->getParameter("discount");
            $total_ht = (1 - floatval($discount) / 100) * $total_ht;

            $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($id_space, $id_invoice, $discount);

            $_SESSION['flash'] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $_SESSION['flashClass'] = 'success';

            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($id_space)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);

            return $this->redirect("bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/O", [], ['invoice' => $invoice]);
        }

        // render
        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "invoice" => $invoice,
            "details" => $detailsData,
            "htmlForm" => $form->getHtml($lang),
            "data" => ['invoice' => $invoice, 'items' => $id_items]
        ));
    }

    public function deleteAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);
        if (!$invoice) {
            Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id_invoice]);
            throw new PfmAuthException('access denied for this resource', 403);
        }

        // get items
        $model = new BkCalendarEntry();
        $services = $model->getInvoiceEntries($id_space, $id_invoice);
        foreach ($services as $s) {
            $model->setReservationInvoice($id_space, $s["id"], null);
        }
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang)
    {
        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $modelClient = new ClClient();
        $modelResource = new ResourceInfo();

        $invoiceInfo = $modelInvoice->get($id_space, $id_invoice);

        $clientInfo = $modelClient->get($id_space, $invoiceInfo["id_responsible"]);
        $id_belonging = $clientInfo["pricing"];

        $item = $modelInvoiceItem->getItem($id_space, $id_item);
        $contentArray = explode(";", $item["content"]);
        $total = 0;
        $deletedPackages = array();
        $bkPackageModel = new BkPackage();
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                // if package and is deleted, add it to the list of deleted packages
                if (strpos($data[0], '_pk_')) {
                    $pk_id = explode('_pk_', $data[0])[1];
                    if ($bkPackageModel->isDeleted($id_space, $pk_id)) {
                        $deletedPackages[] = $pk_id;
                    }
                }
                $itemQuantities[] = $data[1];
                $itemPrices[] = $data[2];
                if (is_numeric($data[1]) && is_numeric($data[2])) {
                    $total += $data[1] * $data[2];
                } else {
                    $_SESSION['flash'] = InvoicesTranslator::NonNumericValue($lang);
                    $_SESSION['flashClass'] = 'danger';
                }
            }
        }

        $listResources = $this->getResourcesList($id_space, $id_belonging, $lang);

        // add selected deleted packages to $listResources
        if (!empty($deletedPackages)) {
            foreach ($deletedPackages as $id_package) {
                $p = $bkPackageModel->getById($id_space, $id_package);
                $pName = $p["name"] . " [!]";
                $r = $modelResource->get($id_space, $p["id_resource"]);
                array_push($listResources["ids"], $r["id"] . "_pk_" . $p["id"]);
                array_push($listResources["names"], $r["name"] . " " . $pName);
            }
        }

        $formAdd = new FormAdd($this->request, "editinvoiceorderformadd");
        $formAdd->addSelect("id_service", ResourcesTranslator::Resource($lang), $listResources["names"], $listResources["ids"], $itemServices);
        $formAdd->addFloat("quantity", InvoicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addFloat("unit_price", InvoicesTranslator::UnitPrice($lang), $itemPrices);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceorderform");

        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->addExternalButton(InvoicesTranslator::GeneratePdfDetails($lang), "bookinginvoiceedit/" . $id_space . "/" . $id_invoice . "/2", "danger", true);

        $form->setFormAdd($formAdd);

        $discount = $modelInvoice->getDiscount($id_space, $id_invoice);
        $form->addText("discount", InvoicesTranslator::Discount($lang), false, $discount);


        $total = (1 - floatval($discount) / 100) * $total;
        $form->addText("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        return $form;
    }

    protected function getResourcesList($id_space, $id_belonging, $lang)
    {
        $modelResource = new ResourceInfo();
        $modelNightWe = new BkNightWE();

        $modelPackages = new BkPackage();
        $resources = $modelResource->getBySpace($id_space);
        $ids = array();
        $names = array();
        foreach ($resources as $r) {
            $ids[] = $r["id"] . "_day";
            if (!$modelNightWe->isNight($id_space, $id_belonging) && !$modelNightWe->isWe($id_space, $id_belonging)) {
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

    protected function createAllForm($id_space, $lang)
    {
        $form = new Form($this->request, "BookingInvoiceAllForm");
        $form->addSeparator(InvoicesTranslator::Invoice_All($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));



        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoice/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang)
    {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(InvoicesTranslator::Invoice_Responsible($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClient = new ClClient();
        $resps = $modelClient->getForList($id_space);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);


        $form->setValidationButton(CoreTranslator::Save($lang), "bookinginvoice/" . $id_space);
        return $form;
    }

    protected function invoiceAll($id_space, $beginPeriod, $endPeriod)
    {
        $cv = new CoreVirtual();
        $rid = $cv->newRequest($id_space, "invoices", "booking:$beginPeriod => $endPeriod");
        Events::send([
            "action" => Events::ACTION_INVOICE_REQUEST,
            "space" => ["id" => intval($id_space)],
            "user" => ["id" => $_SESSION['id_user']],
            "type" => BookingInvoice::$INVOICES_BOOKING_ALL,
            "period_begin" => $beginPeriod,
            "period_end" => $endPeriod,
            "request" => ["id" => $rid]
        ]);
    }

    protected function invoice($id_space, $beginPeriod, $endPeriod, $id_resp)
    {
        $cv = new CoreVirtual();
        $rid = $cv->newRequest($id_space, "invoices", "booking:$beginPeriod => $endPeriod");

        Events::send([
            "action" => Events::ACTION_INVOICE_REQUEST,
            "space" => ["id" => intval($id_space)],
            "user" => ["id" => $_SESSION['id_user']],
            "type" => BookingInvoice::$INVOICES_BOOKING_CLIENT,
            "period_begin" => $beginPeriod,
            "period_end" => $endPeriod,
            "id_client" => intval($id_resp),
            "request" => ["id" => $rid]
        ]);
    }

    /**
     * @deprecated ? seems never called
     */
    protected function invoiceProjects($id_space, $id_projects, $id_unit, $id_resp)
    {
        // add invoice
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $module = "services";
        $controller = "servicesinvoiceproject";
        $number = $modelInvoice->getNextNumber($id_space);
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, $number, date("Y-m-d", time()), $id_unit, $id_resp);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $_SESSION["id_user"]);

        // parse content
        $modelClient = new ClClient();
        $id_pricing = $modelClient->getPricingID($id_space, $id_resp);
        $total_ht = 0;
        $modelProject = new SeProject();
        $addedServices = array();
        $addedServicesCount = array();
        $addedServicesPrice = array();
        $modelPrice = new SePrice();
        foreach ($id_projects as $id_proj) {
            $services = $modelProject->getNoInvoicesServices($id_space, $id_proj);
            for ($i = 0; $i < count($services); $i++) {
                $quantity = 0;
                $modelProject->setServiceInvoice($id_space, $services[$i]["id"], $id_invoice);
                if (!in_array($services[$i]["id_service"], $addedServices)) {
                    $addedServices[] = $services[$i]["id_service"];
                    $quantity = floatval($services[$i]["quantity"]);
                    $price = floatval($modelPrice->getPrice($id_space, $services[$i]["id_service"], $id_pricing));
                    $addedServicesCount[] = $quantity;
                    $addedServicesPrice[] = $price;
                    $total_ht += floatval($quantity) * floatval($price);
                } else {
                    $key = array_search($services[$i]["id_service"], $addedServices);
                    $quantity = floatval($services[$i]["quantity"]);
                    $addedServicesCount[$key] += $quantity;
                    $total_ht += floatval($quantity) * floatval($addedServicesPrice[$key]);
                }
            }
        }

        $content = "";
        for ($i = 0; $i < count($addedServices); $i++) {
            $content .= $addedServices[$i] . "=" . $addedServicesCount[$i] . "=" . $addedServicesPrice[$i] . ";";
        }

        // get details
        $details = "";
        foreach ($id_projects as $id_proj) {
            $name = $modelProject->getName($id_space, $id_proj);
            $details .= $name[0] . "=" . "servicesprojectedit/" . $id_space . "/" . $id_proj . ";";
        }

        // set invoice itmems
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);
    }

    protected function invoiceTable($id_space, $invoice, $id_item, $lang)
    {
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
        $total = 0;
        foreach ($content as $d) {
            if ($d[2] > 0) {
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

    protected function generatePDFInvoiceDetails($id_space, $invoice, $id_item, $lang)
    {
        $tabledata = $this->invoiceTable($id_space, $invoice, $id_item, $lang);
        $table = $tabledata["table"];
        $total = $tabledata["total"];
        $details = $this->detailsTable($id_space, $invoice["id"], $lang);

        $modelClient = new ClClient();
        $clientInfos = $modelClient->get($id_space, $invoice["id_responsible"]);
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];
        $useTTC = true;

        return $this->generatePDF($id_space, $invoice["id"], CoreTranslator::dateFromEn($invoice["date_generated"], $lang), $unit, $resp, $adress, $table, $total, $useTTC, $details, $clientInfos, lang: $lang);
    }

    protected function generatePDFInvoice($id_space, $invoice, $id_item, $lang)
    {
        $tabledata = $this->invoiceTable($id_space, $invoice, $id_item, $lang);
        $table = $tabledata["table"];
        $total = $tabledata["total"];

        $modelClient = new ClClient();
        $clientInfos = $modelClient->get($id_space, $invoice["id_responsible"]);
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];

        $useTTC = true;
        return $this->generatePDF($id_space, $invoice["id"], CoreTranslator::dateFromEn($invoice["date_generated"], $lang), $unit, $resp, $adress, $table, $total, $useTTC, clientInfos: $clientInfos, lang: $lang);
    }

    protected function unparseContent($id_space, $id_item, $lang)
    {
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
                    } elseif ($idDay == "night") {
                        $name .= " " . BookinginvoiceTranslator::night($lang);
                    } elseif ($idDay == "we") {
                        $name .= " " . BookinginvoiceTranslator::WE($lang);
                    }
                } elseif (count($idArray) == 3) {
                    $name = $modelResources->getName($id_space, $idArray[0]);
                    $name .= " " . $modelPackage->getName($id_space, $idArray[2], include_deleted:true);
                }

                $contentList[] = array($name, $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    protected function detailsTable($id_space, $id_invoice, $lang)
    {
        $data = $this->detailsData($id_space, $id_invoice);

        $table = "<h3>".BookinginvoiceTranslator::Details($lang) . "</h3><br/>";
        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 30%; border: solid 1px black;\">" . BookinginvoiceTranslator::Resource($lang) . "</th>
                        <th style=\"width: 50%; border: solid 1px black;\">" . BookinginvoiceTranslator::Recipient($lang) . "</th>
                        <th style=\"width: 20%; border: solid 1px black;\">" . InvoicesTranslator::Quantity($lang) . "</th>
                    </tr>
        ";

        foreach ($data as $d) {
            $table .= "<tr>";
            $table .= "<td style=\"width: 30%; text-align: left; border: solid 1px black;\">" . $d['resource'] . "</td>";
            $table .= "<td style=\"width: 50%; border: solid 1px black;\">" . $d['user'] . "</td>";
            $table .= "<td style=\"width: 20%; text-align: right; border: solid 1px black;\">" . $d['time'] . "</td>";
            $table .= "</tr>";
            $table .= "<tr>";
            $table .= "<td style=\"width: 30%; border: solid 1px black;\"></td>";
            $table .= "<td aria-label=\"details\" colspan=\"2\" style=\"text-align: center; border: solid 1px black;\">";
            $table .= "<span>".BookinginvoiceTranslator::Day($lang).": ". $d['day']."</span>, ";
            $table .= "<span>".BookinginvoiceTranslator::night($lang).": ". $d['night']."</span>, ";
            $table .= "<span>".BookinginvoiceTranslator::WE($lang).": ". $d['we']."</span>";
            $table .= "</td>";
            $table .= "</tr>";
        }

        $table .= "</table>";
        return $table;
    }

    public function detailsData($id_space, $id_invoice)
    {
        $modelCalEntry = new BkCalendarEntry();
        $modelResource = new ResourceInfo();
        $modelUser = new CoreUser();

        $resources = $modelCalEntry->getResourcesForInvoice($id_space, $id_invoice);
        $data = array();
        foreach ($resources as $r) {
            $users = $modelCalEntry->getResourcesUsersForInvoice($id_space, $r['resource_id'], $id_invoice);
            foreach ($users as $user) {
                $resas = $modelCalEntry->getResourcesUserResaForInvoice($id_space, $r['resource_id'], $user['recipient_id'], $id_invoice);
                $time = 0;
                $time_day = 0;
                $time_night = 0;
                $time_we = 0;
                for ($i = 0; $i < count($resas); $i++) {
                    $slots = $modelCalEntry->computeDuration($id_space, $resas[$i]);
                    $resaDayNightWe = $slots['hours'];
                    $time += $slots['total'];
                    $time_day += $resaDayNightWe['nb_hours_day'];
                    $time_night += $resaDayNightWe['nb_hours_night'];
                    $time_we += $resaDayNightWe['nb_hours_we'];
                }
                $data[] = array('resource' => $modelResource->getName($id_space, $r['resource_id']), 'user' => $modelUser->getUserFUllName($user['recipient_id']), 'time' => round($time / 3600, 2), 'day' => $time_day, 'night' => $time_night, 'we' => $time_we);
            }
        }
        return $data;
    }

    public function detailsAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $data = $this->detailsData($id_space, $id_invoice);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->get($id_space, $id_invoice);

        $modelBk = new BkCalendarEntry();
        $entries = $modelBk->getInvoiceEntries($id_space, $id_invoice);
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getBySpace($id_space);
        $rmap = [];
        foreach ($resources as $r) {
            $rmap[$r['id']] = $r['name'];
        }

        $table = new TableView();
        $table->setTitle(BookinginvoiceTranslator::Details($lang) . ": " . $invoiceInfo["number"], 3);

        $headers = array("resource" => BookinginvoiceTranslator::Resource($lang),
            "user" => BookinginvoiceTranslator::Recipient($lang),
            "time" => BookinginvoiceTranslator::Duration($lang),
            "day" =>  BookinginvoiceTranslator::Day($lang),
            "night" =>  BookinginvoiceTranslator::night($lang),
            "we" =>  BookinginvoiceTranslator::WE($lang),
        );

        $tableHtml = $table->view($data, $headers);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "tableHtml" => $tableHtml, 'entries' => $entries, 'resources' => $rmap));
    }
}
