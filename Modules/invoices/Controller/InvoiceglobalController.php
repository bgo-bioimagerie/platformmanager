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
class InvoiceglobalController extends InvoiceAbstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "invoices";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $formAll = $this->createAllForm($id_space, $lang);
        if ($formAll->check()) {

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

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formByPeriod" => $formByPeriod->getHtml($lang),
            "formAll" => $formAll->getHtml($lang)
        ));
    }

    public function editAction($id_space, $id_invoice, $pdf = false) {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelItem = new InInvoiceItem();
        $invoiceitem = $modelItem->getForInvoice($id_space, $id_invoice);

        $validateURL = "invoiceglobaledit/" . $id_space . "/" . $id_invoice;

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "invoice" => $invoice,
            "invoiceitem" => $invoiceitem,
            "validateURL" => $validateURL
        ));
    }

    public function pdfAction($id_space, $id_invoice, $details = 0) {
        $lang = $this->getLanguage();
        Configuration::getLogger()->debug("[TEST][GLOBALCTRL]", ["in pdf action"]);
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelItem = new InInvoiceItem();
        $invoiceItem = $modelItem->getForInvoice($id_space, $id_invoice);

        $modelClient = new ClClient();
        
        $number = $invoice["number"];
        $date = $invoice["date_generated"];
        $unit = "";
        $resp = $modelClient->getContactName($id_space, $invoice["id_responsible"]);
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]);
        $content = json_decode($invoiceItem["content"], true);
        $table = $this->invoiceTable($content, $invoice, $lang);

        $detailsTable = "";
        if ($details > 0) {
            $detailsTable = $this->generateDetailsTable($id_space, $id_invoice);
        }

        $this->genreratePDF($id_space, $number, $date, $unit, $resp, $adress, $table["table"], $table["total"], true, $detailsTable);
    }

    public function editqueryAction($id_space, $id_invoice) {

        $lang = $this->getLanguage();

        $discount = $_POST["discount"];
        $total_ht = $_POST["total_ht"];
        $content = $_POST["content"];
        
        //print_r($content);

        $modelInvoice = new InInvoice();
        $modelInvoice->setDiscount($id_space, $id_invoice, $discount);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);

        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);

        
        $modelItem = new InInvoiceItem();
        $modelItem->setItemContent($id_space, $id_invoice, $content);

        echo json_encode(array("status" => "success", "message" => InvoicesTranslator::InvoiceHasBeenSaved($lang)));
        //echo json_encode(array("status" => "success", "message" => $content ));
    }

    public function deleteAction($id_space, $id_invoice) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modules = Configuration::get("modules");
        foreach ($modules as $module) {
            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {

                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();
                $model->delete($id_space, $id_invoice);
            }
        }
        Events::send([
            "action" => Events::ACTION_INVOICE_DELETE,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);
    }

    // ////////////////////////////////////////////////////////////////////// //
    //                      internal methods
    // ////////////////////////////////////////////////////////////////////// //
    protected function invoiceTable($content, $invoice, $lang) {

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

        $total = 0;
        $modules = Configuration::get("modules");
        foreach ($content as $c) {

            foreach ($c["data"]["count"] as $d) {
                if ($d["unitprice"] > 0) {
                    $table .= "<tr>";
                    $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d["label"] . "</td>";
                    $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format($d["quantity"], 2, ',', ' ') . "</td>";
                    $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d["unitprice"], 2, ',', ' ') . " &euro;</td>";
                    $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d["quantity"] * $d["unitprice"], 2, ',', ' ') . " &euro;</td>";
                    $table .= "</tr>";
                    $total += floatval($d["quantity"]) * floatval($d["unitprice"]);
                }
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

    protected function createAllForm($id_space, $lang) {
        $form = new Form($this->request, "GlobalInvoiceAllForm");
        $form->addSeparator(InvoicesTranslator::Invoice_All($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));

        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceglobal/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang) {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(InvoicesTranslator::Invoice_Responsible($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClients = new ClClient();
        $resps = $modelClients->getForList($id_space);
        
        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);
        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceglobal/" . $id_space);
        return $form;
    }

    protected function invoiceAll($id_space, $beginPeriod, $endPeriod) {

        $modules = Configuration::get("modules");

        $modelUser = new CoreUser();
        $resps = $modelUser->getResponsibles(); 
        
       
        foreach( $resps as $resp ){
        
            $found = false;
            foreach ($modules as $module) {
                $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
                if (file_exists($invoiceModelFile)) {
                    require_once $invoiceModelFile;
                    $modelName = ucfirst(strtolower($module)) . "Invoice";
                    $model = new $modelName();

                    if ($model->hasActivity($id_space, $beginPeriod, $endPeriod, $resp["id"])) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found){
                $this->invoice($id_space, $beginPeriod, $endPeriod, $resp["id"]);
            }
        }
    }

    protected function invoice($id_space, $beginPeriod, $endPeriod, $id_resp) {

        $lang = $this->getLanguage();

        // create invoice in the database
        $modelInvoice = new InInvoice();
        $invoiceNumber = $modelInvoice->getNextNumber();
        $id_invoice = $modelInvoice->addInvoice("invoices", "invoiceglobal", $id_space, $invoiceNumber, date("Y-m-d", time()), $id_resp, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $_SESSION["id_user"]);
        $modelInvoice->setTitle($id_space, $id_invoice, "Facturation: période du " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " au " . CoreTranslator::dateFromEn($endPeriod, $lang));

        // get invoice content
        $modules = Configuration::get("modules");
        $invoiceDataArray = array();
        $total_ht = 0;
        foreach ($modules as $module) {

            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {

                //echo "invoice module " . $invoiceModelFile . "<br/>";
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                $moduleArray = array();
                $moduleArray["module"] = $module;
                $moduleArray["data"] = $model->invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $id_invoice, $lang);
                $invoiceDataArray[] = $moduleArray;
                //echo 'total HT ' . $module . " = " . $moduleArray["data"]["total_ht"] . "<br/>";
                //print_r($moduleArray);
                //echo "<br/>";
                $total_ht += floatval($moduleArray["data"]["total_ht"]);
            }
        }

        // set invoice content to the database
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, "invoices", "invoiceglobal", json_encode($invoiceDataArray), "", $total_ht);

        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);
    }

    protected function generateDetailsTable($id_space, $invoice_id) {

        $lang = $this->getLanguage();

        $html = "";
        $modules = Configuration::get("modules");
        foreach ($modules as $module) {

            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {

                //echo "invoice module " . $invoiceModelFile . "<br/>";
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                $details = $model->details($id_space, $invoice_id, $lang);
                if (isset($details["title"])) {
                    $html .= $this->detailsArrayToHtmlTable($details, $lang);
                }
            }
        }
        return $html;
    }

    protected function detailsArrayToHtmlTable($detail, $lang) {

        $colWidth = round(100 / count($detail["header"]));

        $table = $detail["title"] . "<br/>";
        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>";

        foreach ($detail["header"] as $key => $value) {
            $table .= '<th style="width: ' . $colWidth . '%">' . $value . '</th>';
        }


        $table .= "</tr>" .
                " </table>";

        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; border-collapse: collapse; background: #F7F7F7; text-align: center; font-size: 10pt;\">";

        foreach ($detail["content"] as $d) {
            $table .= "<tr>";

            foreach ($detail["header"] as $key => $value) {
                $table .= '<td style="width: ' . $colWidth . '%; text-align: left; border: solid 1px black;">' . $d[$key] . '</td>';
            }
            $table .= "</tr>";
        }

        $table .= "</table><br/>";
        return $table;
    }

}
