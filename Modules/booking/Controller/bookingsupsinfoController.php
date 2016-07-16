<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalSupinfo.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class bookingsupsinfoController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("bookingsettings");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getAll("name");
        $choicesR = array(); $choicesRid = array();
        foreach($resources as $res){
            $choicesR[] = $res["name"];
            $choicesRid[] = $res["id"];
        }
        
        $modelSups = new BkCalSupinfo();
        $sups = $modelSups->getAll("id_resource");
        $supsIds = array(); $supsIdsRes = array();
        $supsNames = array(); $supsMandatories = array();
        foreach($sups as $p){
            $supsIds[] = $p["id_supinfo"];
            $supsIdsRes[] = $p["id_resource"];
            $supsNames[] = $p["name"];
            $supsMandatories[] = $p["mandatory"];
        }
        
        $form = new Form($this->request, "supsForm");
        $form->setTitle(BookingTranslator::Supplementaries($lang));
        
        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang) , $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);
        $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang) , array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $supsMandatories);
        
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingsupsinfo");
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $supID = $this->request->getParameterNoException("id_sups");
            $supResource = $this->request->getParameterNoException("id_resources");
            $supName = $this->request->getParameterNoException("names");
            $supMandatory = $this->request->getParameterNoException("mandatory");
            
            $count = 0;
            
            // get the last package id
            $lastID = 0;
            for( $p = 0 ; $p < count($supID) ; $p++){
                if ($supName[$p] != "" ){
                    if ($supID[$p] > $lastID){
                        $lastID = $supID[$p];
                    }
                }
            }
                
            for( $p = 0 ; $p < count($supID) ; $p++){
                if ($supName[$p] != "" ){
                    $curentID = $supID[$p];

                    if ($curentID == ""){
                        $lastID++;
                        $curentID = $lastID;
                        $supID[$p] = $lastID;
                    }
                    if ($curentID == 1 && $p > 0){
                        $lastID++;
                        $curentID = $lastID;
                        $supID[$p] = $lastID;
                    }
                    //echo "set package (".$curentID." , " . $id_resource ." , " . $packageName[$p]." , ". $packageDuration[$p] . ")<br/>";
                    $modelSups->setCalSupInfo($curentID, $supResource[$p], $supName[$p], $supMandatory[$p]);
                    $count++;
                }
            }
            
            //echo "sups ids = ". print_r($supID) . "<br/>";
            //echo "sup Resource ids = ". print_r($supResource) . "<br/>";
            
            $modelSups->removeUnlistedSupInfos($supID);
            $_SESSION["message"] = BookingTranslator::Supplementaries_saved($lang);
            $this->redirect("bookingsupsinfo");
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }
}
