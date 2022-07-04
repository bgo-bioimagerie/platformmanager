<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Events.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/services/Model/ServicesInvoice.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeServiceType.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ServicesinvoiceorderController extends InvoiceAbstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $_SESSION["openedNav"] = "invoices";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formUnit = $this->createByUnitForm($id_space, $lang);
        if ($formUnit->check()) {
            $dateBegin = $this->request->getParameterNoException("date_begin");
            $dateEnd = $this->request->getParameterNoException("date_end");
            $clientId = $this->request->getParameterNoException("id_client");
            if ($clientId != '') {
                $this->generateClientBill($dateBegin, $dateEnd, $clientId, $id_space);
                return $this->redirect("invoices/" . $id_space);
            }
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "htmlForm" => $formUnit->getHtml($lang)));
    }

    public function editAction($id_space, $id_invoice, $pdf = 0) {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modelInvoice = new InInvoice();
        $modelInvoiceItem = new InInvoiceItem();
        $invoice = $modelInvoice->get($id_space, $id_invoice);
        $id_items = $modelInvoiceItem->getInvoiceItems($id_space, $id_invoice);
        $lang = $this->getLanguage();

        if ($pdf == 1) {
            $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }

        $details = $this->unparseDetails($id_space, $id_items);
        $form = $this->editForm($id_items[0]["id"], $id_space, $id_invoice, $lang);
        $formAddName = $form->getFormAddId();

        if ($form->check() && $pdf == 0) {
            $total_ht = 0;
            $id_services = $this->request->getParameter("id_service");
            $quantity = $this->request->getParameter("quantity");
            $unit_price = $this->request->getParameter("unit_price");
            $content = "";
            for ($i = 0; $i < count($id_services); $i++) {
                $content .= $id_services[$i] . "=" . $quantity[$i] . "=" . $unit_price[$i] . ";";
                $total_ht += $quantity[$i] * $unit_price[$i];
            }
            // apply discount
            $discount = $form->getParameter("discount");
            $total_ht = (1-floatval($discount)/100)*$total_ht;


            $modelInvoiceItem->editItemContent($id_space, $id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($id_space, $id_invoice, $discount);

            $_SESSION['flash'] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $_SESSION['flashClass'] = 'success';
            
            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($id_space)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);
            $this->redirect("servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/O");
            return;
        }

        $formHtml = $form->getHtml($lang);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "formAddName" => $formAddName, "details" => $details, "htmlForm" => $formHtml,
            "invoice" => $invoice), "editformAction");
    }

    public function deleteAction($id_space, $id_invoice) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // get orders
        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_space, $id_invoice);
        $details = $this->unparseDetails($id_space ,$id_items);
        $modelOrder = new SeOrder();

        // re-open orders and remove invoice number
        foreach ($details as $detail) {
            $modelOrder->reopenEntry($id_space, $detail[0]);
            $modelOrder->setInvoiceIDByNum($id_space, $detail[0], 0);
        }
    }

    protected function createByUnitForm($id_space, $lang) {
        $form = new Form($this->request, "invoicebyunitform");
        $form->setTitle(ServicesTranslator::Invoice_by_client($lang));

        $clientId = $this->request->getParameterNoException("id_client");
        $dateBegin = $this->request->getParameterNoException("date_begin");
        $dateEnd = $this->request->getParameterNoException("date_end");

        $modelClient = new ClClient();
        $clients = $modelClient->getAll($id_space);
        $clientsNames = [];
        $clientsIds = [];
        
        foreach($clients as $client) {
            array_push($clientsNames, $client['name']);
            array_push($clientsIds, $client['id']);
        }

        $form->addDate("date_begin", ServicesTranslator::Date_begin($lang), true, $dateBegin);
        $form->addDate("date_end", ServicesTranslator::Date_end($lang), true, $dateEnd);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clientsNames, $clientsIds, $clientId, false);

        $form->setValidationButton(CoreTranslator::Ok($lang), "servicesinvoiceorder/" . $id_space);

        return $form;
    }

    private function generateClientBill($beginPeriod, $endPeriod, $id_client, $id_space) {
        $cv = new CoreVirtual();
        $rid = $cv->newRequest($id_space, "invoices", "orders[$id_client]:$beginPeriod => $endPeriod");
        Events::send([
            "action" => Events::ACTION_INVOICE_REQUEST,
            "space" => ["id" => intval($id_space)],
            "user" => ["id" => $_SESSION['id_user']],
            "type" => ServicesInvoice::$INVOICES_SERVICES_ORDERS_CLIENT,
            "period_begin" => $beginPeriod,
            "period_end" => $endPeriod,
            "id_client" => $id_client,
            "request" => ["id" => $rid]
        ]);
    }

    protected function unparseContent($id_space ,$id_item) {

        $modelServices = new SeService();
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_space, $id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $contentList[] = array($modelServices->getItemName($id_space, $data[0]), $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang) {

        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $itemQuantityTypes = array();
        $modelInvoiceItem = new InInvoiceItem();
        $modelServices = new SeService();
        $modelSeTypes = new SeServiceType();
        $item = $modelInvoiceItem->getItem($id_space, $id_item);
        $contentArray = explode(";", $item["content"]);
        $total = 0;
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                $itemQuantities[] = $data[1];
                $itemQuantityTypes[] = $modelSeTypes->getType($modelServices->getItemType($id_space, $data[0]));
                $itemPrices[] = $data[2];
                if (is_numeric($data[1]) && is_numeric($data[2])) {
                    $total += $data[1] * $data[2];
                } else {
                    $_SESSION['flash'] = InvoicesTranslator::NonNumericValue($lang);
                    $_SESSION['flashClass'] = 'danger';
                }
            }
        }
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $formAdd = new FormAdd($this->request, "editinvoiceorderformadd");
        $formAdd->addSelect("id_service", ServicesTranslator::service($lang), $services["names"], $services["ids"], $itemServices);
        $formAdd->addFloat("quantity", ServicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addLabel("type", $itemQuantityTypes);
        $formAdd->addFloat("unit_price", ServicesTranslator::UnitPrice($lang), $itemPrices);
        //$formAdd->addHidden("id_item", $itemIds);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceorderform");

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);

        $modelInvoice = new InInvoice();
        $discount = $modelInvoice->getDiscount($id_space ,$id_invoice);
        $form->addText("discount", ServicesTranslator::Discount($lang), false, $discount);

        $total = (1-floatval($discount)/100)*$total;
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        return $form;
    }

    protected function unparseDetails($id_space, $id_items) {

        $details = array();
        $modelItems = new InInvoiceItem();
        foreach ($id_items as $item) {
            $itemData = $modelItems->getItem($id_space, $item[0]);
            $dArray = explode(";", $itemData["details"]);

            foreach ($dArray as $d) {
                $de = explode("=", $d);
                if (count($de) == 2) {
                    $details[] = $de;
                }
            }
        }

        return $details;
    }

    protected function generatePDFInvoice($id_space, $invoice, $id_item, $lang) {

        $table = "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 52%\">" . InvoicesTranslator::Designation($lang) . "</th>
                        <th style=\"width: 14%\">" . InvoicesTranslator::Quantity($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::UnitPrice($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::Price_HT($lang) . "</th>
                    </tr>
                </table>
        ";


        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #F7F7F7; text-align: center; font-size: 10pt;\">";
        $content = $this->unparseContent($id_space, $id_item);
        $total = 0;
        foreach ($content as $d) {
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d[0] . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format(floatval($d[1]), 2, ',', ' ') . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format(floatval($d[2]), 2, ',', ' ') . " &euro;</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format(floatval($d[1] * $d[2]), 2, ',', ' ') . " &euro;</td>";
            $table .= "</tr>";
            $total += floatval($d[1]) * floatval($d[2]);
        }
        $discount = floatval($invoice["discount"]);
        if($discount>0){
            $total = (1-$discount/100)*$total;
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . InvoicesTranslator::Discount($lang) . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . 1 . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        
        $modelClient = new ClClient();
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $clientInfos = $modelClient->get($id_space, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];
        $this->generatePDF($id_space, $invoice["id"], $invoice["date_generated"], $unit, $resp, $adress, $table, $total, clientInfos: $clientInfos, lang: $lang);
    }

}
