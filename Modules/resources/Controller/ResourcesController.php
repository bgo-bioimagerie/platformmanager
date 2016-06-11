<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';

require_once 'Modules/ecosystem/Model/EcSite.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ResourcesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("resources");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::resources($lang));
        
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "category" => ResourcesTranslator::Category($lang),
            "area" => ResourcesTranslator::Area($lang),
            "display_order" => ResourcesTranslator::Display_order($lang)
        );
        
        
        $modelResource = new ResourceInfo();
        if ($this->isUserStatus(CoreStatus::$SUPERADMIN)){
            $resources = $modelResource->getAll();
            $headers["site"] = EcosystemTranslator::Site($lang);
        }   
        else{
            $modelSite = new EcSite();
            $sites = $modelSite->getUserAdminSites($_SESSION["id_user"]);
            $resources = $modelResource->getBySites($sites);
        }
        
        $modelArea = new ReArea();
        $modelCategory = new ReCategory();
        for($i = 0 ; $i < count($resources) ; $i++){
            $resources[$i]["area"] = $modelArea->getName($resources[$i]["id_area"]);
            $resources[$i]["category"] = $modelCategory->getName($resources[$i]["id_category"]);
            if ($this->isUserStatus(CoreStatus::$SUPERADMIN)){
                $resources[$i]["site"] = $modelSite->getName($resources[$i]["id_site"]);
            }
        }
        
        $tableHtml = $table->view($resources, $headers);
        
        $this->render(array("lang" => $lang, "tableHtml" => $tableHtml));
    }
    
    public function editAction($id) {

        // get data
        $lang = $this->getLanguage();
        $modelCategory = new ReCategory($lang);
        $cats = $modelCategory->getAll("name");
        $choicesC = array(); $choicesidC = array();
        foreach($cats as $cat){
            $choicesC[] = $cat["name"]; 
            $choicesidC[] = $cat["id"];
        }
        
        $modelArea = new ReArea();
        $areas = $modelArea->getAll();
        $choicesA = array(); $choicesidA = array();
        foreach($areas as $area){
            $choicesA[] = $area["name"]; 
            $choicesidA[] = $area["id"];
        }
        
        $modelSite = new EcSite();
        $sites = $modelSite->getAll();
        $choicesS = array(); $choicesidS = array();
        foreach($sites as $site){
            $choicesS[] = $site["name"]; 
            $choicesidS[] = $site["id"];
        }
        
        $modelResource = new ResourceInfo();
        $data = $modelResource->getDefault();
        if ($id > 0){
            $data = $modelResource->get($id);
        }
        // form
        
        $form = new Form($this->request, "resourcesedit");
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addText("brand", ResourcesTranslator::Brand($lang), false, $data["brand"]);
        $form->addText("type", ResourcesTranslator::Type($lang), false, $data["type"]);
        $form->addSelect("id_category", ResourcesTranslator::Category($lang), $choicesC, $choicesidC, $data["id_category"]);
        $form->addSelect("id_area", ResourcesTranslator::Area($lang), $choicesA, $choicesidA, $data["id_area"]);
        $form->addSelect("id_site", EcosystemTranslator::Site($lang), $choicesS, $choicesidS, $data["id_site"]);
        $form->addNumber("display_order", ResourcesTranslator::Display_order($lang), false, $data["display_order"]);
        $form->addTextArea("desciption", ResourcesTranslator::Description($lang), false, $data["desciption"], true);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "resources");
        $form->setButtonsWidth(3, 9);
        $form->setColumnsWidth(2, 10);
        
        if ($form->check()){
            // todo
            return;
        }
        
        $headerInfo["curentTab"] = "info";
        $headerInfo["resourceId"] = $id;
        $this->render(array("lang" => $lang, "headerInfo" => $headerInfo, "formHtml" => $form->getHtml()));
        
    }
    
    public function eventsAction($id) {
        
        $lang = $this->getLanguage();
        $headerInfo["curentTab"] = "events";
        $headerInfo["resourceId"] = $id;
        $this->render(array("lang" => $lang, "headerInfo" => $headerInfo));
       
    }
}
