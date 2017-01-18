<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

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
        //$this->checkAuthorizationMenu("services");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $status = "") {

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

        if ($status == "all") {
            $entriesArray = $modelEntry->entries($sortentry);
        } else if ($status == "opened") {
            $entriesArray = $modelEntry->openedEntries($sortentry);
        } else if ($status == "closed") {
            $entriesArray = $modelEntry->closedEntries($sortentry);
        }

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Services_Projects($lang), 3);
        $table->addLineEditButton("servicesprojectedit/" . $id_space);
        $table->addDeleteButton("servicesprojectdelete/" . $id_space, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "resp_name" => CoreTranslator::Responsible($lang),
            "name" => ServicesTranslator::No_identification($lang),
            "user_name" => CoreTranslator::User($lang),
            "date_open" => ServicesTranslator::Opened_date($lang),
            "time_limit" => ServicesTranslator::Time_limite($lang),
            "date_close" => ServicesTranslator::Closed_date($lang)
        );


        for ($i = 0; $i < count($entriesArray); $i++) {
           
            $entriesArray[$i]["date_open"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_open"], $lang);
            $entriesArray[$i]["date_close"] = CoreTranslator::dateFromEn($entriesArray[$i]["date_close"], $lang);
            $entriesArray[$i]["time_limit"] = CoreTranslator::dateFromEn($entriesArray[$i]["time_limit"], $lang);
        }
        $tableHtml = $table->view($entriesArray, $headersArray);

        if ($table->isPrint()) {
            echo $tableHtml;
            return;
        }

        // 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
                ), "indexAction");
    }

    public function openedAction($id_space) {
        $_SESSION["project_lastvisited"] = "opened";
        $this->indexAction($id_space, "opened");
    }

    public function closedAction($id_space) {
        $_SESSION["project_lastvisited"] = "closed";
        $this->indexAction($id_space, "closed");
    }

    public function AllAction($id_space) {

        $_SESSION["project_lastvisited"] = "all";
        $this->indexAction($id_space, "all");
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id);
        $this->redirect("services/" . $id_space);
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "projectEditForm");
        $form->setTitle(ServicesTranslator::Edit_projects($lang), 3);

        $modelProject = new SeProject();

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

        $form->addSeparator(CoreTranslator::Description($lang));
        $form->addSelect("id_resp", CoreTranslator::Responsible($lang), $resps["names"], $resps["ids"], $value["id_resp"]);
        $form->addText("name", ServicesTranslator::No_identification($lang), false, $value["name"]);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
        
        $newIDs = array(1,2,3);
        $newNames = array(CoreTranslator::no($lang),ServicesTranslator::Academique($lang),ServicesTranslator::Industry($lang));
        
        $form->addSelect("new_team", ServicesTranslator::New_team($lang), $newNames, $newIDs, $value["new_team"]);
        $form->addSelect("new_project", ServicesTranslator::New_project($lang), $newNames, $newIDs, $value["new_project"]);
        
        $form->addDate("time_limit", ServicesTranslator::Time_limite($lang), false, CoreTranslator::dateFromEn($value["time_limit"], $lang));
        $form->addDate("date_open", ServicesTranslator::Opened_date($lang), false, CoreTranslator::dateFromEn($value["date_open"], $lang));
        if ($id > 0){
            $form->addDate("date_close", ServicesTranslator::Closed_date($lang), false, CoreTranslator::dateFromEn($value["date_close"], $lang));
        }
        else{
            $form->addHidden("date_close", $value["date_close"]);
        }
        	
        if ($id > 0){
            $modelServices = new SeService();
            $services = $modelServices->getForList($id_space);

            $formAdd = new FormAdd($this->request, "projectEditForm");
            
            $trDates = array();
            foreach($items["dates"] as $d){
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

            $modelProject->setProject($id, $id_space, $this->request->getParameter("name"), $this->request->getParameter("id_resp"), $this->request->getParameter("id_user"), 
 CoreTranslator::dateToEn($this->request->getParameter("date_open"), $lang), CoreTranslator::dateToEn($this->request->getParameter("date_close"), $lang), $this->request->getParameter("new_team"), $this->request->getParameter("new_project"), CoreTranslator::dateToEn($this->request->getParameter("time_limit"), $lang));
            
            
            if ($id > 0){
                $servicesDates = $this->request->getParameter("date");
                $servicesIds = $this->request->getParameter("services");
                $servicesQuantities = $this->request->getParameter("quantities");
                $servicesComments = $this->request->getParameter("comment");
                
                for($i=0 ; $i<count($servicesDates) ; $i++){
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
            $this->redirect("servicesprojects/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

}
