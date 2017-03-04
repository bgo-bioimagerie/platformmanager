<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReVisa.php';
require_once 'Modules/resources/Model/ReCategory.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RevisasController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReArea();
        //$this->checkAuthorizationMenu("resources");
        $_SESSION["openedNav"] = "resources";
    }

    /**
     * List of Visa
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get the user list
        $visaModel = new ReVisa();
        $visaTable = $visaModel->getVisasBySpace($id_space);

        $table = new TableView ();

        $table->setTitle(ResourcesTranslator::Visa($lang), 3);
        //$table->ignoreEntry("id", 1);
        $table->addLineEditButton("resourceseditvisa/" . $id_space);
        $table->addDeleteButton("resourcesdeletevisa/" . $id_space, "id", "id");
        //$table->addPrintButton("sygrrifauthorisations/visa/");

        $modelResourceCategory = new ReCategory();
        $modelUser = new CoreUser();
        for ($i = 0; $i < count($visaTable); $i++) {


            $visaTable[$i]["id_resource_category"] = $modelResourceCategory->getName($visaTable[$i]["id_resource_category"]);
            $visaTable[$i]["id_instructor"] = $modelUser->getUserFUllName($visaTable[$i]["id_instructor"]);
            if ($visaTable[$i]["instructor_status"] == 1) {
                $visaTable[$i]["instructor_status"] = ResourcesTranslator::Instructor($lang);
            } else {
                $visaTable[$i]["instructor_status"] = CoreTranslator::Responsible($lang);
            }
        }

        $tableContent = array(
            "id" => "ID",
            "id_resource_category" => ResourcesTranslator::Categories($lang),
            "id_instructor" => ResourcesTranslator::Instructor($lang),
            "instructor_status" => ResourcesTranslator::Instructor_status($lang)
        );
        $tableHtml = $table->view($visaTable, $tableContent);

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Form to add a visa
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $visaInfo = array("id" => 0, "id_resource_category" => 0, "id_instructor" => 0, "instructor_status" => 1);
        if ($id > 0) {
            $modelVisa = new ReVisa();
            $visaInfo = $modelVisa->getVisa($id);
        }

        // build the form
        $form = new Form($this->request, "formeditVisa");
        $form->setTitle(ResourcesTranslator::Edit_Visa($lang), 3);

        $modelResourcesCategory = new ReCategory();
        $resourcesCategories = $modelResourcesCategory->getBySpace($id_space);
        $rcchoices = array();
        $rcchoicesid = array();
        foreach ($resourcesCategories as $rc) {
            $rcchoicesid[] = $rc["id"];
            $rcchoices[] = $rc["name"];
        }
        $form->addSelect("id_resource_category", ResourcesTranslator::Categories($lang), $rcchoices, $rcchoicesid, $visaInfo["id_resource_category"]);

        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        $form->addSelect("id_instructor", CoreTranslator::User($lang), $users["names"], $users["ids"], $visaInfo["id_instructor"]);

        $ischoicesid = array(1, 2);
        $ischoices = array(ResourcesTranslator::Instructor($lang), CoreTranslator::Responsible($lang));
        $form->addSelect("instructor_status", ResourcesTranslator::Instructor_status($lang), $ischoices, $ischoicesid, $visaInfo["instructor_status"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "resourceseditvisa/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "resourcesvisa/" . $id_space);

        if ($form->check()) {
            // run the database query
            $modelVisa = new ReVisa();
            if ($id > 0) {
                $modelVisa->editVisa($id, $form->getParameter("id_resource_category"), $form->getParameter("id_instructor"), $form->getParameter("instructor_status"));
            } else {
                $modelVisa->addVisa($form->getParameter("id_resource_category"), $form->getParameter("id_instructor"), $form->getParameter("instructor_status"));
            }
            $this->redirect("resourcesvisa/" . $id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'lang' => $lang,
                'id_space' => $id_space,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Export the visas in an xls file
     */
    public function exportAction($id_space) {

        $lang = $this->getLanguage();

        // get all the resources categories
        $modelResources = new ReCategory();
        $resources = $modelResources->getBySpace($id_space);

        // get all the instructors
        $modelVisa = new ReVisa();
        $instructors = $modelVisa->getSpaceInstructors($id_space);

        //print_r($instructors);

        $visas = $modelVisa->getForSpace($id_space);

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=visas.csv");

        // resources
        $content = ";";
        foreach ($resources as $resource) {
            $content .= $resource["name"] . ";";
        }
        $content.= "\r\n";

        // instructors
        $modelUser = new EcUser();
        foreach ($instructors as $instructor) {
            $content .= $modelUser->getUserFUllName($instructor["id_instructor"]) . ";";
            foreach ($resources as $resource) {
                $found = 0;
                foreach ($visas as $visa) {
                    if ($visa["id_resource_category"] == $resource["id"] && $visa["id_instructor"] == $instructor["id_instructor"]) {

                        $instructorStatus = ResourcesTranslator::Instructor($lang);
                        if ($visa["instructor_status"] == 2) {
                            $instructorStatus = EcosystemTranslator::Responsible($lang);
                        }
                        $content .= $instructorStatus . ";";
                        $found = 1;
                        break;
                    }
                }
                if ($found == 0) {
                    $content .= ";";
                }
            }
            $content.= "\r\n";
        }
        $content.= "\r\n";
        echo $content;
    }

    
    public function deleteAction($id_space, $id){
        
        $modelVisa = new ReVisa();
        $modelVisa->delete($id);
        
        $this->redirect("resourcesvisa/" . $id_space);
    }
}
