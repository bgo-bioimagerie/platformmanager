<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeVisa.php';
/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesvisaController extends CoresecureController {

    private $visaModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $_SESSION["openedNav"] = "services";
        $this->visaModel = new SeVisa();
        //$this->checkAuthorizationMenu("services");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Visa($lang), 3);
        
        $table->addLineEditButton("servicesvisaedit/" . $id_space);
        $table->addDeleteButton("servicesvisadelete/" . $id_space, "id", "id");

        $headersArray = array(
            "id" => "ID",
            "user_name" => CoreTranslator::User($lang)
        );

        $modelVisa = new SeVisa();
        $entriesArray = $modelVisa->getAll($id_space);
        $tableHtml = $table->view($entriesArray, $headersArray);

        // 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
                ), "indexAction");
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($id == 0) {
            $value = array("id" => 0,  "id_user" => 0);
        } else {
            $value = $this->visaModel->get($id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Visa($lang));

        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $value["id_user"]);
       
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesvisaedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "servicesvisas/" . $id_space);

        if ($form->check()) {
            $this->visaModel->set($id, $this->request->getParameter("id_user") 
                    , $id_space);
            
            $this->redirect("servicesvisas/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->visaModel->delete($id);
        $this->redirect("servicesvisas/" . $id_space);
    }
}
