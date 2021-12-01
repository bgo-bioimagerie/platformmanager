<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InVisa.php';

require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/invoices/Controller/InvoicesController.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class InvoiceslistController extends InvoicesController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("invoices");
        $_SESSION["openedNav"] = "invoices";
    }
    
    protected function getInvoicePeriod($id_space, $year) {
        $modelCoreConfig = new CoreConfig();
        $projectperiodbegin = $modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("invoiceperiodend", $id_space);

        if(!$projectperiodbegin) {
            $projectperiodbegin = "0000-01-01";
        }
        if(!$projectperiodend) {
            $projectperiodend = "0000-12-31";
        }

        $projectperiodbeginArray = explode("-", $projectperiodbegin);
        $previousYear = $year - 1;
        $yearBegin = $previousYear . "-" . $projectperiodbeginArray[1] . "-" . $projectperiodbeginArray[2];
        $projectperiodendArray = explode("-", $projectperiodend);
        $yearEnd = $year . "-" . $projectperiodendArray[1] . "-" . $projectperiodendArray[2];

        return array("yearBegin" => $yearBegin, "yearEnd" => $yearEnd);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $sent, $year = "") {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($sent == ""){
            $sent = 0;
        }
        
        $modelInvoices = new InInvoice();
        $modelUser = new CoreUser();
        
        $modelConfig = new CoreConfig();
        $invoiceperiodbegin = $modelConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $invoiceperiodend = $modelConfig->getParamSpace("invoiceperiodend", $id_space);

        $years = $modelInvoices->allPeriodYears($id_space, $invoiceperiodbegin, $invoiceperiodend);
        //$years = $modelInvoices->allYears($id_space);
        if ($year == "") {
            if(count($years) == 0){
                $year = date('Y');
            }
            else if (count($years) == 1) {
                $year = $years[0];
            }
            else{
                $year = $years[count($years) - 1];
            }
        }
        $dates = $this->getInvoicePeriod($id_space, $year);
        $invoices = $modelInvoices->getSentByPeriod($id_space, $sent, $dates["yearBegin"], $dates["yearEnd"], "number");
        for ($i = 0; $i < count($invoices); $i++) {
            $invoices[$i]["date_generated"] = CoreTranslator::dateFromEn($invoices[$i]["date_generated"], $lang);
            $invoices[$i]["date_paid"] = CoreTranslator::dateFromEn($invoices[$i]["date_paid"], $lang);
            $invoices[$i]["edited_by"] = $modelUser->getUserFUllName($invoices[$i]["id_edited_by"]);
        }

        $table = new TableView();
        
        if($sent == 1){
            $table->setTitle(InvoicesTranslator::Sent_invoices($lang), 3);
        }
        else{
            $table->setTitle(InvoicesTranslator::To_Send_invoices($lang), 3);
        }

        $headers = array(
            "number" => InvoicesTranslator::Number($lang),
            "title" => InvoicesTranslator::Title($lang),
            "resp" => ClientsTranslator::ClientAccount($lang),
            "date_generated" => InvoicesTranslator::Date_generated($lang),
            "date_paid" => InvoicesTranslator::Date_paid($lang),
            "total_ht" => InvoicesTranslator::Total_HT($lang),
            "edited_by" => InvoicesTranslator::Edited_by($lang)
        );

        
        $table->addLineEditButton("invoiceedit/" . $id_space);
        if($sent == 0){
            $table->addLineButton("invoiceinfo/" . $id_space, "id", InvoicesTranslator::SendStatus($lang));
        }
        $table->addDeleteButton("invoicedelete/" . $id_space, "id", "number");
        if($sent == 1){
            $table->addLineButton("invoiceinfo/" . $id_space, "id", InvoicesTranslator::Info($lang));
        }
        $tableView = $table->view($invoices, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableView,
            "year" => $year, "years" => $years, "sent" => $sent
        ));
    }
    
    public function sentAction($id_space, $year = ""){
        $this->redirect("invoices/".$id_space."/1/".$year);
    }
    
    public function tosendAction($id_space, $year = ""){
        $this->redirect("invoices/".$id_space."/0/".$year);
    }

    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modelInvoice = new InInvoice();
        $service = $modelInvoice->get($id_space, $id);

        //print_r($service);
        
        $controllerName = ucfirst($service["controller"]) . "Controller";
        //echo "<br/> $controllerName";
        //echo '<br/> Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        //return;
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName(new Request(array(), false));
        $object->setRequest($this->request);
        $object->runAction($service["module"], "edit", array($id_space, $id));

        return;
    }

    public function createPurcentageDiscountForm($discountValue) {

        $lang = $this->getLanguage();
        $form = new Form($this->request, "PurcentageDiscountForm");
        $form->addNumber("discount", InvoicesTranslator::Discount($lang), false, $discountValue);

        return $form->getHtml($lang);
    }

    protected function infoForm($id_space, $id){
        $lang = $this->getLanguage();
        //$modelClient = new ClClient();
        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id);

        if ($invoice["date_paid"] == "0000-00-00") {
            $invoice["date_paid"] = "";
        }

        $form = new Form($this->request, "infoActionForm");
        $form->setTitle(InvoicesTranslator::InvoiceInfo($lang));
        $form->addText("number", InvoicesTranslator::Number($lang), false, $invoice["number"], false);
        $form->addText("resp", ClientsTranslator::ClientAccount($lang), false, $modelClient->getName($id_space, $invoice["id_responsible"]), false);
        $form->addDate("date_generated", InvoicesTranslator::Date_generated($lang), true, CoreTranslator::dateFromEn($invoice["date_generated"], $lang));
        $form->addDate("date_send", InvoicesTranslator::Date_send($lang), true, CoreTranslator::dateFromEn($invoice["date_send"], $lang));
        
        $modelVisa = new InVisa();
        $visasList = $modelVisa->getForList($id_space);
        $form->addSelect("visa_send", InvoicesTranslator::Visa_send($lang), $visasList['names'], $visasList['ids'], $invoice["visa_send"]);
        
        $modelConfig = new CoreConfig();
        if($modelConfig->getParamSpace("useInvoiceDatePaid", $id_space) == 1){
            $form->addDate("date_paid", InvoicesTranslator::Date_paid($lang), true, CoreTranslator::dateFromEn($invoice["date_paid"], $lang));
        }
        else{
            $form->addHidden("date_paid", "");
        }
        $form->setButtonsWidth(3, 8);
        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceinfo/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "invoices/" . $id_space);

        if ($form->check()) {
            
            if($this->request->getParameter("date_send") != "" && $this->request->getParameter("visa_send") == 0){
                $message = InvoicesTranslator::TheFieldVisaIsMandatoryWithSend($lang);
                $_SESSION["message"] = $message;
                $this->redirect("invoiceinfo/".$id_space."/".$id);
                return;
            }
            
            $datePaid = CoreTranslator::dateToEn($this->request->getParameter("date_paid"), $lang);
            //echo "date paid = " . $datePaid . "<br/>";
            $modelInvoice->setDatePaid($id_space, $id, $datePaid);
            $modelInvoice->setSend($id_space, $id, 
                    CoreTranslator::dateToEn($this->request->getParameter("date_send"), $lang), 
                    $this->request->getParameter("visa_send"));
            
            $_SESSION["message"] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $this->redirect("invoiceinfo/" . $id_space . "/" . $id);
            return "";
        }
        else{
            return $form->getHtml($lang);
        }
    }
    
    public function infoAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $formHtml = $this->infoForm($id_space, $id);
        if($formHtml) {
            $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $formHtml));
        }
    }

    public function deleteAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // cancel the pricing in the origin module
        $modelInvoice = new InInvoice();
        $service = $modelInvoice->get($id_space, $id);

        $controllerName = ucfirst($service["controller"]) . "Controller";
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName(new Request(array(), false));
        $object->setRequest($this->request);
        $object->runAction($service["module"], "delete", array($id_space, $id));
        
        // delete invoice
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->deleteForInvoice($id_space, $id);
        
        $modelInvoice->delete($id_space, $id);

        // redirect
        $this->redirect("invoices/" . $id_space);
    }

}
