<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReVisa.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RevisasController extends ResourcesBaseController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->model = new ReArea();
        //$this->checkAuthorizationMenu("resources");

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
        $revisas = $visaTable;

        $table = new TableView ();

        $table->setTitle(ResourcesTranslator::Visa($lang), 3);
        //$table->ignoreEntry("id", 1);
        $table->addLineEditButton("resourceseditvisa/" . $id_space);
        $table->addDeleteButton("resourcesdeletevisa/" . $id_space, "id", "id");
        //$table->addPrintButton("sygrrifauthorisations/visa/");

        $modelResourceCategory = new ReCategory();
        $modelUser = new CoreUser();
        for ($i = 0; $i < count($visaTable); $i++) {

            $visaTable[$i]["id_resource_category"] = $modelResourceCategory->getName($id_space, $visaTable[$i]["id_resource_category"]);
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

        return $this->render(array(
            'data' => ['revisas' => $revisas],
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

        $visaInfo = array("id" => 0, "is_active" => 1, "id_resource_category" => 0, "id_instructor" => 0, "instructor_status" => 1);
        if ($id > 0) {
            $modelVisa = new ReVisa();
            $visaInfo = $modelVisa->getVisa($id_space, $id);
        }
        //print_r($visaInfo);

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

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");

        if (empty($users['ids']) || empty($resourcesCategories)) {
            $_SESSION['flash'] = ResourcesTranslator::User_category_Needed($lang);
            $_SESSION['flashClass'] = "warning";
        }

        $form->addSelect("id_instructor", CoreTranslator::User($lang), $users["names"], $users["ids"], $visaInfo["id_instructor"]);
        $form->addSelect("is_active", ResourcesTranslator::IsActive($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $visaInfo["is_active"]);
        $ischoicesid = array(1, 2);
        $ischoices = array(ResourcesTranslator::Instructor($lang), CoreTranslator::Responsible($lang));
        $form->addSelect("instructor_status", ResourcesTranslator::Instructor_status($lang), $ischoices, $ischoicesid, $visaInfo["instructor_status"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "resourceseditvisa/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "resourcesvisa/" . $id_space);

        if ($form->check()) {
            // run the database query
            $modelVisa = new ReVisa();
            if ($id > 0) {
                $modelVisa->editVisa($id_space, $id, $form->getParameter("id_resource_category"), $form->getParameter("id_instructor"), $form->getParameter("instructor_status"));
            } else {
                $id = $modelVisa->addVisa($id_space, $form->getParameter("id_resource_category"), $form->getParameter("id_instructor"), $form->getParameter("instructor_status"));
            }
            $modelVisa->setActive($id_space, $id, $form->getParameter("is_active"));
            return $this->redirect("resourcesvisa/" . $id_space, [], ['revisa' => ['id' => $id]]);
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
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
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
        $modelUser = new CoreUser();
        foreach ($instructors as $instructor) {
            $content .= $modelUser->getUserFUllName($instructor["id_instructor"]) . ";";
            foreach ($resources as $resource) {
                $found = 0;
                foreach ($visas as $visa) {
                    if ($visa["id_resource_category"] == $resource["id"] && $visa["id_instructor"] == $instructor["id_instructor"]) {

                        $instructorStatus = ResourcesTranslator::Instructor($lang);
                        if ($visa["instructor_status"] == 2) {
                            $instructorStatus = CoreTranslator::Responsible($lang);
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
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        $modelVisa = new ReVisa();
        $modelVisa->delete($id_space, $id);
        
        $this->redirect("resourcesvisa/" . $id_space);
    }

    public function getCategoryvisasAction($id_space, $id_category) {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        $modelReVisa = new ReVisa();
        $visas = $modelReVisa->getForListByCategory($id_space, $id_category);
        $data = array();
        for ($i=0; $i<count($visas['ids']); $i++) {
            $data[$i] = ["id" => $visas["ids"][$i], "name" => $visas["names"][$i]];
        }
        $this->render(['data' => ['elements' => $data]]);
    }
}
