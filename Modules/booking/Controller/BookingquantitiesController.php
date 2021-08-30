<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingquantitiesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bookingsettings");
        $_SESSION["openedNav"] = "bookingsettings";
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
        
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);
        $choicesR = array(); $choicesRid = array();
        foreach($resources as $res){
            $choicesR[] = $res["name"];
            $choicesRid[] = $res["id"];
        }
        
        $modelSups = new BkCalQuantities();
        $sups = $modelSups->getForSpace($id_space, "id_resource");
        $supsIds = array();
        $supsIdsRes = array();
        $supsNames = array();
        $supsMandatories = array();
        foreach($sups as $p){
            $supsIds[] = $p["id_quantity"];
            $supsIdsRes[] = $p["id_resource"];
            $supsNames[] = $p["name"];
            $supsMandatories[] = $p["mandatory"];
            $supIsInvoicingUnit[] = $p["is_invoicing_unit"] ? intval($p["is_invoicing_unit"]) : 0;
        }
        
        $form = new Form($this->request, "supsForm");
        $form->setTitle(BookingTranslator::Quantities($lang));
        
        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang) , $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);
        $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang) , array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $supsMandatories);
        $formAdd->addSelect("is_invoicing_unit", BookingTranslator::Is_invoicing_unit($lang) , array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $supIsInvoicingUnit);
        
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingquantities/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()) {
            $supID = $this->request->getParameterNoException("id_sups");
            $supResource = $this->request->getParameterNoException("id_resources");
            $supName = $this->request->getParameterNoException("names");
            $supMandatory = $this->request->getParameterNoException("mandatory");
            $supIsInvoicingUnit = $this->request->getParameterNoException("is_invoicing_unit");

            // format into arrays
            $supIsInvoicingUnit = is_array($supIsInvoicingUnit) ? $supIsInvoicingUnit : [$supIsInvoicingUnit];
            $supID = is_array($supID) ? $supID : [$supID];

            // find out if multiple quantities are used as invoicing units
            if (count(array_keys($supIsInvoicingUnit, 1)) > 1) {
                $_SESSION["message"] = ["content" => BookingTranslator::maxInvoicingUnits($lang), "type" => "alert-danger", "dismissible" => true];
            } else {
                $count = 0;
            
                // get the last package id
                $lastID = 0;
                for ($p = 0 ; $p < count($supID) ; $p++) {
                    if ($supName[$p] != "" && $supID[$p] > $lastID ) {
                        $lastID = $supID[$p];
                    }
                }
                    
                for ($p = 0 ; $p < count($supID) ; $p++) {
                    if ($supName[$p] != "" ) {
                        $curentID = $supID[$p];

                        if ($curentID == "") {
                            $lastID++;
                            $curentID = $lastID;
                            $supID[$p] = $lastID;
                        }
                        if ($curentID == 1 && $p > 0) {
                            $lastID++;
                            $curentID = $lastID;
                            $supID[$p] = $lastID;
                        }
                        $modelSups->setCalQuantity($id_space, $curentID, $supResource[$p], $supName[$p], $supMandatory[$p], $supIsInvoicingUnit[$p]);
                        $count++;
                    }
                }
                $count = 0;
            
                // get the last package id
                $lastID = 0;
                for ($p = 0 ; $p < count($supID) ; $p++) {
                    if ($supName[$p] != "" && $supID[$p] > $lastID ) {
                        $lastID = $supID[$p];
                    }
                }
                    
                for ($p = 0 ; $p < count($supID) ; $p++) {
                    if ($supName[$p] != "" ) {
                        $curentID = $supID[$p];
    
                        if ($curentID == "") {
                            $lastID++;
                            $curentID = $lastID;
                            $supID[$p] = $lastID;
                        }
                        if ($curentID == 1 && $p > 0) {
                            $lastID++;
                            $curentID = $lastID;
                            $supID[$p] = $lastID;
                        }
                        $modelSups->setCalQuantity($curentID, $supResource[$p], $supName[$p], $supMandatory[$p], $supIsInvoicingUnit[$p]);
                        $count++;
                    }
                }
                $modelSups->removeUnlistedQuantities($id_space, $supID);
                $_SESSION["message"] = ["content" => BookingTranslator::Quantities_saved($lang), "type" => "alert-success", "dismissible" => true];
            }
            $this->redirect("bookingquantities/".$id_space);
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }
}

