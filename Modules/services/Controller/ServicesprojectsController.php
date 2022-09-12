<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Model/SeVisa.php';
require_once 'Modules/services/Model/SeTask.php';
require_once 'Modules/services/Model/SeTaskCategory.php';

require_once 'Modules/services/Model/StockShelf.php';

require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/services/Controller/ServicesController.php';
require_once 'Framework/FileUpload.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesprojectsController extends ServicesController {

    protected $tabsNames;

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $lang = $this->getLanguage();
        $this->tabsNames = [
            "sheet" => ServicesTranslator::Sheet($lang),
            "followup" => ServicesTranslator::FollowUp($lang),
            "closing" => ServicesTranslator::Closing($lang),
            "sample" => ServicesTranslator::StockSamples($lang),
            "kanban" => ServicesTranslator::KanbanBoard($lang),
            "gantt" => "Gantt",
        ];
    }

    public function userAction($id_space) {
        if(!isset($_SESSION['id_user']) || !$_SESSION['id_user']) {
            throw new PfmAuthException('need login', 403);
        }
        $m = new SeProject();
        $projects = $m->getUserProjects($id_space, $_SESSION['id_user']);
        return $this->render(['data' => ['projects' => $projects]]);
    }

    /**
     * checks if user is principal user or member of the project
     * If not, checks if user has all authorizations for services module
     * If not again, raises an exception
     */
    public function checkProjectAccessAuthorization($id_space, $id_project) {
        $projectModel = new SeProject();
        if (!($projectModel->isProjectUser($id_space, $_SESSION['id_user'], $id_project)
                || $_SESSION['id_user'] == $projectModel->getResp($id_space, $id_project))) {
            $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        }
    }

    protected function getProjectPeriod($id_space, $year) {
        $modelCoreConfig = new CoreConfig();
        $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $id_space);

        $projectperiodbeginArray = $projectperiodbegin ? explode("-", $projectperiodbegin) : [0,1,1];
        $previousYear = $year - 1;
        $yearBegin = $previousYear . "-" . $projectperiodbeginArray[1] . "-" . $projectperiodbeginArray[2];
        $projectperiodendArray = $projectperiodend ? explode("-", $projectperiodend) : [0,12,31];
        $yearEnd = $year . "-" . $projectperiodendArray[1] . "-" . $projectperiodendArray[2];

        return array("yearBegin" => $yearBegin, "yearEnd" => $yearEnd);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $year = "", $status = "") {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get sort action
        $sortentry = "id";

        // get the commands list
        $modelEntry = new SeProject();
        $entriesArray = array();
        if ($status == "") {
            if (isset($_SESSION["project_lastvisited"])) {
                $status = $_SESSION["project_lastvisited"];
            } else {
                $status = "all";
            }
        }

        $title = ServicesTranslator::Services_Projects($lang);
        $years = array();
        $yearsUrl = "";

        if ($status == "all") {
            $title = ServicesTranslator::All_projects($lang);
            $modelCoreConfig = new CoreConfig();
            $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $id_space);
            $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $id_space);

            $years = $modelEntry->allProjectsYears($id_space, $projectperiodbegin, $projectperiodend);
            if ($year == "") {
                $year = $years[count($years) - 1];
            }
            $dates = $this->getProjectPeriod($id_space, $year);
            $yearsUrl = "servicesprojectsall";
            $entriesArray = $modelEntry->entries($id_space, $dates['yearBegin'], $dates['yearEnd'], $sortentry);
        } else if ($status == "opened") {
            $title = ServicesTranslator::Opened_projects($lang);
            $entriesArray = $modelEntry->openedEntries($id_space, $sortentry);
        } else if ($status == "closed") {
            $modelCoreConfig = new CoreConfig();
            $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $id_space);
            $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $id_space);

            $years = $modelEntry->closedProjectsPeriods($id_space, $projectperiodend);
            $yearsUrl = "servicesprojectsclosed";
            
            if ($year == "") {
                if (empty($years)) {
                    $year = date('Y');
                } else {
                    $year = $years[count($years) - 1];
                }
            }

            $dates = $this->getProjectPeriod($id_space, $year);
            $title = ServicesTranslator::Closed_projects($lang);
            $entriesArray = $modelEntry->closedEntries($id_space, $dates['yearBegin'], $dates['yearEnd'], $sortentry);
        } else if ($status == "period") {
            $modelConfig = new CoreConfig();
            $projectperiodbegin = $modelConfig->getParamSpace("projectperiodbegin", $id_space);
            $projectperiodend = $modelConfig->getParamSpace("projectperiodend", $id_space);
            $projectperiodbeginArray = $projectperiodbegin ? explode("-", $projectperiodbegin) : [0, 1, 1];
            $projectperiodendArray = $projectperiodend ? explode("-", $projectperiodend) : [0, 12, 31];
            if ($projectperiodbeginArray[1] <= date("m", time())) {
                $year = date("Y", time());
            } else {
                $year = date("Y", time()) - 1;
            }
            $yearp = $year + 1;

            $month = $projectperiodbeginArray[1];
            if ($month < 10) {
                $month = "0" . $month;
            }
            
            $monthp = $projectperiodendArray[1];
            if ($monthp < 10) {
                $monthp = "0" . $monthp;
            }

            $day = $projectperiodbeginArray[2];
            if ($day < 10) {
                $day = "0" . $day;
            }

            $dayp = $projectperiodendArray[2];
            if ($dayp < 10) {
                $dayp = "0" . $dayp;
            }

            $periodStart = $year . "-" . $month . "-" . $day;
            $periodEnd = $yearp . "-" . $monthp . "-" . $dayp;

            $entriesArray = $modelEntry->allPeriodProjects($id_space, $periodStart, $periodEnd);
        }

        $table = new TableView();
        $table->setTitle($title, 3);
        $table->setColorIndexes(array("all" => "color", "time_limit" => "time_color", "date_close" => "closed_color", "all_text" => "txtcolor"));

        $table->addLineEditButton("servicesprojectsheet/" . $id_space);
        $table->addDeleteButton("servicesprojectdelete/" . $id_space, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "resp_name" => ClientsTranslator::ClientAccount($lang),
            "name" => ServicesTranslator::No_identification($lang),
            "user_name" => CoreTranslator::User($lang),
            "date_open" => ServicesTranslator::Opened_date($lang),
            "time_limit" => ServicesTranslator::Time_limite($lang),
            "date_close" => ServicesTranslator::Closed_date($lang),
            "close_icon" => array("title" => "", "type" => "glyphicon", "color" => "red"),
        );

        $modelPricing = new ClPricing();
        $modelClient = new ClClient();

        $modelConfig = new CoreConfig();
        $warning = intval($modelConfig->getParamSpace("SeProjectDelayWarning", $id_space));

        for ($i = 0; $i < count($entriesArray); $i++) {
            $entriesArray[$i]["close_icon"] = "";
            if (
                (
                    $entriesArray[$i]["date_close"] == null || $entriesArray[$i]["date_close"] == "" || $entriesArray[$i]["date_close"] == "0000-00-00"
                ) && (
                    !($entriesArray[$i]["time_limit"] == null || $entriesArray[$i]["time_limit"] == "" || $entriesArray[$i]["time_limit"] == "0000-00-00")
                )
            ) {

                $limiteArray = explode('-', $entriesArray[$i]["time_limit"]);
                $limitD = mktime(0, 0, 0, $limiteArray[1], $limiteArray[2], $limiteArray[0]);

                $today = time();
                $delay = $limitD - $today;
                if ($delay < 0 || $delay < $warning * 24 * 3600) {
                    $entriesArray[$i]["close_icon"] = "bi-exclamation-triangle-fill";
                }
            }

            $entriesArray[$i]["date_open"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_open"], $lang);
            $entriesArray[$i]["date_close"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_close"], $lang);
            $entriesArray[$i]["time_limit"] = CoreTranslator::dateFromEn($entriesArray[$i]["time_limit"], $lang);

            // get the pricing color
            $clientAccounts = $modelClient->get($id_space ,$entriesArray[$i]["id_resp"]);

            $entriesArray[$i]["resp_name"] = $clientAccounts["name"];
            $pricingInfo = $modelPricing->get($id_space ,$clientAccounts["pricing"]);
            
            $entriesArray[$i]["color"] = $pricingInfo["color"];
            $entriesArray[$i]["txtcolor"] = $pricingInfo["txtcolor"];

            $entriesArray[$i]["time_color"] = Constants::COLOR_WHITE;
            if ($entriesArray[$i]["time_limit"] != "" && ($entriesArray[$i]["time_limit"] && strval($entriesArray[$i]["time_limit"]) != "0000-00-00")) {
                $entriesArray[$i]["time_color"] = "#FFCC00";
            }

            $entriesArray[$i]["closed_color"] = Constants::COLOR_WHITE;
            if ($entriesArray[$i]["date_close"] && $entriesArray[$i]["date_close"] != "0000-00-00") {
                $entriesArray[$i]["closed_color"] = "#99CC00";
            }
        }
        $tableHtml = $table->view($entriesArray, $headersArray);

        // 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            'yearsUrl' => $yearsUrl,
            'years' => $years,
            'year' => $year
                ), "indexAction");
    }

    public function periodAction($id_space, $year = "") {
        $_SESSION["project_lastvisited"] = "period";
        $this->indexAction($id_space, $year, "period");
    }

    public function openedAction($id_space, $year = "") {
        $_SESSION["project_lastvisited"] = "opened";
        $this->indexAction($id_space, $year, "opened");
    }

    public function closedAction($id_space, $year = "") {
        $_SESSION["project_lastvisited"] = "closed";
        $this->indexAction($id_space, $year, "closed");
    }

    public function AllAction($id_space, $year) {

        $_SESSION["project_lastvisited"] = "all";
        $this->indexAction($id_space, $year, "all");
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $serviceModel = new SeProject();
        $serviceModel->delete($id_space ,$id);
        $this->redirect("services/" . $id_space);
    }

    public function closingAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelVisa = new SeVisa();
        $visas = $modelVisa->getForList($id_space);

        $modelProject = new SeProject();
        $project = $modelProject->getEntry($id_space ,$id);



        $form = new Form($this->request, "projectclosingform");
        $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, $project["date_close"]);
        $form->addSelect("closed_by", ServicesTranslator::Closed_by($lang), $visas["names"], $visas["ids"], $project["closed_by"]);
        $form->addTextArea("samplereturn", ServicesTranslator::SampleReturn($lang), false, $project["samplereturn"]);
        $form->addDate("samplereturndate", ServicesTranslator::DateSampleReturn($lang), false, $project["samplereturndate"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectclosing/" . $id_space . "/" . $id);


        if ($form->check()) {
            
            $modelProject->closeProject(
                    $id_space,
                    $id,
                    CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang),
                    $this->request->getParameter("closed_by")
            );
            
            $modelProject->sampleReturn(
                    $id_space,
                    $id,
                    $this->request->getParameter("samplereturn"),
                    CoreTranslator::dateToEn($this->request->getParameter("samplereturndate"), $lang)
            );
        
            $_SESSION['flash'] = ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';
            return $this->redirect("servicesprojectclosing/" . $id_space . "/" . $id, [], ['project' => $project]);
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "closing";
        $headerInfo["personInCharge"] = $project["in_charge"];

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project["name"],
            "data" => ['project' => $project]
        ));
    }

    public function samplestockAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelShelf = new StockShelf();
        $cabinets = $modelShelf->getAllForProjectSelect($id_space);
        
        $modelProject = new SeProject();
        $project = $modelProject->getEntry($id_space, $id);

        $form = new Form($this->request, "projectreturnform");
        
        //$form->addSelect("samplestocked", ServicesTranslator::SampleStocked($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $project["samplestocked"]);
        $form->addSelect("id_cabinet", ServicesTranslator::Cabinet($lang), $cabinets["names"], $cabinets["ids"], $project["id_sample_cabinet"]);
        $form->addTextArea("samplescomment", ServicesTranslator::Comment($lang), false, $project["samplescomment"]);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectsample/" . $id_space . "/" . $id);


        if ($form->check()) {
            $modelProject->setSampleStock(
                    $id_space,
                    $id, 
                    1,
                    $this->request->getParameter("id_cabinet"),
                    $this->request->getParameter("samplescomment")
            );

            $_SESSION['flash'] = ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("servicesprojectsample/" . $id_space . "/" . $id);
            return;
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "samplereturn";
        $headerInfo["personInCharge"] = $project["in_charge"];

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project["name"])
        );
    }

    protected function generateProjectForm($id_space, $id, $lang) {

        $modelProject = new SeProject();
        $form = new Form($this->request, "projectEditForm");

        // ADD USERS SELECTION
        $projectUsers = $modelProject->getProjectUsersIds($id_space, $id);
        $projectUserIds = [];
        foreach($projectUsers as $pUser) {
            array_push($projectUserIds, $pUser['id_user']);
        }        

        $value = null;
        if ($id > 0) {
            $value = $modelProject->getEntry($id_space , $id);
            array_push($projectUserIds, $value['id_user']);
        } else {
            $form->setTitle(ServicesTranslator::Add_projects($lang), 3);
            $value = $modelProject->defaultEntryValues();
        }

        $projectUserIds = array_unique($projectUserIds);

        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelVisa = new SeVisa();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space ,"name");
        // remove users first entry if == ""
        if (!empty($users) && $users["ids"][0] == "") {
            array_shift($users["ids"]);
            array_shift($users["names"]);
        }
        $clients = $modelClient->getForList($id_space);

        if($value['id_resp'] && !in_array($value['id_resp'], $clients["ids"])){
            $modelCl = new ClClient();
            $clName = $modelCl->getName($id_space, $value['id_resp']);
            if(!$clName) {
                $clName = Constants::UNKNOWN;
            }
            array_push($clients["names"], '[!] '.$clName);
            array_push($clients["ids"], $value['id_resp']);
        }

        $inChargeList = $modelVisa->getForList($id_space);

        $form->addText("name", ServicesTranslator::No_identification($lang), true, $value["name"]);
        // id_client is denominated id_resp in se_project table
        $form->addSelectMandatory("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"], $value["id_resp"]);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
        $form->addSelectMandatory("in_charge", ServicesTranslator::InCharge($lang), $inChargeList["names"], $inChargeList["ids"], $value["in_charge"]);

        $newIDs = array("", 1, 2, 3);
        $newNames = array("", CoreTranslator::no($lang), ServicesTranslator::Academique($lang), ServicesTranslator::Industry($lang));

        $form->addSelectMandatory("new_team", ServicesTranslator::New_team($lang), $newNames, $newIDs, $value["new_team"]);
        $form->addSelectMandatory("new_project", ServicesTranslator::New_project($lang), $newNames, $newIDs, $value["new_project"]);

        $modelOrigin = new SeOrigin();
        $origins = $modelOrigin->getForList($id_space);
        $form->addSelectMandatory("id_origin", ServicesTranslator::servicesOrigin($lang), $origins['names'], $origins['ids'], $value["id_origin"]);

        $form->addDate("time_limit", ServicesTranslator::Time_limite($lang), true, $value["time_limit"]);
        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, $value["date_open"]);

        if ($id > 0) {
            $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, $value["date_close"]);
        } else {
            $form->addHidden("date_close", $value["date_close"]);
        }

        $formAddProjectUsers = new FormAdd($this->request, "project_users");
        $formAddProjectUsers->addSelect("users", CoreTranslator::Users($lang), $users["names"], $users["ids"], $projectUserIds);
        $formAddProjectUsers->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAddProjectUsers, CoreTranslator::Users($lang));

        return $form;

    }

    public function sheetAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelProject = new SeProject();
        $project = $modelProject->getEntry($id_space, $id);

        $form = $this->generateProjectForm($id_space, $id, $lang);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectsheet/" . $id_space . "/" . $id);

        if ($form->check()) {
            $id = $this->updateProject($id, $id_space, $lang);
            
            if (!isset($_SESSION['flash'])) {
                $_SESSION['flash'] = "";
            }
            $_SESSION['flash'] = $_SESSION['flash'] . " " . ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';

            $this->redirect("servicesprojectsheet/" . $id_space . "/" . $id);
            return;
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "sheet";
        $headerInfo["personInCharge"] = $project["in_charge"];

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project['name']
            )
        );
    }

    public function followupAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelProject = new SeProject();
        $project = $modelProject->getEntry($id_space, $id);

        $table = new TableView();
        $table->addLineEditButton("editentry", "id", true);
        $table->addDeleteButton("servicesprojectdeleteentry/" . $id_space . "/" . $id, "id", "id");

        $headersArray = array(
            "date" => CoreTranslator::Date($lang),
            "description" => ServicesTranslator::Description($lang),
            "comment" => ServicesTranslator::Comment($lang),
            "invoice" => ServicesTranslator::Invoice($lang)
        );

        $modelServices = new SeService();
        $modelInvoice = new InInvoice();
        $items = $modelProject->getProjectServicesDefault($id_space, $id);
        for ($i = 0; $i < count($items); $i++) {
            $name = $modelServices->getItemName($id_space, $items[$i]["id_service"]);
            if($name == null){
                $name = '[!] '.($modelServices->getItemName($id_space, $items[$i]["id_service"], true) ?? Constants::UNKNOWN);
            }
            $items[$i]["description"] = 'q='.$items[$i]["quantity"] . " " . $name;
            $items[$i]["date"] = CoreTranslator::dateFromEn($items[$i]["date"], $lang);
            $items[$i]["invoice"] = $modelInvoice->getInvoiceNumber($id_space, $items[$i]["id_invoice"]);
        }
        $tableHtml = $table->view($items, $headersArray);

        $formEdit = $this->createEditEntryForm($id_space, $lang);

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "followup";
        $headerInfo["personInCharge"] = $project["in_charge"];

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "projectName" => $project['name'],
            "tableHtml" => $tableHtml,
            "headerInfo" => $headerInfo,
            "formedit" => $formEdit,
            "projectEntries" => $items,
            "id_project" => $id,
            "data" => ["entries" => $items]
        ));
    }

    public function kanbanAction($id_space, $id_project) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $isManager = $this->role >= CORESPACE::$MANAGER;
        $id_task = $this->request->params()["task"] ?? 0;
        $taskModel= new SeTask();
        $tasks = $taskModel->getByProject($id_project, $id_space);

        $projectModel = new SeProject();
        $project = $projectModel->getEntry($id_space, $id_project);
        $projectServices = $projectModel->getProjectServicesDefault($id_space, $id_project);
        

        $serviceModel = new SeService();
        $services = array();
        foreach($projectServices as $projectService) {
            array_push($services, $serviceModel->getItem($id_space, $projectService['id_service']));
        }
        for($i=0; $i<count($tasks); $i++) {
            $tasks[$i]['services'] = $taskModel->getTaskServicesIds($id_space, $tasks[$i]['id']);
            // cast private boolean attribute to string
            $tasks[$i]['private'] = $tasks[$i]['private'] ? "true" : "false";
        }
        
        
        $categoryModel = new SeTaskCategory();
        $categories = $categoryModel->getByProject($id_project, $id_space);

        for($i=0; $i<count($categories); $i++) {
            $categories[$i]['tasks'] = [];
        }

        $projectName = $projectModel->getName($id_space ,$id_project);
        $seProjectUsers = $projectModel->getProjectUsersIds($id_space, $id_project);
        $projectMainUser = $project['id_user'];


        $modelUser = new CoreUser();
        $projectUsers = array();
        array_push($projectUsers, ['id' => 0, 'name' => '---', 'firstname' => '---']);

        $ids = [];
        foreach($seProjectUsers as $seProjectUser) {
            $ids[] = $seProjectUser['id_user'];
            array_push($projectUsers, $modelUser->getUser($seProjectUser['id_user']));
        }

        $csu = new CoreSpaceUser();
        $managers = $csu->managersOrAdmin($id_space);
        foreach ($managers as $manager) {
            if(in_array($manager['id_user'], $ids)) {
                continue;
            }
            array_push($projectUsers, $modelUser->getUser($manager['id_user']));
        }

        $textContent = [
            "newTask" => ServicesTranslator::NewTask($lang),
            "newCategory" => ServicesTranslator::NewCategory($lang),
            "renameCategory" => ServicesTranslator::RenameCategory($lang),
            "deleteTask" => ServicesTranslator::DeleteTask($lang),
            "deleteCategory" => ServicesTranslator::DeleteCategory($lang),
            "assignee" => ServicesTranslator::Assignee($lang),
            "noUserAssigned" => ServicesTranslator::NoUserAssigned($lang),
            "noServiceAssigned" => ServicesTranslator::NoServiceAssigned($lang),
            "details" => ServicesTranslator::Details($lang),
            "clearSelection" => ServicesTranslator::ClearSelection($lang),
            "startDate" => ServicesTranslator::StartDate($lang),
            "endDate" => ServicesTranslator::EndDate($lang),
            "visibility" => ServicesTranslator::Visibility($lang),
            "private" => ServicesTranslator::Private($lang),
            "addFile" => ServicesTranslator::AddFile($lang),
            "replaceFile" => ServicesTranslator::ReplaceFile($lang),
            "download" => ServicesTranslator::downloadAttachedFile($lang),
            "close" => CoreTranslator::Close($lang),
            "save" => CoreTranslator::Save($lang),
            "edit" => CoreTranslator::Edit($lang),
            "name" => CoreTranslator::Name($lang),
            "currentFile" => CoreTranslator::CurrentFile($lang),
            "downloadError" => CoreTranslator::DownloadError($lang),
            "uploadError" => CoreTranslator::UploadError($lang),
        ];

        $headerInfo["projectId"] = $id_project;
        $headerInfo["curentTab"] = "kanban";
        $headerInfo["personInCharge"] = $project["in_charge"];

        return $this->render(array(
            "id_space" => $id_space,
            "sessionUserId" => $_SESSION["id_user"],
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "textContent" => json_encode($textContent),
            "projectString" => ServicesTranslator::Project($lang),
            "projectName" => $projectName,
            "headerInfo" => $headerInfo,
            "id_project" => $id_project,
            "tasks" => json_encode($tasks),
            "id_task" => $id_task,
            "categories" => json_encode($categories),
            "projectServices" => json_encode($services),
            "projectUsers" => json_encode($projectUsers),
            "mainUser" => $projectMainUser,
            "personInCharge" => $project['in_charge'],
            "userIsManager" => json_encode($isManager),
        ));
    }

    public function setTaskAction($id_space, $id_project) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $taskData = $this->request->params()['task'];
        $taskModel = new SeTask();

        // delete removed services
        if ($taskData['id'] > 0) {
            $dbTaskServices = $taskModel->getTaskServices($id_space, $taskData['id']);
            foreach ($dbTaskServices as $dbTaskService) {
                if (!in_array($dbTaskService['id_service'], $taskData['services'])) {
                    $taskModel->deleteTaskService($id_space, $taskData['id'], $dbTaskService['id_service']);
                }
            }
        }

        // add/update task
        $id = $taskModel->set(
            $taskData['id'],
            $id_space,
            $id_project,
            $taskData['state'],
            $taskData['name'],
            $taskData['content'],
            $taskData['start_date'],
            $taskData['end_date'],
            $taskData['services'],
            $taskData['id_user'],
            $taskData['id_owner'],
            // cast bool to int
            $taskData['done'] ? 1 : 0,
            $taskData['private'] ? 1 : 0
        );
        $this->render(['data' => ['id' => $id]]);
    }

    // task files related methods => to be used in next release
    
    /* public function uploadTaskFileAction($id_space, $id_task) {
        $taskModel = new SeTask();
        $target_dir = "data/services/projecttasks/" . $id_space . "/";
        if (isset($_FILES) && isset($_FILES['file']) && $_FILES["file"]["name"] != "") {
            $fileName = pathinfo($_FILES["file"]["name"], PATHINFO_BASENAME);
            $url = $target_dir . $id_task . "_" . $fileName;

            // If target directory doesn't exist, creates it
            if(!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $uploaded = FileUpload::uploadFile($target_dir, "file", $id_task . "_" . $fileName);
            if ($uploaded) {
                $taskModel->setFile($id_space, $id_task, $url, $fileName);
            }
        }
    }

    public function getTaskFileAction($id_space, $id_task) {
        $taskModel = new SeTask();
        $file = $taskModel->getFile($id_space, $id_task);
        $this->render(['data' => $file]);
    }

    public function openFileAction($id_space, $id_task) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $taskModel = new SeTask();
        $task = $taskModel->getById($id_space, $id_task);

        // If private task, check if user is the owner of the task or if user is at least manager
        if ($task['private'] == 1
            && (!$this->role >= CoreSpace::$MANAGER && $task['id_owner'] != $_SESSION["id_user"])) {
                throw new PfmAuthException('private document');
        }

        $file = $taskModel->getFile($id_space, $id_task)['file'];
        if (file_exists($file)) {
            $mime = mime_content_type($file);
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        } else {
            throw new PfmFileException("File not found", 404);
        }
    } */

    public function getTasksAction($id_space, $id_project) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);        
        $taskModel = new SeTask();
        $tasks = $taskModel->getByProject($id_project, $id_space);

        $userModel = new CoreUser();
        for($i=0; $i<count($tasks); $i++) {
            $tasks[$i]['userName'] = $userModel->getUserFUllName($tasks[$i]['id_user']);
        }
        $this->render(['data' => ['elements' => $tasks]]);
    }

    public function getTaskServicesAction($id_space, $id_task) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $serviceModel = new SeService();
        $taskModel = new SeTask();
        $serviceIds = $taskModel->getTaskServicesIds($id_space, $id_task);

        $services = [];
        foreach($serviceIds as $serviceId) {
            array_push($services, $serviceModel->getItem($id_space, $serviceId));
        }
        $this->render(['data' => ['elements' => $services]]);
    }

    public function deleteTaskAction($id_space, $id_task) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $taskModel = new SeTask();
        return $taskModel->delete($id_space, $id_task);
    }

    public function setTaskCategoryAction($id_space, $id_project) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $categoryData = $this->request->params()['category'];
        $categoryModel = new SeTaskCategory();
        $id = $categoryModel->set($categoryData['id'], $id_space, $id_project, $categoryData['name'], $categoryData['position'], $categoryData['color']);
        $this->render(['data' => ['id' => $id]]);
    }

    public function deleteTaskCategoryAction($id_space, $id_category) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $categoryModel = new SeTaskCategory();
        return $categoryModel->delete($id_space, $id_category);
    }

    protected function createEditEntryForm($id_space, $lang) {
        $form = new Form($this->request, "editNoteForm", true);

        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $form->addHidden("formprojectentryid", 0);
        $form->addHidden("formprojectentryprojectid", 0);
        $form->addDate("formprojectentrydate", CoreTranslator::Date($lang), true, "");
        $form->addSelectMandatory("formserviceid", ServicesTranslator::service($lang), $services["names"], $services["ids"]);
        $form->addFloat("formservicequantity", ServicesTranslator::Quantity($lang), true, 0);
        $form->addTextArea("formservicecomment", ServicesTranslator::Comment($lang), false, "");

        $form->setColumnsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojecteditentryquery/" . $id_space);
        return $form->getHtml($lang);
    }

    public function editentryqueryAction($id_space) {

        $lang = $this->getLanguage();

        $id_entry = $this->request->getParameter("formprojectentryid");
        $id_project = $this->request->getParameter("formprojectentryprojectid");
        $date = CoreTranslator::dateToEn($this->request->getParameter("formprojectentrydate"), $lang);
        $id_service = $this->request->getParameter("formserviceid");
        $quantity = $this->request->getParameter("formservicequantity");
        $comment = $this->request->getParameter("formservicecomment");

        $modelProject = new SeProject();
        $id = $modelProject->setEntry($id_space ,$id_entry, $id_project, $id_service, $date, $quantity, $comment, 0);

        return $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project, [], ['entry' => ['id' => $id]]);
    }

    public function deleteentryAction($id_space, $id_project, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $modelProject = new SeProject();
        $modelProject->deleteEntry($id_space, $id);

        $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project);
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = $this->generateProjectForm($id_space, $id, $lang);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectedit/" . $id_space . "/" . $id);

        if ($form->check()) {
            $id_project = $this->updateProject($id, $id_space, $lang);
            return $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project, [], ['project' => ['id' => $id_project]]);
        }
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    protected function updateProject($id, $id_space, $lang) {
        $modelProject = new SeProject();
        $id_user = $this->request->getParameter("id_user") == "" ? "0" : $this->request->getParameter("id_user");
        $pic = $this->request->getParameter("in_charge");
        $id_project =
            $modelProject->setProject(
                $id,
                $id_space,
                $this->request->getParameter("name"),
                $this->request->getParameter("id_client"),
                $id_user,
                CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang),
                CoreTranslator::dateToEn($this->request->getParameterNoException("date_close"), $lang),
                $this->request->getParameter("new_team"),
                $this->request->getParameter("new_project"),
                CoreTranslator::dateToEn($this->request->getParameter("time_limit"), $lang)
            );
        $modelProject->setOrigin($id_space ,$id_project, $this->request->getParameter("id_origin"));
        $modelProject->setInCharge($id_space, $id_project, $pic);

        // add project users
        if ($this->request->getParameter("users") && !empty($this->request->getParameter("users"))) {
            $formProjectUserIds = $this->request->getParameter("users");
        }
        if (!in_array($id_user, $formProjectUserIds)) {
            array_push($formProjectUserIds, $id_user);
            // if main project user not in users list, display warning
            $_SESSION['flash'] = ServicesTranslator::MainUserNotInList($lang);
        }
        
        if($id>0) {
            // remove deleted users
            $dbProjectUserIds = [];
            $dbProjectUsers = $modelProject->getProjectUsersIds($id_space, $id);
            foreach ($dbProjectUsers as $dbProjectUser) {
                array_push($dbProjectUserIds, $dbProjectUser["id_user"]);
            }
            $toDeleteList = array_diff($dbProjectUserIds, $formProjectUserIds);
            foreach($toDeleteList as $toDelete) {
                $modelProject->deleteProjectUser($id_space, $toDelete, $id);
            }
        }
            
        foreach($formProjectUserIds as $user_id) {
            $modelProject->setProjectUser($id_space, $user_id, $id_project);
        }
        return $id_project;
    }

    public function exportAction($id_space, $id) {
        // get project entries
        $modelProject = new SeProject();
        $projectEntries = $modelProject->getProjectServicesBase($id_space ,$id);

        // calculate total sum and price HT
        $modelClient = new ClClient();
        $id_resp = $modelProject->getResp($id_space, $id);
        $LABpricingid = $modelClient->getPricingID($id_space, $id_resp);

        $itemPricing = new SePrice();


        $content = "Date ; Commentaire ; Prestation ; Quantité ; Prix ;  Total \r\n";
        $totalHT = 0;
        $modelItem = new SeService();
        //print_r($projectEntries);
        foreach ($projectEntries as $entry) {

            $content .= $entry["date"] . ";";
            $content .= str_replace(";", ",", $entry["comment"]) . ";";
            $content .= ($modelItem->getItemName($id_space, $entry["id_service"]) ?? Constants::UNKNOWN) . ";";
            if ($modelItem->getItemType($id_space, $entry["id_service"]) == 4) {

                $content .= 1 . ";";
                $unitPrice = $entry["quantity"];
                $entry["quantity"] = 1;
            } else {
                $content .= $entry["quantity"] . ";";
                $unitPrice = $itemPricing->getPrice($id_space, $entry["id_service"], $LABpricingid);
                //echo "price for service " . $entry["id_service"] . " and lab " . $LABpricingid . " = " .$unitPrice. " <br/>";
            }
            $content .= $unitPrice . ";";
            $price = (float) $entry["quantity"] * (float) $unitPrice;
            $totalHT += $price;
            $content .= $price . "\r\n";
        }

        for ($i = 0; $i <= 4; $i++) {
            $content .= " ; ";
        }
        $content .= $totalHT . "\r\n";

        header("Content-Type: application/csv-tab-delimited-table;charset=UTF-8");
        header("Content-disposition: filename=projet.csv");
        echo $content;
    }

}
