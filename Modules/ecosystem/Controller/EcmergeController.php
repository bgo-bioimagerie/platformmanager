<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InVisa.php';

require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';

require_once 'Modules/documents/Model/Document.php';

require_once 'Modules/resources/Model/ReVisa.php';

require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeVisa.php';


/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class EcmergeController extends CoresecureController {

    /**
     * User model object
     */
    private $unitModel;
    private $userModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }

        $this->unitModel = new EcUnit ();
        $this->userModel = new EcUser();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function unitsAction() {

        $lang = $this->getLanguage();

        $unitsList = $this->unitModel->getUnitsForList();

        $form = new Form($this->request, "formmergeunits");
        $form->setTitle(EcosystemTranslator::mergeUnits($lang));
        $form->addComment(EcosystemTranslator::ItIsTheFirstTakenIntoAccount($lang));

        $formAdd = new FormAdd($this->request, "formmaddergeunits");
        $formAdd->addSelect("units", CoreTranslator::Unit($lang), $unitsList["names"], $unitsList["ids"]);

        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd);
        $form->setValidationButton(CoreTranslator::Ok($lang), "mergeunits");

        if ($form->check()) {
            $units = $form->getParameter("units");

            $this->mergeUnits($units);
            $_SESSION["message"] = "units has been saved";
            $this->redirect("mergeunits");
        }
        $this->render(array(
            'formHtml' => $form->getHtml($lang),
            'lang' => $lang
        ));
    }

    protected function mergeUnits($units) {

        // 2- EcUser change the unit to reference
        $modelUser = new EcUser();
        $modelUser->mergeUnits($units);

        // 3- Change id_unit to reference unit in in_invoices
        $inInvoices = new InInvoice();
        $inInvoices->mergeUnits($units);

        // 4- Remove the not reference units
        $this->unitModel->mergeUnits($units);
    }

    public function usersAction() {
        $lang = $this->getLanguage();

        $usersList = $this->userModel->getAcivesForSelect("name");

        $form = new Form($this->request, "formmergeusers");
        $form->setTitle(EcosystemTranslator::mergeUsers($lang));
        $form->addComment(EcosystemTranslator::ItIsTheFirstTakenIntoAccount($lang));

        $formAdd = new FormAdd($this->request, "formmaddergeunits");
        $formAdd->addSelect("users", CoreTranslator::User($lang), $usersList["names"], $usersList["ids"]);

        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd);
        $form->setValidationButton(CoreTranslator::Ok($lang), "mergeusers");

        if ($form->check()) {
            $users = $form->getParameter("users");

            $this->mergeUsers($users);
            $_SESSION["message"] = "users has been saved";
            //$this->redirect("mergeusers");
            return;
        }
        $this->render(array(
            'formHtml' => $form->getHtml($lang),
            'lang' => $lang
        ));
    }
    
    protected function mergeUsers($users){
        
        $modelAntibodies = new Anticorps();
        $modelAntibodies->mergeUsers($users);
        
        $modelAuth = new BkAuthorization();
        $modelAuth->mergeUsers($users);
        
        $modelCalendar = new BkCalendarEntry();
        $modelCalendar->mergeUsers($users);
       
        $modelDoc = new Document();
        $modelDoc->mergeUsers($users);
        
        $modelInvoice = new InInvoice();
        $modelInvoice->mergeUsers($users);
        
        $modelVisa = new InVisa();
        $modelVisa->mergeUsers($users);
        
        $modelResResp = new ReResps();
        $modelResResp->mergeUsers($users);
        
        $modelVisaRe = new ReVisa();
        $modelVisaRe->mergeUsers($users);
        
        $modelSeProj = new SeProject();
        $modelSeProj->mergeUsers($users);
        
        $modelVisaSe = new SeVisa();
        $modelVisaSe->mergeUsers($users);
         
        $modelSpace = new CoreSpace();
        $modelSpace->mergeUsers($users);
        
        $modelUserSettings = new CoreUserSettings();
        $modelUserSettings->mergeUsers($users);
        
        $modelUser = new CoreUser();
        $modelUser->mergeUsers($users);
                
        $modelResp = new EcResponsible();
        $modelResp->mergeUsers($users);
        
        $modelEcUser = new EcUser();
        $modelEcUser->mergeUsers($users);
        
    }

}
