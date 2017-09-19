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

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }
        
        $this->unitModel = new EcUnit ();
        $_SESSION["openedNav"] = "ecusers";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function unitsAction($id_space) {
        
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
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang),
            'lang' => $lang
        ));
    }
    
    protected function mergeUnits($units){
        
        // 2- EcUser change the unit to reference
        $modelUser = new EcUser();
        $modelUser->mergeUnits($units);
        
        // 3- Change id_unit to reference unit in in_invoices
        $inInvoices = new InInvoice();
        $inInvoices->mergeUnits($units);
        
        // 4- Remove the not reference units
        $this->unitModel->mergeUnits($units);
    }

}
