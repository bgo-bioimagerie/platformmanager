<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingquantitiesController extends BookingsettingsController {
    
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
        foreach($sups as $sup){
            $supsIds[] = $sup["id_quantity"];
            $supsIdsRes[] = $sup["id_resource"];
            $supsNames[] = $sup["name"];
            $supsMandatories[] = $sup["mandatory"];
            $supIsInvoicingUnit[] = $sup["is_invoicing_unit"] ? intval($sup["is_invoicing_unit"]) : 0;
        }
        
        $form = new Form($this->request, "supsForm");
        $form->setTitle(BookingTranslator::Quantities($lang));
        
        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang) , $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);
        $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang) , array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0,1), $supsMandatories);
        $formAdd->addSelect("is_invoicing_unit", BookingTranslator::Is_invoicing_unit($lang) , array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0,1), $supIsInvoicingUnit);
        
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingquantities/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $supID = $this->request->getParameterNoException("id_sups");
            $supResources = $this->request->getParameterNoException("id_resources");
            $supName = $this->request->getParameterNoException("names");
            $supMandatory = $this->request->getParameterNoException("mandatory");
            $supIsInvoicingUnit = $this->request->getParameterNoException("is_invoicing_unit");

            // format into arrays
            $supIsInvoicingUnit = is_array($supIsInvoicingUnit) ? $supIsInvoicingUnit : [$supIsInvoicingUnit];
            $supID = is_array($supID) ? $supID : [$supID];
            $supResources = is_array($supResources) ? $supResources : [$supResources];

            // find out if multiple quantities are used as invoicing units
            $invoicingUnitsResources = [];
            foreach ($supResources as $index => $resource) {
                if ($supIsInvoicingUnit[$index] == 1) {
                    if (in_array($resource, $invoicingUnitsResources)) {
                        $_SESSION["flash"] = BookingTranslator::maxInvoicingUnits($lang);
                        $_SESSION["flashClass"] = "danger";
                        $this->redirect("bookingquantities/".$id_space);
                        return;
                    } else {
                        array_push($invoicingUnitsResources, $resource);
                    }
                }
            }

            $supacks = [];
            for ($sup = 0; $sup < count($supID); $sup++) {
                if ($supName[$sup] != "" && $supID[$sup]) {
                   $supacks[$supName[$sup]] = $supID[$sup];
                }
            }
            for ($sup = 0; $sup < count($supID); $sup++) {
                if (!$supID[$sup]) {
                    // If package id not set, use from known packages
                    if(isset($supacks[$supName[$sup]])) {
                        $supID[$sup] = $supacks[$supName[$sup]];
                    } else {
                        // Or create a new package
                       $cvm = new CoreVirtual();
                       $vid = $cvm->new('quantities');
                       $supID[$sup] = $vid;
                       $supacks[$supName[$sup]] = $vid;
                   }
                }
                $modelSups->setCalQuantity($id_space,  $supID[$sup], $supResources[$sup], $supName[$sup], $supMandatory[$sup], $supIsInvoicingUnit[$sup]);
            }
            
            $sups = $modelSups->getForSpace($id_space, "id_resource");
            // If package in db is not listed in provided package list, delete them
            foreach ($sups as $sup) {
                if($sup['id_quantity'] && !in_array($sup['id_quantity'], $supID)) {
                    $modelSups->delete($id_space, $sup['id']);
                }
            } 

            $modelSups->removeUnlistedQuantities($id_space, $supID);
            $_SESSION["flash"] = BookingTranslator::Quantities_saved($lang);
            $_SESSION["flashClass"] = "success";
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
