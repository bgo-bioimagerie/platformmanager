<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReEvent.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Model/ReEventData.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';


require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

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
        $table->addLineEditButton("resourcesedit");
        $table->addDeleteButton("resourcesdelete");
        
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "category" => ResourcesTranslator::Category($lang),
            "area" => ResourcesTranslator::Area($lang),
            "display_order" => ResourcesTranslator::Display_order($lang)
        );
        
        $modelResource = new ResourceInfo();
        $modelSite = new EcSite();
        if ($this->isUserStatus(CoreStatus::$SUPERADMIN)){
            $resources = $modelResource->getAll();
            $headers["site"] = EcosystemTranslator::Site($lang);
        }   
        else{
            
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
        $form->addText("description", ResourcesTranslator::Description($lang), false, $data["description"], true);
        $form->addTextArea("long_description", ResourcesTranslator::Description($lang), false, $data["long_description"], true);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "resources");
        $form->setButtonsWidth(3, 9);
        $form->setColumnsWidth(2, 10);
        
        if ($form->check()){
            $modelResource->set($form->getParameter("id"), 
                    $form->getParameter("name"),
                    $form->getParameter("brand"),
                    $form->getParameter("type"),
                    $form->getParameter("description"), 
                    $form->getParameter("long_description"), 
                    $form->getParameter("id_category"),
                    $form->getParameter("id_area"),
                    $form->getParameter("id_site"),
                    $form->getParameter("display_order"));
            $this->redirect("resources");
            return;
        }
        
        $headerInfo["curentTab"] = "info";
        $headerInfo["resourceId"] = $id;
        $this->render(array("lang" => $lang, "headerInfo" => $headerInfo, "formHtml" => $form->getHtml()));
        
    }
    
    public function eventsAction($id) {
        
        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->addLineEditButton("resourceeditevent/" . $id);
        $table->addDeleteButton("resourceseventdelete", "id", "date");
        
        $headers = array(
            "date" => CoreTranslator::Date($lang),
            "user" => CoreTranslator::User($lang),
            "eventtype" => ResourcesTranslator::Event_Type($lang),
            "state" => ResourcesTranslator::State($lang)
            );
        
        $modelEvent = new ReEvent();
        $modelUser = new EcUser();
        $modelState = new ReState();
        $modelEventType = new ReEventType();
        $events = $modelEvent->getByResource($id);
        
        for($i = 0 ; $i < count($events) ; $i++){
            $events[$i]["user"] = $modelUser->getUserFUllName($events[$i]["id_user"]);
            $events[$i]["eventtype"] = $modelEventType->getName($events[$i]["id_eventtype"]);
            $events[$i]["state"] = $modelState->getName($events[$i]["id_state"]);
        }
        
        $tableHtml = $table->view($events, $headers);
        
        
        $headerInfo["curentTab"] = "events";
        $headerInfo["resourceId"] = $id;
        $this->render(array("lang" => $lang, "headerInfo" => $headerInfo, "tableHtml" => $tableHtml, "id_resource" => $id));
       
    }
    
    public function editEventAction($id_resource, $id_event){
        
        if($id_event == 0){
            $modelEvent = new ReEvent();
            $id_event = $modelEvent->addDefault($id_resource, $_SESSION["id_user"]);
            $this->redirect("resourceeditevent/" . $id_resource . "/" .$id_event);
            return;
        }
        
        $lang = $this->getLanguage();
        
        $formEvent = $this->createEventForm($id_resource, $id_event, $lang);
        if($formEvent->check()){
            
            $modelEvent = new ReEvent();
            $modelEvent->set($id_event, $id_resource, 
                    CoreTranslator::dateToEn($formEvent->getParameter("date"), $lang), 
                    $formEvent->getParameter("id_user"),  
                    $formEvent->getParameter("id_eventtype"),  
                    $formEvent->getParameter("id_state"),  
                    $formEvent->getParameter("comment"));
            
            $this->redirect("resourceeditevent/" . $id_resource . "/" .$id_event);
            return;
        }
        $formDownload = $this->createDownloadForm($id_resource, $id_event, $lang);

        $formDownloadHtml = $formDownload->getHtml($lang);
        $filesTable = $this->createFilesTable($id_event, $lang);
        
        $headerInfo["curentTab"] = "events";
        $headerInfo["resourceId"] = $id_resource;
        $this->render(array("lang" => $lang, "formEvent" => $formEvent->getHtml($lang), 
            "formDownload" => $formDownloadHtml, "headerInfo" => $headerInfo,
            "filesTable" => $filesTable, "id_event" => $id_event));
    }
    
    public function editeventfileAction(){

        $id_resource = $this->request->getParameter("id_resource");
        $id_event = $this->request->getParameter("id_event");

        $target_dir = "data/resources/events/";
        if ($_FILES["file_url"]["name"] != "") {
            $ext = pathinfo($_FILES["file_url"]["name"], PATHINFO_BASENAME);
            FileUpload::uploadFile($target_dir, "file_url", $id_event . "_" . $ext);
                
            $modelEventData = new ReEventData();
            $modelEventData->addFile($id_event, $target_dir . $id_event . "_" . $ext);        
        }
            
        $this->redirect("resourceeditevent/" . $id_resource . "/" .$id_event);
        
    }
    
    protected function createFilesTable($id_event, $lang){
        
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Files($lang), 3);
        $table->useSearch(false);
        $table->addDownloadButton("url");
        
        $headers = array( "name" => CoreTranslator::Name($lang) );
        $modelEventData = new ReEventData();
        $events = $modelEventData->getByEvent($id_event);
        for($i = 0 ; $i < count($events) ; $i++){
            $events[$i]["name"] = str_replace("data/resources/events/".$id_event."_", "", $events[$i]["url"]);
        }
        
        return $table->view($events, $headers);
    }
    
    protected function createDownloadForm($id_resource, $id_event, $lang){
        
        $form = new Form($this->request, "eventaddfileform");
        $form->addSeparator(ResourcesTranslator::Add_File($lang));
        $form->addHidden("id_resource", $id_resource);
        $form->addHidden("id_event", $id_event);
        $form->addUpload("file_url", "");
        $form->setValidationButton(CoreTranslator::Save($lang), "resourceediteventfile");
        $form->setColumnsWidth(0, 12);
        return $form;
    }
            
    protected function createEventForm($id_resource, $id_event, $lang){
        
        $modelResources = new ResourceInfo();
        
        $modelEvent = new ReEvent();
        $modelUser = New EcUser();
        $users = $modelUser->getActiveUsersInfo(1);
        $choicesU = array(); $choicesidU = array();
        foreach($users as $user){
            $choicesU[] = $user["name"] . " " . $user["firstname"];
            $choicesidU[] = $user["id"];
        }
        
        $modelET = New ReEventType();
        $ets = $modelET->getAll();
        $choicesET = array(); $choicesidET = array();
        foreach($ets as $et){
            $choicesET[] = $et["name"];
            $choicesidET[] = $et["id"];
        }
                
        $modelState = New ReState();
        $states = $modelState->getAll();
        $choicesS = array(); $choicesidS = array();
        foreach($states as $state){
            $choicesS[] = $state["name"];
            $choicesidS[] = $state["id"];
        }
        
        if ($id_event == 0){
            $data = array( 
                "date" => CoreTranslator::dateFromEn(date("Y-m-d"), $lang),
                "id_user" => $_SESSION["id_user"],
                "id_eventtype" => 0,
                "id_state" => 0,
                "comment" => ""
            );
        }
        else{
            $data = $modelEvent->get($id_event);
        }
        
        $form = new Form($this->request, "editevent");
        $form->addSeparator(ResourcesTranslator::Edit_event_for($lang) . " " . $modelResources->getName($id_resource));
        $form->addDate("date", CoreTranslator::Date($lang), true, CoreTranslator::dateFromEn($data["date"], $lang));
        $form->addHidden("id_resource", $id_resource);
        $form->addSelect("id_user", CoreTranslator::User($lang), $choicesU, $choicesidU, $data["id_user"]);
        $form->addSelect("id_eventtype", ResourcesTranslator::Event_Type($lang), $choicesET, $choicesidET, $data["id_eventtype"]);
        $form->addSelect("id_state", ResourcesTranslator::State($lang), $choicesS, $choicesidS, $data["id_state"]);
        $form->addTextArea("comment", ResourcesTranslator::Description($lang), false, $data["comment"], false);
        $form->setValidationButton(CoreTranslator::Save($lang), "resourceeditevent/".$id_resource."/".$id_event);
        $form->setColumnsWidth(2, 10);
        
        return $form;
    } 
    
    public function respsAction($id_resource){
        
        $modelResps = new ReResps();
        $respsData = $modelResps->getResps($id_resource);
        $resps = array(); $rstatus = array();
        foreach($respsData as $r){
            $resps[] = $r["id_user"];
            $rstatus[] = $r["id_status"];
        }
        
        $modelUser = new EcUser();
        $users = $modelUser->getActiveUsersInfo(1);
        $choicesU = array(); $choicesidU = array(); 
        foreach($users as $user){
            $choicesU[] = $user["name"] . " " . $user["firstname"];
            $choicesidU[] = $user["id"];
        }
        
        $modelRStatus = new ReRespsStatus();
        $statuss = $modelRStatus->getAll();
        foreach($statuss as $status){
            $choicesS[] = $status["name"];
            $choicesidS[] = $status["id"];
        }
        
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "respsform");
        $formAdd = new FormAdd($this->request, "respaddform");
        $formAdd->addSelect("id_user", CoreTranslator::User($lang), $choicesU, $choicesidU, $resps);
        $formAdd->addSelect("id_status", ResourcesTranslator::Status($lang), $choicesS, $choicesidS, $rstatus);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, "");
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesresp/".$id_resource);

        $form->setButtonsWidth(2, 9);
        
        if($form->check()){
            
            $id_users = $this->request->getParameter("id_user");
            $id_statuss = $this->request->getParameter("id_status");
            
            for($i = 0 ; $i < count($id_users) ; $i++){
                $modelResps->setResp($id_resource, $id_users[$i], $id_statuss[$i]);
            }
            $modelResps->clean($id_resource, $id_users);
            $this->redirect("resourcesresp/".$id_resource);
            return;
            
        }
        
        $headerInfo["curentTab"] = "resps";
        $headerInfo["resourceId"] = $id_resource;
        $this->render(array("lang" => $lang, "formHtml" => $form->getHtml($lang), "headerInfo" => $headerInfo));
    }
    
    public function deleteAction($id){
        $modelResource = new ResourceInfo();
        $modelResource->delete($id);
    }
}
