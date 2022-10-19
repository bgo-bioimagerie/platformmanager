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
class ServicesprojectsController extends ServicesController
{
    protected $tabsNames;

    public function __construct(Request $request, ?array $space=null)
    {
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

    public function userAction($idSpace)
    {
        if (!isset($_SESSION['id_user']) || !$_SESSION['id_user']) {
            throw new PfmAuthException('need login', 403);
        }
        $m = new SeProject();
        $projects = $m->getUserProjects($idSpace, $_SESSION['id_user']);
        return $this->render(['data' => ['projects' => $projects]]);
    }

    /**
     * checks if user is principal user or member of the project
     * If not, checks if user has all authorizations for services module
     * If not again, raises an exception
     */
    public function checkProjectAccessAuthorization($idSpace, $id_project)
    {
        $projectModel = new SeProject();
        if (!($projectModel->isProjectUser($idSpace, $_SESSION['id_user'], $id_project)
                || $_SESSION['id_user'] == $projectModel->getResp($idSpace, $id_project))) {
            $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        }
    }

    protected function getProjectPeriod($idSpace, $year)
    {
        $modelCoreConfig = new CoreConfig();
        $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $idSpace);
        $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $idSpace);

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
    public function indexAction($idSpace, $year = "", $status = "")
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
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
            $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $idSpace);
            $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $idSpace);

            $years = $modelEntry->allProjectsYears($idSpace, $projectperiodbegin, $projectperiodend);
            if ($year == "") {
                $year = $years[count($years) - 1];
            }
            $dates = $this->getProjectPeriod($idSpace, $year);
            $yearsUrl = "servicesprojectsall";
            $entriesArray = $modelEntry->entries($idSpace, $dates['yearBegin'], $dates['yearEnd'], $sortentry);
        } elseif ($status == "opened") {
            $title = ServicesTranslator::Opened_projects($lang);
            $entriesArray = $modelEntry->openedEntries($idSpace, $sortentry);
        } elseif ($status == "closed") {
            $modelCoreConfig = new CoreConfig();
            $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $idSpace);
            $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $idSpace);

            $years = $modelEntry->closedProjectsPeriods($idSpace, $projectperiodend);
            $yearsUrl = "servicesprojectsclosed";

            if ($year == "") {
                if (empty($years)) {
                    $year = date('Y');
                } else {
                    $year = $years[count($years) - 1];
                }
            }

            $dates = $this->getProjectPeriod($idSpace, $year);
            $title = ServicesTranslator::Closed_projects($lang);
            $entriesArray = $modelEntry->closedEntries($idSpace, $dates['yearBegin'], $dates['yearEnd'], $sortentry);
        } elseif ($status == "period") {
            $modelConfig = new CoreConfig();
            $projectperiodbegin = $modelConfig->getParamSpace("projectperiodbegin", $idSpace);
            $projectperiodend = $modelConfig->getParamSpace("projectperiodend", $idSpace);
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

            $entriesArray = $modelEntry->allPeriodProjects($idSpace, $periodStart, $periodEnd);
        }

        $table = new TableView();
        $table->setTitle($title, 3);
        $table->setColorIndexes(array("all" => "color", "time_limit" => "time_color", "date_close" => "closed_color", "all_text" => "txtcolor"));

        $table->addLineEditButton("servicesprojectsheet/" . $idSpace);
        $table->addDeleteButton("servicesprojectdelete/" . $idSpace, "id", "id");

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
        $warning = intval($modelConfig->getParamSpace("SeProjectDelayWarning", $idSpace));

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
            $clientAccounts = $modelClient->get($idSpace, $entriesArray[$i]["id_resp"]);

            $entriesArray[$i]["resp_name"] = $clientAccounts["name"];
            $pricingInfo = $modelPricing->get($idSpace, $clientAccounts["pricing"]);

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
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml,
            'yearsUrl' => $yearsUrl,
            'years' => $years,
            'year' => $year
                ), "indexAction");
    }

    public function periodAction($idSpace, $year = "")
    {
        $_SESSION["project_lastvisited"] = "period";
        $this->indexAction($idSpace, $year, "period");
    }

    public function openedAction($idSpace, $year = "")
    {
        $_SESSION["project_lastvisited"] = "opened";
        $this->indexAction($idSpace, $year, "opened");
    }

    public function closedAction($idSpace, $year = "")
    {
        $_SESSION["project_lastvisited"] = "closed";
        $this->indexAction($idSpace, $year, "closed");
    }

    public function AllAction($idSpace, $year)
    {
        $_SESSION["project_lastvisited"] = "all";
        $this->indexAction($idSpace, $year, "all");
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $serviceModel = new SeProject();
        $serviceModel->delete($idSpace, $id);
        $this->redirect("services/" . $idSpace);
    }

    public function closingAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelVisa = new SeVisa();
        $visas = $modelVisa->getForList($idSpace);

        $modelProject = new SeProject();
        $project = $modelProject->getEntry($idSpace, $id);



        $form = new Form($this->request, "projectclosingform");
        $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, $project["date_close"]);
        $form->addSelect("closed_by", ServicesTranslator::Closed_by($lang), $visas["names"], $visas["ids"], $project["closed_by"]);
        $form->addTextArea("samplereturn", ServicesTranslator::SampleReturn($lang), false, $project["samplereturn"]);
        $form->addDate("samplereturndate", ServicesTranslator::DateSampleReturn($lang), false, $project["samplereturndate"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectclosing/" . $idSpace . "/" . $id);


        if ($form->check()) {
            $modelProject->closeProject(
                $idSpace,
                $id,
                CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang),
                $this->request->getParameter("closed_by")
            );

            $modelProject->sampleReturn(
                $idSpace,
                $id,
                $this->request->getParameter("samplereturn"),
                CoreTranslator::dateToEn($this->request->getParameter("samplereturndate"), $lang)
            );

            $_SESSION['flash'] = ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';
            return $this->redirect("servicesprojectclosing/" . $idSpace . "/" . $id, [], ['project' => $project]);
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "closing";
        $headerInfo["personInCharge"] = $project["in_charge"];

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project["name"],
            "data" => ['project' => $project]
        ));
    }

    public function samplestockAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelShelf = new StockShelf();
        $cabinets = $modelShelf->getAllForProjectSelect($idSpace);

        $modelProject = new SeProject();
        $project = $modelProject->getEntry($idSpace, $id);

        $form = new Form($this->request, "projectreturnform");

        //$form->addSelect("samplestocked", ServicesTranslator::SampleStocked($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $project["samplestocked"]);
        $form->addSelect("id_cabinet", ServicesTranslator::Cabinet($lang), $cabinets["names"], $cabinets["ids"], $project["id_sample_cabinet"]);
        $form->addTextArea("samplescomment", ServicesTranslator::Comment($lang), false, $project["samplescomment"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectsample/" . $idSpace . "/" . $id);


        if ($form->check()) {
            $modelProject->setSampleStock(
                $idSpace,
                $id,
                1,
                $this->request->getParameter("id_cabinet"),
                $this->request->getParameter("samplescomment")
            );

            $_SESSION['flash'] = ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("servicesprojectsample/" . $idSpace . "/" . $id);
            return;
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "samplereturn";
        $headerInfo["personInCharge"] = $project["in_charge"];

        $this->render(
            array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project["name"])
        );
    }

    protected function generateProjectForm($idSpace, $id, $lang)
    {
        $modelProject = new SeProject();
        $form = new Form($this->request, "projectEditForm");

        // ADD USERS SELECTION
        $projectUsers = $modelProject->getProjectUsersIds($idSpace, $id);
        $projectUserIds = [];
        foreach ($projectUsers as $pUser) {
            array_push($projectUserIds, $pUser['id_user']);
        }

        $value = null;
        if ($id > 0) {
            $value = $modelProject->getEntry($idSpace, $id);
            array_push($projectUserIds, $value['id_user']);
        } else {
            $form->setTitle(ServicesTranslator::Add_projects($lang), 3);
            $value = $modelProject->defaultEntryValues();
        }

        $projectUserIds = array_unique($projectUserIds);

        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelVisa = new SeVisa();
        $users = $modelUser->getSpaceActiveUsersForSelect($idSpace, "name");
        $clients = $modelClient->getForList($idSpace);

        if ($value['id_resp'] && !in_array($value['id_resp'], $clients["ids"])) {
            $modelCl = new ClClient();
            $clName = $modelCl->getName($idSpace, $value['id_resp']);
            if (!$clName) {
                $clName = Constants::UNKNOWN;
            }
            array_push($clients["names"], '[!] '.$clName);
            array_push($clients["ids"], $value['id_resp']);
        }

        $inChargeList = $modelVisa->getForList($idSpace);

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
        $origins = $modelOrigin->getForList($idSpace);
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

    public function sheetAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelProject = new SeProject();
        $project = $modelProject->getEntry($idSpace, $id);

        $form = $this->generateProjectForm($idSpace, $id, $lang);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectsheet/" . $idSpace . "/" . $id);

        if ($form->check()) {
            $id = $this->updateProject($id, $idSpace, $lang);

            if (!isset($_SESSION['flash'])) {
                $_SESSION['flash'] = "";
            }
            $_SESSION['flash'] = $_SESSION['flash'] . " " . ServicesTranslator::projectEdited($lang);
            $_SESSION["flashClass"] = 'success';

            $this->redirect("servicesprojectsheet/" . $idSpace . "/" . $id);
            return;
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "sheet";
        $headerInfo["personInCharge"] = $project["in_charge"];

        $this->render(
            array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tabsNames" => $this->tabsNames,
            "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo,
            "projectName" => $project['name']
            )
        );
    }

    public function followupAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelProject = new SeProject();
        $project = $modelProject->getEntry($idSpace, $id);

        $table = new TableView();
        $table->addLineEditButton("editentry", "id", true);
        $table->addDeleteButton("servicesprojectdeleteentry/" . $idSpace . "/" . $id, "id", "id");

        $headersArray = array(
            "date" => CoreTranslator::Date($lang),
            "description" => ServicesTranslator::Description($lang),
            "comment" => ServicesTranslator::Comment($lang),
            "invoice" => ServicesTranslator::Invoice($lang)
        );

        $modelServices = new SeService();
        $modelInvoice = new InInvoice();
        $items = $modelProject->getProjectServicesDefault($idSpace, $id);
        for ($i = 0; $i < count($items); $i++) {
            $name = $modelServices->getItemName($idSpace, $items[$i]["id_service"]);
            if ($name == null) {
                $name = '[!] '.($modelServices->getItemName($idSpace, $items[$i]["id_service"], true) ?? Constants::UNKNOWN);
            }
            $items[$i]["description"] = $name. ($items[$i]["quantity"] ? " [q=".$items[$i]["quantity"]."]" : "");
            $items[$i]["date"] = CoreTranslator::dateFromEn($items[$i]["date"], $lang);
            $items[$i]["invoice"] = $modelInvoice->getInvoiceNumber($idSpace, $items[$i]["id_invoice"]);
        }
        $tableHtml = $table->view($items, $headersArray);

        $formEdit = $this->createEditEntryForm($idSpace, $lang);

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "followup";
        $headerInfo["personInCharge"] = $project["in_charge"];

        return $this->render(array(
            "id_space" => $idSpace,
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

    public function kanbanAction($idSpace, $id_project)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $isManager = $this->role >= CORESPACE::$MANAGER;
        $id_task = $this->request->params()["task"] ?? 0;
        $taskModel= new SeTask();
        $tasks = $taskModel->getByProject($id_project, $idSpace);

        $projectModel = new SeProject();
        $project = $projectModel->getEntry($idSpace, $id_project);
        $projectServices = $projectModel->getProjectServicesDefault($idSpace, $id_project);


        $serviceModel = new SeService();
        $services = array();
        foreach ($projectServices as $projectService) {
            array_push($services, $serviceModel->getItem($idSpace, $projectService['id_service']));
        }
        for ($i=0; $i<count($tasks); $i++) {
            $tasks[$i]['services'] = $taskModel->getTaskServicesIds($idSpace, $tasks[$i]['id']);
            // cast private boolean attribute to string
            $tasks[$i]['private'] = $tasks[$i]['private'] ? "true" : "false";
        }


        $categoryModel = new SeTaskCategory();
        $categories = $categoryModel->getByProject($id_project, $idSpace);

        for ($i=0; $i<count($categories); $i++) {
            $categories[$i]['tasks'] = [];
        }

        $projectName = $projectModel->getName($idSpace, $id_project);
        $seProjectUsers = $projectModel->getProjectUsersIds($idSpace, $id_project);
        $projectMainUser = $project['id_user'];


        $modelUser = new CoreUser();
        $projectUsers = array();
        array_push($projectUsers, ['id' => 0, 'name' => '---', 'firstname' => '---']);

        $ids = [];
        foreach ($seProjectUsers as $seProjectUser) {
            $ids[] = $seProjectUser['id_user'];
            array_push($projectUsers, $modelUser->getUser($seProjectUser['id_user']));
        }

        $csu = new CoreSpaceUser();
        $managers = $csu->managersOrAdmin($idSpace);
        foreach ($managers as $manager) {
            if (in_array($manager['id_user'], $ids)) {
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
            "id_space" => $idSpace,
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

    public function setTaskAction($idSpace, $id_project)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $taskData = $this->request->params()['task'];
        $taskModel = new SeTask();

        // delete removed services
        if ($taskData['id'] > 0) {
            $dbTaskServices = $taskModel->getTaskServices($idSpace, $taskData['id']);
            foreach ($dbTaskServices as $dbTaskService) {
                if (!in_array($dbTaskService['id_service'], $taskData['services'])) {
                    $taskModel->deleteTaskService($idSpace, $taskData['id'], $dbTaskService['id_service']);
                }
            }
        }

        // add/update task
        $id = $taskModel->set(
            $taskData['id'],
            $idSpace,
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

    /* public function uploadTaskFileAction($idSpace, $id_task) {
        $taskModel = new SeTask();
        $target_dir = "data/services/projecttasks/" . $idSpace . "/";
        if (isset($_FILES) && isset($_FILES['file']) && $_FILES["file"]["name"] != "") {
            $fileName = pathinfo($_FILES["file"]["name"], PATHINFO_BASENAME);
            $url = $target_dir . $id_task . "_" . $fileName;

            // If target directory doesn't exist, creates it
            if(!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $uploaded = FileUpload::uploadFile($target_dir, "file", $id_task . "_" . $fileName);
            if ($uploaded) {
                $taskModel->setFile($idSpace, $id_task, $url, $fileName);
            }
        }
    }

    public function getTaskFileAction($idSpace, $id_task) {
        $taskModel = new SeTask();
        $file = $taskModel->getFile($idSpace, $id_task);
        $this->render(['data' => $file]);
    }

    public function openFileAction($idSpace, $id_task) {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $taskModel = new SeTask();
        $task = $taskModel->getById($idSpace, $id_task);

        // If private task, check if user is the owner of the task or if user is at least manager
        if ($task['private'] == 1
            && (!$this->role >= CoreSpace::$MANAGER && $task['id_owner'] != $_SESSION["id_user"])) {
                throw new PfmAuthException('private document');
        }

        $file = $taskModel->getFile($idSpace, $id_task)['file'];
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

    public function getTasksAction($idSpace, $id_project)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $taskModel = new SeTask();
        $tasks = $taskModel->getByProject($id_project, $idSpace);

        $userModel = new CoreUser();
        for ($i=0; $i<count($tasks); $i++) {
            $tasks[$i]['userName'] = $userModel->getUserFullName($tasks[$i]['id_user']);
        }
        $this->render(['data' => ['elements' => $tasks]]);
    }

    public function getTaskServicesAction($idSpace, $id_task)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $serviceModel = new SeService();
        $taskModel = new SeTask();
        $serviceIds = $taskModel->getTaskServicesIds($idSpace, $id_task);

        $services = [];
        foreach ($serviceIds as $serviceId) {
            array_push($services, $serviceModel->getItem($idSpace, $serviceId));
        }
        $this->render(['data' => ['elements' => $services]]);
    }

    public function deleteTaskAction($idSpace, $id_task)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $taskModel = new SeTask();
        return $taskModel->delete($idSpace, $id_task);
    }

    public function setTaskCategoryAction($idSpace, $id_project)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $categoryData = $this->request->params()['category'];
        $categoryModel = new SeTaskCategory();
        $id = $categoryModel->set($categoryData['id'], $idSpace, $id_project, $categoryData['name'], $categoryData['position'], $categoryData['color']);
        $this->render(['data' => ['id' => $id]]);
    }

    public function deleteTaskCategoryAction($idSpace, $id_category)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $categoryModel = new SeTaskCategory();
        return $categoryModel->delete($idSpace, $id_category);
    }

    protected function createEditEntryForm($idSpace, $lang)
    {
        $form = new Form($this->request, "editNoteForm", true);

        $modelServices = new SeService();
        $services = $modelServices->getForList($idSpace);

        $form->addHidden("formprojectentryid", 0);
        $form->addHidden("formprojectentryprojectid", 0);
        $form->addDate("formprojectentrydate", CoreTranslator::Date($lang), true, "");
        $form->addSelectMandatory("formserviceid", ServicesTranslator::service($lang), $services["names"], $services["ids"]);
        $form->addFloat("formservicequantity", ServicesTranslator::Quantity($lang), true, 0);
        $form->addTextArea("formservicecomment", ServicesTranslator::Comment($lang), false, "");

        $form->setColumnsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojecteditentryquery/" . $idSpace);
        return $form->getHtml($lang);
    }

    public function editentryqueryAction($idSpace)
    {
        $lang = $this->getLanguage();

        $id_entry = $this->request->getParameter("formprojectentryid");
        $id_project = $this->request->getParameter("formprojectentryprojectid");
        $date = CoreTranslator::dateToEn($this->request->getParameter("formprojectentrydate"), $lang);
        $id_service = $this->request->getParameter("formserviceid");
        $quantity = $this->request->getParameter("formservicequantity");
        $comment = $this->request->getParameter("formservicecomment");

        $modelProject = new SeProject();
        $id = $modelProject->setEntry($idSpace, $id_entry, $id_project, $id_service, $date, $quantity, $comment, 0);

        return $this->redirect("servicesprojectfollowup/" . $idSpace . "/" . $id_project, [], ['entry' => ['id' => $id]]);
    }

    public function deleteentryAction($idSpace, $id_project, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $modelProject = new SeProject();
        $modelProject->deleteEntry($idSpace, $id);

        $this->redirect("servicesprojectfollowup/" . $idSpace . "/" . $id_project);
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = $this->generateProjectForm($idSpace, $id, $lang);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectedit/" . $idSpace . "/" . $id);

        if ($form->check()) {
            $id_project = $this->updateProject($id, $idSpace, $lang);
            return $this->redirect("servicesprojectfollowup/" . $idSpace . "/" . $id_project, [], ['project' => ['id' => $id_project]]);
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    protected function updateProject($id, $idSpace, $lang)
    {
        $modelProject = new SeProject();
        $idUser = $this->request->getParameter("id_user") == "" ? "0" : $this->request->getParameter("id_user");
        $pic = $this->request->getParameter("in_charge");
        $id_project =
            $modelProject->setProject(
                $id,
                $idSpace,
                $this->request->getParameter("name"),
                $this->request->getParameter("id_client"),
                $idUser,
                CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang),
                CoreTranslator::dateToEn($this->request->getParameterNoException("date_close"), $lang),
                $this->request->getParameter("new_team"),
                $this->request->getParameter("new_project"),
                CoreTranslator::dateToEn($this->request->getParameter("time_limit"), $lang)
            );
        $modelProject->setOrigin($idSpace, $id_project, $this->request->getParameter("id_origin"));
        $modelProject->setInCharge($idSpace, $id_project, $pic);

        // add project users
        if ($this->request->getParameter("users") && !empty($this->request->getParameter("users"))) {
            $formProjectUserIds = $this->request->getParameter("users");
        }
        if ($idUser && !in_array($idUser, $formProjectUserIds)) {
            array_push($formProjectUserIds, $idUser);
            // if main project user not in users list, display warning
            $_SESSION['flash'] = ServicesTranslator::MainUserNotInList($lang);
        }

        if ($id>0) {
            // remove deleted users
            $dbProjectUserIds = [];
            $dbProjectUsers = $modelProject->getProjectUsersIds($idSpace, $id);
            foreach ($dbProjectUsers as $dbProjectUser) {
                array_push($dbProjectUserIds, $dbProjectUser["id_user"]);
            }
            $toDeleteList = array_diff($dbProjectUserIds, $formProjectUserIds);
            foreach ($toDeleteList as $toDelete) {
                $modelProject->deleteProjectUser($idSpace, $toDelete, $id);
            }
        }

        foreach ($formProjectUserIds as $user_id) {
            $modelProject->setProjectUser($idSpace, $user_id, $id_project);
        }
        return $id_project;
    }

    public function exportAction($idSpace, $id)
    {
        // get project entries
        $modelProject = new SeProject();
        $projectEntries = $modelProject->getProjectServicesBase($idSpace, $id);

        // calculate total sum and price HT
        $modelClient = new ClClient();
        $id_resp = $modelProject->getResp($idSpace, $id);
        $LABpricingid = $modelClient->getPricingID($idSpace, $id_resp);

        $itemPricing = new SePrice();


        $content = "Date ; Commentaire ; Prestation ; Quantité ; Prix ;  Total \r\n";
        $totalHT = 0;
        $modelItem = new SeService();
        //print_r($projectEntries);
        foreach ($projectEntries as $entry) {
            $content .= $entry["date"] . ";";
            $content .= str_replace(";", ",", $entry["comment"]) . ";";
            $content .= ($modelItem->getItemName($idSpace, $entry["id_service"]) ?? Constants::UNKNOWN) . ";";
            if ($modelItem->getItemType($idSpace, $entry["id_service"]) == 4) {
                $content .= 1 . ";";
                $unitPrice = $entry["quantity"];
                $entry["quantity"] = 1;
            } else {
                $content .= $entry["quantity"] . ";";
                $unitPrice = $itemPricing->getPrice($idSpace, $entry["id_service"], $LABpricingid);
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
