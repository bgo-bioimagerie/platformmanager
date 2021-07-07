<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

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
        }
        
        $form = new Form($this->request, "supsForm");
        $form->setTitle(BookingTranslator::Quantities($lang));
        
        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang) , $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);
        $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang) , array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $supsMandatories);
        
        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingquantities/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $supID = $this->request->getParameterNoException("id_sups");
            $supResource = $this->request->getParameterNoException("id_resources");
            $supName = $this->request->getParameterNoException("names");
            $supMandatory = $this->request->getParameterNoException("mandatory");
            
            // $count = 0;


            $packs = [];
            for ($p = 0; $p < count($supID); $p++) {
                if ($supName[$p] != "" && $supID[$p]) {
                   $packs[$supName[$p]] = $supID[$p];
                }
            }
            for ($p = 0; $p < count($supID); $p++) {
                if (!$supID[$p]) {
                    // If package id not set, use from known packages
                    if(isset($packs[$supName[$p]])) {
                        $supID[$p] = $packs[$supName[$p]];
                    } else {
                        // Or create a new package
                       $cvm = new CoreVirtual();
                       $vid = $cvm->new('quantities');
                       $supID[$p] = $vid;
                       $packs[$supName[$p]] = $vid;
                   }
                }
                $modelSups->setCalQuantity($id_space,  $supID[$p], $supResource[$p], $supName[$p], $supMandatory[$p]);
            }

            /* bug to get last id (could conflict)
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
                    if(! in_array($supResource[$p], $choicesRid)) {
                        continue;
                    }
                    //echo "set package (".$curentID." , " . $id_resource ." , " . $packageName[$p]." , ". $packageDuration[$p] . ")<br/>";
                    $modelSups->setCalQuantity($id_space, $curentID, $supResource[$p], $supName[$p], $supMandatory[$p]);
                    $count++;
                }
            }
            */
            
            //echo "sups ids = ". print_r($supID) . "<br/>";
            //echo "sup Resource ids = ". print_r($supResource) . "<br/>";
            
            $sups = $modelSups->getForSpace($id_space, "id_resource");
            // If package in db is not listed in provided package list, delete them
            foreach ($sups as $s) {
                if($s['id_quantity'] && !in_array($s['id_quantity'], $supID)) {
                    $modelSups->delete($id_space, $s['id']);
                }
            } 

            // $modelSups->removeUnlistedQuantities($supID);
            $_SESSION["message"] = BookingTranslator::Quantities_saved($lang);
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
