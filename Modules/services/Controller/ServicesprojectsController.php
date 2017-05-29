<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Model/SeVisa.php';

require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesprojectsController extends CoresecureController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $_SESSION["openedNav"] = "services";
        //$this->checkAuthorizationMenu("services");
    }

    protected function getProjectPeriod($id_space, $year) {
        $modelCoreConfig = new CoreConfig();
        $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $id_space);

        $projectperiodbeginArray = explode("-", $projectperiodbegin);
        $previousYear = $year - 1;
        $yearBegin = $previousYear . "-" . $projectperiodbeginArray[1] . "-" . $projectperiodbeginArray[2];
        $projectperiodendArray = explode("-", $projectperiodend);
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

            $years = $modelEntry->closedProjectsYears($id_space);
            $yearsUrl = "servicesprojectsclosed";
            if ($year == "") {
                $year = $years[count($years) - 1];
            }
            $dates = $this->getProjectPeriod($id_space, $year);
            $title = ServicesTranslator::Closed_projects($lang);
            $entriesArray = $modelEntry->closedEntries($id_space, $dates['yearBegin'], $dates['yearEnd'], $sortentry);
        }

        //echo "year = " . $year . "<br/>"; 
        //echo "years = ";
        //print_r($years);

        $table = new TableView();
        $table->setTitle($title, 3);
        $table->setColorIndexes(array("all" => "color", "time_limit" => "time_color", "date_close" => "closed_color"));

        $table->addLineEditButton("servicesprojectsheet/" . $id_space);
        $table->addDeleteButton("servicesprojectdelete/" . $id_space, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "resp_name" => CoreTranslator::Responsible($lang),
            "name" => ServicesTranslator::No_identification($lang),
            "user_name" => CoreTranslator::User($lang),
            "date_open" => ServicesTranslator::Opened_date($lang),
            "time_limit" => ServicesTranslator::Time_limite($lang),
            "date_close" => ServicesTranslator::Closed_date($lang),
            "close_icon" => array("title" => "", "type" => "glyphicon"),
        );

        $modelUser = new EcUser();
        $modelUnit = new EcUnit();
        $modelBelonging = new EcBelonging();
        
        $modelConfig = new CoreConfig();
        $warning = $modelConfig->getParamSpace("SeProjectDelayWarning", $id_space);
        
        for ($i = 0; $i < count($entriesArray); $i++) {

            $entriesArray[$i]["close_icon"] = "";
            
            //echo "date clode = " . $entriesArray[$i]["date_close"] . "<br/>";
            if ($entriesArray[$i]["date_close"] == "" || $entriesArray[$i]["date_close"] == "0000-00-00"){
                
                if($entriesArray[$i]["time_limit"] == "" || $entriesArray[$i]["time_limit"] == "0000-00-00"){
                    
                }
                else{
                
                    $limiteArray = explode('-',$entriesArray[$i]["time_limit"]);
                    $limitD = mktime(0,0,0,$limiteArray[1], $limiteArray[2], $limiteArray[0]);

                    $today = time();

                    //echo "limite time = " . $entriesArray[$i]["time_limit"] . "<br/>";

                    $delay = $limitD - $today;
                    //echo "delay = " . $delay . "<br/>";

                    //$warning = 30;
                    if( $delay < 0 || $delay < $warning*3600){
                        $entriesArray[$i]["close_icon"] = "glyphicon glyphicon-warning-sign";
                    }
                }
            }
            
            $entriesArray[$i]["date_open"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_open"], $lang);
            $entriesArray[$i]["date_close"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_close"], $lang);
            $entriesArray[$i]["time_limit"] = CoreTranslator::dateFromEn($entriesArray[$i]["time_limit"], $lang);

            // get the pricing color
            $id_unit = $modelUser->getUnit($entriesArray[$i]["id_resp"]);
            $id_belonging = $modelUnit->getBelonging($id_unit, $id_space);
            $pricingInfo = $modelBelonging->getInfo($id_belonging);
            $entriesArray[$i]["color"] = $pricingInfo["color"];

            $entriesArray[$i]["time_color"] = "#ffffff";
            if ($entriesArray[$i]["time_limit"] != "") {

                if (strval($entriesArray[$i]["time_limit"]) != "0000-00-00") {
                    $entriesArray[$i]["time_color"] = "#FFCC00";
                }
            }


            $entriesArray[$i]["closed_color"] = "#ffffff";
            if ($entriesArray[$i]["date_close"] != "0000-00-00") {
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
        $serviceModel->delete($id);
        $this->redirect("services/" . $id_space);
    }

    public function sheetAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "projectEditForm");
        //$form->setTitle(ServicesTranslator::Edit_projects($lang), 3);

        $modelProject = new SeProject();
        $projectName = $modelProject->getName($id);

        if ($id > 0) {
            $value = $modelProject->getEntry($id);
            $items = $modelProject->getProjectServices($id);
        } else {
            $value = $modelProject->defaultEntryValues();
            $items = array("dates" => array(), "services" => array(), "quantities" => array(), "comment" => array());
        }

        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        $resps = $modelUser->getAcivesRespsForSelect("name");

        //$form->addSeparator(CoreTranslator::Description($lang));
        $form->addSelect("id_resp", CoreTranslator::Responsible($lang), $resps["names"], $resps["ids"], $value["id_resp"]);
        $form->addText("name", ServicesTranslator::No_identification($lang), false, $value["name"]);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);

        $newIDs = array(1, 2, 3);
        $newNames = array(CoreTranslator::no($lang), ServicesTranslator::Academique($lang), ServicesTranslator::Industry($lang));

        $form->addSelect("new_team", ServicesTranslator::New_team($lang), $newNames, $newIDs, $value["new_team"]);
        $form->addSelect("new_project", ServicesTranslator::New_project($lang), $newNames, $newIDs, $value["new_project"]);

        $modelOrigin = new SeOrigin();
        $origins = $modelOrigin->getForList($id_space);
        $form->addSelect("id_origin", ServicesTranslator::servicesOrigin($lang), $origins['names'], $origins['ids'], $value["id_origin"]);

        $form->addDate("time_limit", ServicesTranslator::Time_limite($lang), false, CoreTranslator::dateFromEn($value["time_limit"], $lang));
        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, CoreTranslator::dateFromEn($value["date_open"], $lang));
        if ($id > 0) {
            $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, CoreTranslator::dateFromEn($value["date_close"], $lang));
            
            $modelVisa = new SeVisa();
            $visas = $modelVisa->getForList($id_space);
            
            $form->addSelect("closed_by", ServicesTranslator::Closed_by($lang), $visas["names"], $visas["ids"], $value["closed_by"]);
            
        } else {
            $form->addHidden("date_close", $value["date_close"]);
            $form->addHidden("closed_by", $value["closed_by"]);
        }
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectsheet/" . $id_space . "/" . $id);
        $form->setButtonsWidth(2, 10);

        if ($form->check()) {

            $id = $modelProject->setProject($id, $id_space, $this->request->getParameter("name"), 
                    $this->request->getParameter("id_resp"), $this->request->getParameter("id_user"), 
                    CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang), 
                    CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang), 
                    $this->request->getParameter("new_team"), $this->request->getParameter("new_project"), 
                    CoreTranslator::dateToEn($this->request->getParameter("time_limit"), $lang));
            $modelProject->setOrigin($id, $this->request->getParameter("id_origin"));
            $modelProject->setClosedBy($id, $this->request->getParameter("closed_by"));

            $_SESSION["message"] = ServicesTranslator::projectEdited($lang);
            $this->redirect("servicesprojectsheet/" . $id_space . "/" . $id);
            return;
        }

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "sheet";

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang),
            "headerInfo" => $headerInfo, "projectName" => $projectName));
    }

    public function followupAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelProject = new SeProject();
        $projectName = $modelProject->getName($id);

        $table = new TableView();
        $table->addLineEditButton("editentry", "id", true);
        $table->addDeleteButton("servicesprojectdeleteentry/" . $id_space . "/" . $id, "id", "id");

        $headersArray = array(
            "date" => CoreTranslator::Date($lang),
            "description" => ServicesTranslator::Description($lang),
            "comment" => ServicesTranslator::Comment($lang)
        );

        $modelServices = new SeService();
        $items = $modelProject->getProjectServicesDefault($id);
        for ($i = 0; $i < count($items); $i++) {
            $items[$i]["description"] = $items[$i]["quantity"] . " " . $modelServices->getItemName($items[$i]["id_service"]);
            $items[$i]["date"] = CoreTranslator::dateFromEn($items[$i]["date"], $lang);
        }
        $tableHtml = $table->view($items, $headersArray);

        $formEdit = $this->createEditEntryForm($id_space, $lang);

        $headerInfo["projectId"] = $id;
        $headerInfo["curentTab"] = "followup";

        $this->render(array("id_space" => $id_space, "lang" => $lang, "projectName" => $projectName,
            "tableHtml" => $tableHtml, "headerInfo" => $headerInfo,
            "formedit" => $formEdit, "projectEntries" => $items,
            "id_project" => $id));
    }

    protected function createEditEntryForm($id_space, $lang) {
        $form = new Form($this->request, "editNoteForm", true);

        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $form->addHidden("formprojectentryid", 0);
        $form->addHidden("formprojectentryprojectid", 0);
        $form->addDate("formprojectentrydate", CoreTranslator::Date($lang), true, "");
        $form->addSelect("formserviceid", ServicesTranslator::service($lang), $services["names"], $services["ids"]);
        $form->addText("formservicequantity", ServicesTranslator::Quantity($lang), true, 0);
        $form->addTextArea("formservicecomment", ServicesTranslator::Comment($lang), false, "", false);

        $form->setColumnsWidth(2, 9);
        $form->setButtonsWidth(2, 10);
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
        $modelProject->setEntry($id_entry, $id_project, $id_service, $date, $quantity, $comment, 0);

        $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project);
    }

    public function deleteentryAction($id_space, $id_project, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $modelProject = new SeProject();
        $modelProject->deleteEntry($id);

        $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project);
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "projectEditForm");
        $form->setTitle(ServicesTranslator::Add_projects($lang), 3);

        $modelProject = new SeProject();

        if ($id > 0) {
            $value = $modelProject->getEntry($id);
            $items = $modelProject->getProjectServices($id);
        } else {
            $value = $modelProject->defaultEntryValues();
            $items = array("dates" => array(), "services" => array(), "quantities" => array(), 
                "comment" => array());
        }

        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        $resps = $modelUser->getAcivesRespsForSelect("name");

        //$form->addSeparator(CoreTranslator::Description($lang));
        $form->addSelect("id_resp", CoreTranslator::Responsible($lang), $resps["names"], $resps["ids"], $value["id_resp"]);
        $form->addText("name", ServicesTranslator::No_identification($lang), false, $value["name"]);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);

        $newIDs = array(1, 2, 3);
        $newNames = array(CoreTranslator::no($lang), ServicesTranslator::Academique($lang), ServicesTranslator::Industry($lang));

        $form->addSelect("new_team", ServicesTranslator::New_team($lang), $newNames, $newIDs, $value["new_team"]);
        $form->addSelect("new_project", ServicesTranslator::New_project($lang), $newNames, $newIDs, $value["new_project"]);

        $modelOrigin = new SeOrigin();
        $origins = $modelOrigin->getForList($id_space);
        $form->addSelect("id_origin", ServicesTranslator::servicesOrigin($lang), $origins['names'], $origins['ids'], $value["id_origin"]);

        $form->addDate("time_limit", ServicesTranslator::Time_limite($lang), false, CoreTranslator::dateFromEn($value["time_limit"], $lang));
        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, CoreTranslator::dateFromEn($value["date_open"], $lang));
        if ($id > 0) {
            $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, CoreTranslator::dateFromEn($value["date_close"], $lang));
        } else {
            $form->addHidden("date_close", $value["date_close"]);
        }

        if ($id > 0) {
            $modelServices = new SeService();
            $services = $modelServices->getForList($id_space);

            $formAdd = new FormAdd($this->request, "projectEditForm");

            $trDates = array();
            foreach ($items["dates"] as $d) {
                $trDates[] = CoreTranslator::dateFromEn($d, $lang);
            }

            $formAdd->addDate("date", CoreTranslator::Date($lang), $trDates);
            $formAdd->addSelect("services", ServicesTranslator::services($lang), $services["names"], $services["ids"], $items["services"]);
            $formAdd->addNumber("quantities", ServicesTranslator::Quantity($lang), $items["quantities"]);
            $formAdd->addText("comment", ServicesTranslator::Comment($lang), $items["comments"]);
            $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
            $form->addSeparator(ServicesTranslator::Services_list($lang));
            $form->setFormAdd($formAdd);
        }

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprojectedit/" . $id_space . "/" . $id);
        $form->setButtonsWidth(2, 10);

        if ($form->check()) {

            $id_project = $modelProject->setProject($id, $id_space, $this->request->getParameter("name"), $this->request->getParameter("id_resp"), $this->request->getParameter("id_user"), CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang), CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang), $this->request->getParameter("new_team"), $this->request->getParameter("new_project"), CoreTranslator::dateToEn($this->request->getParameter("time_limit"), $lang));

            /*
            if ($id > 0) {
                $servicesDates = $this->request->getParameter("date");
                $servicesIds = $this->request->getParameter("services");
                $servicesQuantities = $this->request->getParameter("quantities");
                $servicesComments = $this->request->getParameter("comment");

                for ($i = 0; $i < count($servicesDates); $i++) {
                    $servicesDates[$i] = CoreTranslator::dateToEn($servicesDates[$i], $lang);
                }


                for ($i = 0; $i < count($servicesQuantities); $i++) {
                    if ($id == 0) {
                        $qOld = 0;
                    } else {
                        $qOld = $modelProject->getProjectServiceQuantity($id, $servicesIds[$i]);
                    }
                    $qDelta = $servicesQuantities[$i] - $qOld[0];
                    $modelServices->editquantity($servicesIds[$i], $qDelta, "subtract");
                    $modelProject->setService($id, $servicesIds[$i], $servicesDates[$i], $servicesQuantities[$i], $servicesComments[$i]);
                }
                $modelProject->removeUnsetServices($id, $servicesIds, $servicesDates);
            }
            */
            $this->redirect("servicesprojectfollowup/" . $id_space . "/" . $id_project);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function exportAction($id_space, $id) {
        // get project entries
        $modelProject = new SeProject();
        $projectEntries = $modelProject->getProjectServicesBase($id);

        // calculate total sum and price HT
        $modelUser = new EcUser();
        $modelUnit = new ECUnit();

        $id_resp = $modelProject->getResp($id);
        $id_unit = $modelUser->getUnit($id_resp);

        $LABpricingid = $modelUnit->getBelonging($id_unit, $id_space);

        $itemPricing = new SePrice();


        $content = "Date ; Commentaire ; Prestation ; Quantité ; Prix ;  Total \r\n";
        $totalHT = 0;
        $modelItem = new SeService();
        //print_r($projectEntries);
        foreach ($projectEntries as $entry) {

            $content .= $entry["date"] . ";";
            $content .= str_replace(";", ",", $entry["comment"]) . ";";
            $content .= $modelItem->getItemName($entry["id_service"]) . ";";
            if ($modelItem->getItemType($entry["id_service"]) == 4){
            
                $content .= 1 . ";";
                $unitPrice = $entry["quantity"];
                $entry["quantity"] = 1;
            }
            else{
                $content .= $entry["quantity"] . ";";
                $unitPrice = $itemPricing->getPrice($entry["id_service"], $LABpricingid);
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
