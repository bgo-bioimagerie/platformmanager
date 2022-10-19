<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReEvent.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Model/ReEventData.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ResourcesinfoController extends ResourcesBaseController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ResourcesTranslator::resources($lang), 3);
        $table->addLineEditButton("resourcesedit/" . $idSpace);
        $table->addDeleteButton("resourcesdelete/" . $idSpace);

        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "category" => ResourcesTranslator::Category($lang),
            "area" => ResourcesTranslator::Area($lang),
            "display_order" => ResourcesTranslator::Display_order($lang),
            "id" => "ID"
        );

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getBySpaceWithoutCategory($idSpace);
        $data = $resources;

        $modelArea = new ReArea();
        $modelCategory = new ReCategory();
        $areas = $modelArea->getForSpace($idSpace);
        $categories = $modelCategory->getBySpace($idSpace);
        $careas = [];
        $ccats = [];
        foreach ($categories as $c) {
            $ccats[$c['id']] = $c['name'];
        }
        foreach ($areas as $c) {
            $careas[$c['id']] = $c['name'];
        }
        for ($i = 0; $i < count($resources); $i++) {
            $resources[$i]["area"] = $careas[$resources[$i]["id_area"]];
            $resources[$i]["category"] = $ccats[$resources[$i]["id_category"]];
            if ($resources[$i]["category"] === "") {
                $_SESSION["flash"] = ResourcesTranslator::NoCategoryWarning($resources[$i]['name'], $lang);
            }
        }

        $tableHtml = $table->view($resources, $headers);

        return $this->render(array("data" => ["resources" => $data], "id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // get data
        $lang = $this->getLanguage();
        $modelCategory = new ReCategory($lang);
        $cats = $modelCategory->getBySpace($idSpace);
        $choicesC = array();
        $choicesidC = array();
        foreach ($cats as $cat) {
            $choicesC[] = $cat["name"];
            $choicesidC[] = $cat["id"];
        }

        $modelArea = new ReArea();
        $areas = $modelArea->getForSpace($idSpace);
        $choicesA = array();
        $choicesidA = array();
        foreach ($areas as $area) {
            $choicesA[] = $area["name"];
            $choicesidA[] = $area["id"];
        }

        if (empty($areas) || empty($cats)) {
            $_SESSION['flash'] = ResourcesTranslator::Area_category_Needed($lang);
            $_SESSION['flashClass'] = "warning";
        }

        $modelResource = new ResourceInfo();
        $data = $modelResource->getDefault();
        if ($id > 0) {
            $data = $modelResource->get($idSpace, $id);
        }
        // form

        $form = new Form($this->request, "resourcesedit");
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addText("brand", ResourcesTranslator::Brand($lang), false, $data["brand"]);
        $form->addText("type", ResourcesTranslator::Type($lang), false, $data["type"]);
        $form->addSelectMandatory("id_category", ResourcesTranslator::Category($lang), $choicesC, $choicesidC, $data["id_category"]);
        $form->addSelectMandatory("id_area", ResourcesTranslator::Area($lang), $choicesA, $choicesidA, $data["id_area"]);
        $form->addNumber("display_order", ResourcesTranslator::Display_order($lang), false, $data["display_order"]);
        $form->addUpload("image", CoreTranslator::Image($lang), $data["image"]);
        $form->addText("description", ResourcesTranslator::Description($lang), false, $data["description"], true);
        $form->addTextArea("long_description", ResourcesTranslator::DescriptionFull($lang), false, $data["long_description"], true);

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "resourcesedit/".$idSpace."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "resources/" . $idSpace);

        $form->setColumnsWidth(2, 10);

        if ($form->check()) {
            $rid = $form->getParameter("id");
            if ($rid == 0) {
                $plan = (new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']))->plan();
                if ($plan && array_key_exists('limits', $plan) && array_key_exists('resources', $plan['limits']) && $plan['limits']['resources']) {
                    // Count
                    $spaceResources = $modelResource->getBySpace($idSpace);
                    if (count($spaceResources) >= $plan['limits']['resources']) {
                        throw new PfmParamException("Resource plan limit reached: ".$plan['limits']['resources']);
                    }
                }
            }
            $id = $modelResource->set(
                $form->getParameter("id"),
                $form->getParameter("name"),
                $form->getParameter("brand"),
                $form->getParameter("type"),
                $form->getParameter("description"),
                $form->getParameter("long_description"),
                $form->getParameter("id_category"),
                $form->getParameter("id_area"),
                $idSpace,
                $form->getParameter("display_order")
            );

            // set default authorizations in bk_access
            $modelBkAccess = new BkAccess();
            if (!$modelBkAccess->get($idSpace, $id)) {
                $modelBkAccess->set($idSpace, $id, 3); // 3 for 'manager'
            }

            // upload image
            $target_dir = "data/resources/";
            if (array_key_exists("image", $_FILES) && $_FILES["image"]["name"] != "") {
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                $url = $id . "." . $ext;
                FileUpload::uploadFile($target_dir, "image", $url);

                $modelResource->setImage($idSpace, $id, $target_dir . $url);
            }

            $_SESSION["flash"] = ResourcesTranslator::Item_created("resource", $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                return $this->redirect("resources/".$idSpace, [], ['resource' => ['id' => $id]]);
            }
        }

        $headerInfo["curentTab"] = "info";
        $headerInfo["resourceId"] = $id;
        $this->render(array("id_space" => $idSpace, "lang" => $lang, "headerInfo" => $headerInfo, "formHtml" => $form->getHtml()));
    }

    public function eventsroAction($idSpace, $id)
    {
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->addLineEditButton("resourceeditevent/" . $idSpace . "/" . $id);

        $headers = array(
            "date" => CoreTranslator::Date($lang),
            "user" => CoreTranslator::User($lang),
            "eventtype" => ResourcesTranslator::Event_Type($lang),
            "state" => ResourcesTranslator::State($lang)
        );

        $modelResource = new ResourceInfo();
        $resourceInfo = $modelResource->get($idSpace, $id);

        $modelEvent = new ReEvent();
        $modelUser = new CoreUser();
        $modelState = new ReState();
        $modelEventType = new ReEventType();
        $events = $modelEvent->getByResource($idSpace, $id);

        for ($i = 0; $i < count($events); $i++) {
            $events[$i]["user"] = $modelUser->getUserFullName($events[$i]["id_user"]);
            $events[$i]["eventtype"] = $modelEventType->getName($idSpace, $events[$i]["id_eventtype"]);
            $events[$i]["state"] = $modelState->getName($idSpace, $events[$i]["id_state"]);
            $events[$i]["date"] = CoreTranslator::dateFromEn($events[$i]["date"], $lang);
        }

        $tableHtml = $table->view($events, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml, "resourceInfo" => $resourceInfo));
    }

    public function eventsAction($idSpace, $id)
    {
        $is_authorized = $this->checkAuthorizationMenuSpaceNoException("resources", $idSpace, $_SESSION["id_user"]);
        if (!$is_authorized) {
            $this->eventsroAction($idSpace, $id);
            return;
        }

        $lang = $this->getLanguage();

        $table = new TableView();
        $table->addLineEditButton("resourceeditevent/" . $idSpace . "/" . $id);
        $table->addDeleteButton("resourceseventdelete/" . $idSpace . "/" . $id, "id", "date");

        $headers = array(
            "date" => CoreTranslator::Date($lang),
            "user" => CoreTranslator::User($lang),
            "eventtype" => ResourcesTranslator::Event_Type($lang),
            "state" => ResourcesTranslator::State($lang)
        );

        $modelEvent = new ReEvent();
        $modelUser = new CoreUser();
        $modelState = new ReState();
        $modelEventType = new ReEventType();
        $events = $modelEvent->getByResource($idSpace, $id);

        for ($i = 0; $i < count($events); $i++) {
            $events[$i]["user"] = $modelUser->getUserFullName($events[$i]["id_user"]);
            $events[$i]["eventtype"] = $modelEventType->getName($idSpace, $events[$i]["id_eventtype"]);
            $events[$i]["state"] = $modelState->getName($idSpace, $events[$i]["id_state"]);
            $events[$i]["date"] = CoreTranslator::dateFromEn($events[$i]["date"], $lang);
        }

        $tableHtml = $table->view($events, $headers);


        $headerInfo["curentTab"] = "events";
        $headerInfo["resourceId"] = $id;
        $this->render(array("id_space" => $idSpace, "lang" => $lang, "headerInfo" => $headerInfo, "tableHtml" => $tableHtml, "id_resource" => $id));
    }

    public function deleteeventAction($idSpace, $id_resource, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);
        $modelEvent = new ReEvent();
        $modelEvent->delete($idSpace, $id);

        $this->redirect("resourcesevents/" . $idSpace . "/" . $id_resource);
    }

    public function editeventroAction($idSpace, $id_resource, $id_event)
    {
        $lang = $this->getLanguage();
        $formEvent = $this->createEventForm($idSpace, $id_resource, $id_event, $lang, false);
        $filesTable = $this->createFilesTable($idSpace, $id_event, $lang);

        $data = null;
        if ($id_event > 0) {
            $modelEvent = new ReEvent();
            $data = $modelEvent->get($idSpace, $id_event);
        }

        $modelResource = new ResourceInfo();
        $resourceInfo = $modelResource->get($idSpace, $id_resource);

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "formEvent" => $formEvent->getHtml($lang),
            "filesTable" => $filesTable,
            "resourceInfo" => $resourceInfo,
            "id_event" => $id_event,
            "data" => ["reevent" => $data]
        ));
    }

    public function editEventAction($idSpace, $id_resource, $id_event)
    {
        $authorized = $this->checkAuthorizationMenuSpaceNoException("resources", $idSpace, $_SESSION["id_user"]);
        if (!$authorized) {
            return $this->editeventroAction($idSpace, $id_resource, $id_event);
        }

        $modelEvent = new ReEvent();
        $data = null;
        if ($id_event == 0) {
            //$id_event = $modelEvent->addDefault($idSpace, $id_resource, $_SESSION["id_user"]);
            //return $this->redirect("resourceeditevent/" . $idSpace . "/" . $id_resource . "/" . $id_event);
        } else {
            $data = $modelEvent->get($idSpace, $id_event);
        }

        $lang = $this->getLanguage();

        $formEvent = $this->createEventForm($idSpace, $id_resource, $id_event, $lang);
        if ($formEvent->check()) {
            $modelEvent = new ReEvent();
            $new_id_event = $modelEvent->set($idSpace, $id_event, $id_resource, CoreTranslator::dateToEn($formEvent->getParameter("date"), $lang), $formEvent->getParameter("id_user"), $formEvent->getParameter("id_eventtype"), $formEvent->getParameter("id_state"), $formEvent->getParameter("comment"));
            $_SESSION['flash'] = 'Event updated';
            return $this->redirect("resourceeditevent/" . $idSpace . "/" . $id_resource . "/" . $new_id_event, [], ['reevent' => ['id' => $new_id_event]]);
        }
        $formDownload = $this->createDownloadForm($idSpace, $id_resource, $id_event, $lang);

        $formDownloadHtml = $formDownload->getHtml($lang);
        $filesTable = $this->createFilesTable($idSpace, $id_event, $lang);

        $headerInfo["curentTab"] = "events";
        $headerInfo["resourceId"] = $id_resource;
        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "formEvent" => $formEvent->getHtml($lang),
            "formDownload" => $formDownloadHtml,
            "headerInfo" => $headerInfo,
            "filesTable" => $filesTable,
            "id_event" => $id_event,
            "data" => ['reevent' => $data]
        ));
    }

    public function editeventfileAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $id_resource = $this->request->getParameter("id_resource");
        $id_event = $this->request->getParameter("id_event");

        $target_dir = "data/resources/events/";
        if ($_FILES["file_url"]["name"] != "") {
            $ext = pathinfo($_FILES["file_url"]["name"], PATHINFO_BASENAME);
            FileUpload::uploadFile($target_dir, "file_url", $id_event . "_" . $ext);

            $modelEventData = new ReEventData();
            $modelEventData->addFile($idSpace, $id_event, $target_dir . $id_event . "_" . $ext);
        }

        $this->redirect("resourceeditevent/" . $idSpace . "/" . $id_resource . "/" . $id_event);
    }

    public function downloadeventfileAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);
        $modelEventData = new ReEventData();
        $reData = $modelEventData->get($idSpace, $id);
        $file = $reData['url'];
        if (file_exists($file)) {
            $fileNameArray = explode("/", $file);
            $fileName = $fileNameArray[ count($fileNameArray) -1];

            header("Content-Type: binary/octet-stream");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Length: " . filesize("$file"));
            $fp = fopen("$file", "r");
            fpassthru($fp);
        } else {
            throw new PfmFileException('file does not exists', 404);
        }
    }

    protected function createFilesTable($idSpace, $id_event, $lang)
    {
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Files($lang), 3);
        $table->useSearch(false);
        $table->addDownloadButton("url");

        $headers = array("name" => CoreTranslator::Name($lang));
        $modelEventData = new ReEventData();
        $events = $modelEventData->getByEvent($idSpace, $id_event);
        for ($i = 0; $i < count($events); $i++) {
            $eventDataId = $events[$i]['id'];
            $events[$i]["name"] = str_replace("data/resources/events/" . $id_event . "_", "", $events[$i]["url"]);
            $events[$i]["url"] = "/resources/eventfile/$idSpace/$eventDataId";
        }

        return $table->view($events, $headers);
    }

    protected function createDownloadForm($idSpace, $id_resource, $id_event, $lang)
    {
        $form = new Form($this->request, "eventaddfileform");
        $form->addSeparator(ResourcesTranslator::Add_File($lang));
        $form->addHidden("id_resource", $id_resource);
        $form->addHidden("id_event", $id_event);
        $form->addUpload("file_url", "");
        $form->setValidationButton(CoreTranslator::Save($lang), "resourceediteventfile/" . $idSpace);
        $form->setColumnsWidth(0, 12);
        return $form;
    }

    protected function createEventForm($idSpace, $id_resource, $id_event, $lang, $editButton = true)
    {
        $modelResources = new ResourceInfo();

        $modelEvent = new ReEvent();
        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsers($idSpace);
        $choicesU = array();
        $choicesidU = array();
        foreach ($users as $user) {
            $choicesU[] = $user["name"] . " " . $user["firstname"];
            $choicesidU[] = $user["id"];
        }

        $modelET = new ReEventType();
        $ets = $modelET->getForSpace($idSpace);
        $choicesET = array();
        $choicesidET = array();
        foreach ($ets as $et) {
            $choicesET[] = $et["name"];
            $choicesidET[] = $et["id"];
        }

        $modelState = new ReState();
        $states = $modelState->getForSpace($idSpace);
        $choicesS = array();
        $choicesidS = array();
        foreach ($states as $state) {
            $choicesS[] = $state["name"];
            $choicesidS[] = $state["id"];
        }

        if ($id_event == 0) {
            $data = array(
                "date" => date("Y-m-d"),
                "id_user" => $_SESSION["id_user"],
                "id_eventtype" => 0,
                "id_state" => 0,
                "comment" => ""
            );
        } else {
            $data = $modelEvent->get($idSpace, $id_event);
        }

        $form = new Form($this->request, "editevent");
        $form->addSeparator(ResourcesTranslator::Edit_event_for($lang) . " " . $modelResources->getName($idSpace, $id_resource));
        $form->addDate("date", CoreTranslator::Date($lang), true, $data["date"]);
        $form->addHidden("id_resource", $id_resource);
        $form->addSelect("id_user", CoreTranslator::User($lang), $choicesU, $choicesidU, $data["id_user"]);
        $form->addSelect("id_eventtype", ResourcesTranslator::Event_Type($lang), $choicesET, $choicesidET, $data["id_eventtype"]);
        $form->addSelect("id_state", ResourcesTranslator::State($lang), $choicesS, $choicesidS, $data["id_state"]);
        $form->addTextArea("comment", ResourcesTranslator::Description($lang), false, $data["comment"], false);

        if ($editButton) {
            $form->setValidationButton(CoreTranslator::Save($lang), "resourceeditevent/" . $idSpace . "/" . $id_resource . "/" . $id_event);
            $form->setColumnsWidth(2, 10);
        }

        return $form;
    }

    public function respsAction($idSpace, $id_resource)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelResps = new ReResps();
        $respsData = $modelResps->getResps($idSpace, $id_resource);
        $data = $respsData;
        $resps = array();
        $rstatus = array();
        foreach ($respsData as $r) {
            $resps[] = $r["id_user"];
            $rstatus[] = $r["id_status"];
        }

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsers($idSpace);
        $choicesU = array();
        $choicesidU = array();
        foreach ($users as $user) {
            $choicesU[] = $user["name"] . " " . $user["firstname"];
            $choicesidU[] = $user["id"];
        }

        $modelRStatus = new ReRespsStatus();
        $statuss = $modelRStatus->getForSpace($idSpace);
        $choicesS = array();
        $choicesidS = array();
        foreach ($statuss as $status) {
            $choicesS[] = $status["name"];
            $choicesidS[] = $status["id"];
        }
        if (empty($choicesidS)) {
            $_SESSION['flash'] = ResourcesTranslator::StatusNeeded($lang);
        }

        $form = new Form($this->request, "respsform");
        $formAdd = new FormAdd($this->request, "respaddform");
        $formAdd->addSelect("id_user", CoreTranslator::User($lang), $choicesU, $choicesidU, $resps);
        $formAdd->addSelect("id_status", ResourcesTranslator::Status($lang), $choicesS, $choicesidS, $rstatus, isMandatory:true);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, "");
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesresp/" . $idSpace . "/" . $id_resource);



        if ($form->check()) {
            $idUsers = $this->request->getParameter("id_user");
            $id_statuss = $this->request->getParameter("id_status");

            $ids = [];
            for ($i = 0; $i < count($idUsers); $i++) {
                $ids[] = $modelResps->setResp($idSpace, $id_resource, $idUsers[$i], $id_statuss[$i]);
            }
            $modelResps->clean($idSpace, $id_resource, $idUsers);
            return $this->redirect("resourcesresp/" . $idSpace . "/" . $id_resource, [], ['reresps' => $ids]);
        }

        $headerInfo["curentTab"] = "resps";
        $headerInfo["resourceId"] = $id_resource;
        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "data" => ["reresps" => $data]
        ));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $modelResource = new ResourceInfo();
        $modelResource->delete($idSpace, $id);

        // get resource bk_access and delete it
        $modelBkAccess = new BkAccess();
        if ($modelBkAccess->get($idSpace, $id)) {
            $modelBkAccess->delete($idSpace, $id);
        }


        $this->redirect("resources/" . $idSpace);
    }
}
