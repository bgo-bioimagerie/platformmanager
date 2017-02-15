<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

require_once 'Modules/invoices/Model/InInvoice.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class InvoicesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("invoices");
    }

    protected function getInvoicePeriod($id_space, $year) {
        $modelCoreConfig = new CoreConfig();
        $projectperiodbegin = $modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("invoiceperiodend", $id_space);

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
    public function indexAction($id_space, $year = "") {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelInvoices = new InInvoice();

        
        $years = $modelInvoices->allYears($id_space);
        if ($year == "") {
            if(count($years) == 1){
                $year = $years[0];
            }
            $year = $years[count($years) - 1];
        }
        $dates = $this->getInvoicePeriod($id_space, $year);
        $invoices = $modelInvoices->getByPeriod($id_space, $dates["yearBegin"], $dates["yearEnd"], "number");
        for ($i = 0; $i < count($invoices); $i++) {
            $invoices[$i]["date_generated"] = CoreTranslator::dateFromEn($invoices[$i]["date_generated"], $lang);
            $invoices[$i]["date_paid"] = CoreTranslator::dateFromEn($invoices[$i]["date_paid"], $lang);
        }

        $table = new TableView();
        $table->setTitle(InvoicesTranslator::invoices($lang), 3);

        $headers = array("number" => InvoicesTranslator::Number($lang),
            "unit" => EcosystemTranslator::Unit($lang),
            "resp" => EcosystemTranslator::Responsible($lang),
            "date_generated" => InvoicesTranslator::Date_generated($lang),
            "date_paid" => InvoicesTranslator::Date_paid($lang),
            "total_ht" => InvoicesTranslator::Total_HT($lang));

        $table->addLineEditButton("invoiceedit/" . $id_space);
        $table->addLineButton("invoiceinfo/" . $id_space, "id", InvoicesTranslator::Info($lang));
        $table->addDeleteButton("invoicedelete/" . $id_space, "id", "number");
        $tableView = $table->view($invoices, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableView,
                            "year" => $year, "years" => $years
            ));
    }

    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modelInvoice = new InInvoice();
        $service = $modelInvoice->get($id);

        $controllerName = ucfirst($service["controller"]) . "Controller";
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName();
        $object->setRequest($this->request);
        $object->runAction($service["module"], "edit", array($id_space, $id));

        return;
    }
    
    public function createPurcentageDiscountForm($discountValue){
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "PurcentageDiscountForm");
        $form->addNumber("discount", InvoicesTranslator::Discount($lang), false, $discountValue);
        
        return $form->getHtml($lang);
        
    }

    public function infoAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $modelUnit = new EcUnit();
        $modelUser = new EcUser();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id);

        if ($invoice["date_paid"] == "0000-00-00") {
            $invoice["date_paid"] = "";
        }

        $form = new Form($this->request, "infoActionForm");
        $form->setTitle(InvoicesTranslator::InvoiceInfo($lang));
        $form->addText("number", InvoicesTranslator::Number($lang), false, $invoice["number"], false);
        $form->addText("unit", EcosystemTranslator::Units($lang), false, $modelUnit->getUnitName($invoice["id_unit"]), false);
        $form->addText("resp", EcosystemTranslator::Responsible($lang), false, $modelUser->getUserFUllName($invoice["id_responsible"]), false);
        $form->addDate("date_generated", InvoicesTranslator::Date_generated($lang), true, CoreTranslator::dateFromEn($invoice["date_generated"], $lang));
        $form->addDate("date_paid", InvoicesTranslator::Date_paid($lang), true, CoreTranslator::dateFromEn($invoice["date_paid"], $lang));

        $form->setButtonsWidth(3, 8);
        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceinfo/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "invoices/" . $id_space);

        if ($form->check()) {
            $datePaid = CoreTranslator::dateToEn($this->request->getParameter("date_paid"), $lang);
            //echo "date paid = " . $datePaid . "<br/>";
            $modelInvoice->setDatePaid($id, $datePaid);
            $this->redirect("invoices/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // cancel the pricing in the origin module
        $modelInvoice = new InInvoice();
        $service = $modelInvoice->get($id);

        $controllerName = ucfirst($service["controller"]) . "Controller";
        require_once 'Modules/' . $service["module"] . "/Controller/" . $controllerName . ".php";
        $object = new $controllerName();
        $object->setRequest($this->request);
        $object->runAction($service["module"], "delete", array($id_space, $id));

        // delete invoice
        $modelInvoice->delete($id);

        // redirect
        $this->redirect("invoices/" . $id_space);
    }

}
