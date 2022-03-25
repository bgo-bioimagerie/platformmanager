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
abstract class BookingsupsabstractController extends BookingsettingsController {
    
    protected $modelSups;
    protected string $supsType;
    protected string $supsTypePlural;
    protected bool $invoicable;
    protected bool $mandatoryFields;
    protected string $formUrl;

    protected function getSupForm($id_space, $formTitle) {
        $lang = $this->getLanguage();
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);
        $choicesR = array(); $choicesRid = array();
        foreach($resources as $res){
            $choicesR[] = $res["name"];
            $choicesRid[] = $res["id"];
        }

        $sups = $this->modelSups->getForSpace($id_space, "id_resource");
        $supsIds = array();
        $supsIdsRes = array();
        $supsNames = array();
        $supsMandatories = array();
        $supIsInvoicingUnit = array();
        foreach($sups as $sup){
            $supsIds[] = $sup["id_" . $this->supsType];
            $supsIdsRes[] = $sup["id_resource"];
            $supsNames[] = $sup["name"];
            $supsMandatories[] = $sup["mandatory"];
            if ($this->invoicable) {
                $supIsInvoicingUnit[] = $sup["is_invoicing_unit"] ? intval($sup["is_invoicing_unit"]) : 0;
            }
        }

        $form = new Form($this->request, "supsForm");
        $form->setTitle($formTitle);

        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang), $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);

        if ($this->mandatoryFields) {
            $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang), array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0,1), $supsMandatories);
        }
        
        if ($this->invoicable) {
            $formAdd->addSelect("is_invoicing_unit", BookingTranslator::Is_invoicing_unit($lang) , array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0,1), $supIsInvoicingUnit);
        }
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), $this->formUrl . "/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function supsFormCheck($id_space) {
        $lang = $this->getLanguage();
        $modelResource = new ResourceInfo();
        $supID = $this->request->getParameterNoException("id_sups");
        $supResources = $this->request->getParameterNoException("id_resources");
        $supName = $this->request->getParameterNoException("names");
        $supMandatory = $this->request->getParameterNoException("mandatory");
        $supIsInvoicingUnit = $this->request->getParameterNoException("is_invoicing_unit");

        // format into arrays
        $supIsInvoicingUnit = is_array($supIsInvoicingUnit) ? $supIsInvoicingUnit : [$supIsInvoicingUnit];
        $supID = is_array($supID) ? $supID : [$supID];
        $supResources = is_array($supResources) ? $supResources : [$supResources];

        if ($this->invoicable) {
            // find out if multiple sups are used as invoicing units
            $invoicingUnitsResources = [];
            foreach ($supResources as $index => $resource) {
                if ($supIsInvoicingUnit[$index] == 1) {
                    if (in_array($resource, $invoicingUnitsResources)) {
                        $_SESSION["flash"] = BookingTranslator::maxInvoicingUnits($lang);
                        $_SESSION["flashClass"] = "danger";
                        $this->redirect("booking" . $this->formUrl ."/".$id_space);
                        return;
                    } else {
                        array_push($invoicingUnitsResources, $resource);
                    }
                }
            }
        }
        

        $supacks = [];
        for ($sup = 0; $sup < count($supID); $sup++) {
            if ($supName[$sup] != "" && $supID[$sup]) {
                $supacks[$supName[$sup]] = $supID[$sup];
            }
        }

        $coupleSupResourceExists = false;
        for ($sup = 0; $sup < count($supID); $sup++) {
            if($supName[$sup] == "") {
                continue;
            }
            if (!$supID[$sup]) {
                // If sup id not set, use from known sups
                if(isset($supacks[$supName[$sup]])) {
                    $supID[$sup] = $supacks[$supName[$sup]];
                    if ($this->coupleSupResourceExists($supID[$sup],$supResources[$sup], $id_space)) {
                        $coupleSupResourceExists = [
                            "resource" => $modelResource->get($id_space, $supResources[$sup])['name'],
                            "sup" => $supName[$sup]
                        ];
                    }
                } else {
                    // Or create a new package
                    $cvm = new CoreVirtual();
                    $vid = $cvm->new($this->supsType); // TODO: check if $this->supsType ok in any case
                    $supID[$sup] = $vid;
                    $supacks[$supName[$sup]] = $vid;
                }
            }
            $this->modelSups->setSupplementary($id_space,  $supID[$sup], $supResources[$sup], $supName[$sup], $supMandatory[$sup], $supIsInvoicingUnit[$sup]);
        }
        
        
        //  get all ids from id_sup
        $id_qtes = [];
        for ($i=0; $i<count($supID); $i++) {
            array_push($id_qtes, $this->modelSups->getBySupID($id_space, $supID[$i], $supResources[$i])['id']);
        }
        
        // If package in db is not listed in provided package list, delete them
        $this->modelSups->removeUnlisted($id_space, $id_qtes, false);

        if ($coupleSupResourceExists) {
            $_SESSION["flash"] = BookingTranslator::Sup_resource_exists(
                $coupleSupResourceExists["sup"],
                $coupleSupResourceExists["resource"],
                $lang
            );
            $_SESSION["flashClass"] = 'danger';
        } else {
            $_SESSION["flash"] = BookingTranslator::Sups_saved($this->supsTypePlural, $lang);
            $_SESSION["flashClass"] = "success";
        }

    }

    protected function coupleSupResourceExists($id_sup, $id_resource, $id_space) {
        $dbSups = $this->modelSups->calSupByResource($id_space, $id_resource);
        foreach ($dbSups as $dbSup) {
            if ($dbSup['id_' . $this->supsType] == $id_sup) {
                return true;
            }
        }
        return false;
    }

    
}
