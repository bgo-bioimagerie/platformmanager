<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/core/Model/CoreTranslator.php';

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

        if(!file_exists('data/invoices/'.$id_space.'/template.twig') && !file_exists('data/invoices/'.$id_space.'/template.php')) {
            $_SESSION['flash'] = InvoicesTranslator::NoTemplate($lang);
            $_SESSION['flashClass'] = 'warning';
        }
        
        $modelInvoices = new InInvoice();
        $modelUser = new CoreUser();
        
        $modelConfig = new CoreConfig();
        $invoiceperiodbegin = $modelConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $invoiceperiodend = $modelConfig->getParamSpace("invoiceperiodend", $id_space);

        $years = $modelInvoices->allPeriodYears($id_space, $invoiceperiodbegin, $invoiceperiodend);
        if ($year == "") {
            if(empty($years)){
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
        $data = $modelInvoices->getSentByPeriod($id_space, $sent, $dates["yearBegin"], $dates["yearEnd"], "number");
        $invoices = $data;
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

        $cv = new CoreVirtual();
        $requests = $cv->getRequests($id_space, 'invoices');

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableView,
            "requests" => $requests,
            "year" => $year,
            "years" => $years,
            "sent" => $sent,
            "data" => ['invoices' => $data]
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
        if(!$service) {
            throw new PfmUserException('invoice not found', 404);
        }

        //print_r($service);
        
        $controllerName = ucfirst($service["controller"]) . "Controller";
        //echo "<br/> $controllerName";
        //echo '<br/> Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        //return;
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName($this->request, $this->currentSpace);
        //$object->setRequest($this->request);
        Configuration::getLogger()->debug('[invoices][edit]', ['controller' => $service['controller'], 'module' => $service['module'], 'id' => $id]);
        return $object->runAction($service["module"], "edit", ['id_space' => $id_space, 'id_invoice' => $id]);
    }

    public function createPurcentageDiscountForm($discountValue) {

        $lang = $this->getLanguage();
        $form = new Form($this->request, "PurcentageDiscountForm");
        $form->addNumber("discount", InvoicesTranslator::Discount($lang), false, $discountValue);

        return $form->getHtml($lang);
    }

    protected function infoForm($id_space, $id){
        $lang = $this->getLanguage();
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
        $form->addDate("date_generated", InvoicesTranslator::Date_generated($lang), true, $invoice["date_generated"]);
        $form->addDate("date_send", InvoicesTranslator::Date_send($lang), true, $invoice["date_send"]);
        
        $modelVisa = new InVisa();
        $visasList = $modelVisa->getForList($id_space);
        $form->addSelect("visa_send", InvoicesTranslator::Visa_send($lang), $visasList['names'], $visasList['ids'], $invoice["visa_send"]);
        
        $modelConfig = new CoreConfig();
        if($modelConfig->getParamSpace("useInvoiceDatePaid", $id_space) == 1){
            $form->addDate("date_paid", InvoicesTranslator::Date_paid($lang), true, $invoice["date_paid"]);
        }
        else{
            $form->addHidden("date_paid", "");
        }

        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceinfo/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "invoices/" . $id_space);

        if ($form->check()) {
            
            if($this->request->getParameter("date_send") != "" && $this->request->getParameter("visa_send") == 0){
                $message = InvoicesTranslator::TheFieldVisaIsMandatoryWithSend($lang);
                $_SESSION['flash'] = $message;
                $this->redirect("invoiceinfo/".$id_space."/".$id);
                return "";
            }
            
            $datePaid = CoreTranslator::dateToEn($this->request->getParameter("date_paid"), $lang);
            //echo "date paid = " . $datePaid . "<br/>";
            $modelInvoice->setDatePaid($id_space, $id, $datePaid);
            $modelInvoice->setSend($id_space, $id, 
                    CoreTranslator::dateToEn($this->request->getParameter("date_send"), $lang), 
                    $this->request->getParameter("visa_send"));
            
            $_SESSION['flash'] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $_SESSION["flashClass"] = 'success';
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
        $lang = $this->getLanguage();
        // cancel the pricing in the origin module
        $modelInvoice = new InInvoice();
        $service = $modelInvoice->get($id_space, $id);
        if(!$service) {
            $_SESSION['flash'] = InvoicesTranslator::Invoice($lang)." $id ".CoreTranslator::NotFound($lang);
            return $this->redirect("invoices/" . $id_space, [], ['invoice' => null]);
        }


        $controllerName = ucfirst($service["controller"]) . "Controller";
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName($this->request, $this->currentSpace);
        Configuration::getLogger()->debug('[invoices][delete]', ['controller' => $service['controller'], 'module' => $service['module'], 'id' => $id]);
        $object->runAction($service["module"], "delete", array($id_space, $id));
        
        // delete invoice
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->deleteForInvoice($id_space, $id);
        
        $modelInvoice->delete($id_space, $id);

        // redirect
        return $this->redirect("invoices/" . $id_space, [], ['invoice' => $service]);
    }

}
