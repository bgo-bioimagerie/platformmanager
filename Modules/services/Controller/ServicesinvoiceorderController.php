<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SePrice.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';

//require_once 'Modules/statistics/Model/StatisticsTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesinvoiceorderController extends InvoiceAbstractController {
    //private $serviceModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
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
            $unitId = $this->request->getParameterNoException("id_unit");
            $respId = $this->request->getParameterNoException("id_resp");
            if ($unitId != 0 && $respId != 0) {
                $this->generateRespBill($dateBegin, $dateEnd, $unitId, $respId, $id_space);
                $this->redirect("invoices/" . $id_space);
                return;
            }
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "htmlForm" => $formUnit->getHtml($lang)));
    }
    
    /*
    public function getHeaders($lang) {
        return array(ServicesTranslator::service($lang), ServicesTranslator::Quantity($lang), ServicesTranslator::UnitPrice($lang));
    }

    public function getItemInfo($id_item) {
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_item);
        $contentArray = explode(";", $item["content"]);
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $itemInfo[] = $data;
            }
        }
        return $itemInfo;
    }
    */

    public function editAction($id_space, $id_invoice, $pdf = 0) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        
        $modelInvoice = new InInvoice();
        $modelInvoiceItem = new InInvoiceItem();
        $invoice = $modelInvoice->get($id_invoice);
        $id_items = $modelInvoiceItem->getInvoiceItems($id_invoice);
        $lang = $this->getLanguage();

        if ($pdf == 1) {
            $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }

        $details = $this->unparseDetails($id_items);
        $form = $this->editForm($id_items[0]["id"], $id_space, $id_invoice, $lang);

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
            $modelInvoiceItem->editItemContent($id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($id_invoice, $total_ht);
            $this->redirect("servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/O");
        }

        $formHtml = $form->getHtml($lang);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "details" => $details, "htmlForm" => $formHtml,
            "invoice" => $invoice), "editformAction");
    }

    public function deleteAction($id_space, $id_invoice) {
        
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        
        // get orders
        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_invoice);
        $details = $this->unparseDetails($id_items);
        $modelOrder = new SeOrder();
        
        // re-open orders and remove invoice number
        foreach ($details as $detail) {
            $modelOrder->reopenEntry($detail[0]);
            $modelOrder->setInvoiceID($detail[0], 0);
        }
    }

    protected function createByUnitForm($id_space, $lang) {
        $form = new Form($this->request, "invoicebyunitform");
        $form->setTitle(ServicesTranslator::Invoice_by_unit($lang), 3);

        $unitId = $this->request->getParameterNoException("id_unit");
        $respId = $this->request->getParameterNoException("id_resp");
        $dateBegin = $this->request->getParameterNoException("date_begin");
        $dateEnd = $this->request->getParameterNoException("date_end");

        $modelUnit = new EcUnit();
        $units = $modelUnit->getUnitsForList("name");

        $modelUser = new EcUser();
        $resps = $modelUser->getResponsibleOfUnit($unitId);

        $form->addDate("date_begin", ServicesTranslator::Date_begin($lang), true, $dateBegin);
        $form->addDate("date_end", ServicesTranslator::Date_end($lang), true, $dateEnd);
        $form->addSelect("id_unit", EcosystemTranslator::Units($lang), $units["names"], $units["ids"], $unitId, true);
        $form->addSelect("id_resp", EcosystemTranslator::Responsible($lang), $resps["names"], $resps["ids"], $respId);
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Ok($lang), "servicesinvoiceorder/" . $id_space);

        return $form;
    }

    private function generateRespBill($dateBegin, $dateEnd, $id_unit, $id_resp, $id_space) {

        $modelOrder = new SeOrder();
        $modelInvoice = new InInvoice();
        $modelInvoiceItem = new InInvoiceItem();
        $modelUnit = new EcUnit();
        // select all the opened order
        $orders = $modelOrder->openedForRespPeriod($dateBegin, $dateEnd, $id_resp);

        if (count($orders) == 0) {
            throw new Exception("there are no orders open for this responsible");
            //echo "there are no orders open for this responsible";
            //return;
        }
        
        $lang = $this->getLanguage();

        // get the bill number
        $number = $modelInvoice->getNextNumber();
        $module = "services";
        $controller = "servicesinvoiceorder";
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, $number, date("Y-m-d", time()), $id_unit, $id_resp);
        $modelInvoice->setEditedBy($id_invoice, $_SESSION["id_user"]);
        $modelInvoice->setTitle($id_invoice, "Prestations: période du " . CoreTranslator::dateFromEn($dateBegin, $lang) . " au " . CoreTranslator::dateFromEn($dateEnd, $lang));
        
        // add the counts to the Invoice
        $services = $modelOrder->openedItemsForResp($id_resp);
        $belonging = $modelUnit->getBelonging($id_unit, $id_space);
        $content = $this->parseServicesToContent($services, $belonging);
        $details = $this->parseOrdersToDetails($orders, $id_space);
        
        $total_ht = $this->calculateTotal($services, $belonging);

        $modelInvoiceItem->setItem(0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_invoice, $total_ht);
        
        // close orders
        foreach ($orders as $order) {
            $modelOrder->setEntryCloded($order["id"]);
            $modelOrder->setInvoiceID($order["id"], $id_invoice);
        }
    }

    protected function parseOrdersToDetails($orders, $id_space) {
        $details = "";
        foreach ($orders as $order) {
            //echo "<br/>";
            //print_r($order);
            $details .= $order["no_identification"] . "=servicesorderedit/" . $id_space . "/" . $order["id"] . ";";
        }
        return $details;
    }

    protected function parseServicesToContent($services, $id_belonging) {
        $content = "";
        $addedServices = array();
        $modelPrice = new SePrice();
        for ($i = 0; $i < count($services); $i++) {
            $quantity = 0;
            if (!in_array($services[$i]["id_service"], $addedServices)) {
                for ($j = $i; $j < count($services); $j++) {
                    if ($services[$j]["id_service"] == $services[$i]["id_service"]) {
                        $quantity += floatval($services[$j]["quantity"]);
                    }
                }
                $price = $modelPrice->getPrice($services[$i]["id_service"], $id_belonging);
                $addedServices[] = $services[$i]["id_service"];
                $content .= $services[$i]["id_service"] . "=" . $quantity . "=" . $price . ";";
            }
        }
        return $content;
    }

    protected function calculateTotal($services, $id_belongings) {
        $total_HT = 0;
        $modelPrice = new SePrice();
        foreach ($services as $service) {
            $price = $modelPrice->getPrice($service["id_service"], $id_belongings);
            $total_HT += floatval($service["quantity"]) *  floatval($price);
        }
        return $total_HT;
    }

    protected function unparseContent($id_item) {

        $modelServices = new SeService();
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $contentList[] = array($modelServices->getItemName($data[0]), $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang) {

        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $modelInvoiceItem = new InInvoiceItem();

        //print_r($id_item);
        $item = $modelInvoiceItem->getItem($id_item);

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
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $formAdd = new FormAdd($this->request, "editinvoiceorderformadd");
        $formAdd->addSelect("id_service", ServicesTranslator::service($lang), $services["names"], $services["ids"], $itemServices);
        $formAdd->addNumber("quantity", ServicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addNumber("unit_price", ServicesTranslator::UnitPrice($lang), $itemPrices);
        //$formAdd->addHidden("id_item", $itemIds);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceorderform");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceorderedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        return $form;
    }

    protected function unparseDetails($id_items) {

        $details = array();
        $modelItems = new InInvoiceItem();
        foreach ($id_items as $item) {
            $itemData = $modelItems->getItem($item[0]);
            //print_r($itemData) . "<br/>";
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
        $content = $this->unparseContent($id_item);
        //print_r($invoice);
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
        $table .= "</table>";

        $modelUnit = new EcUnit();
        $unit = $modelUnit->getUnitName($invoice["id_unit"]);
        $adress = $modelUnit->getAdress($invoice["id_unit"]);
        $modelUser = new EcUser();
        $resp = $modelUser->getUserFUllName($invoice["id_responsible"]);
        $this->genreratePDF($id_space, $invoice["number"], $invoice["date_generated"], $unit, $resp, $adress, $table, $total);
    }

}
