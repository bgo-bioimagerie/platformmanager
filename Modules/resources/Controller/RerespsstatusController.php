<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';

require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RerespsstatusController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReRespsStatus();
        //$this->checkAuthorizationMenu("resources");
        $_SESSION["openedNav"] = "resources";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Resps_Status($lang), 3);
        $table->addLineEditButton("rerespsstatusedit/".$id_space);
        $table->addDeleteButton("rerespsstatusdelete/".$id_space);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $data = $this->model->getForSpace($id_space);

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "htmlTable" => $tableHtml));
    }

    /**
     * Edit form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        $data = array("id" => 0, "name" => "", "id_space" => $id_space);
        if ($id > 0) {
            $data = $this->model->get($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "rerespsstatusedit");
        $form->setTitle(ResourcesTranslator::Edit_Resps_status($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "rerespsstatusedit/".$id_space. "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "rerespsstatus/".$id_space);

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), $id_space);
            $this->redirect("rerespsstatus/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                "id_space" => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    public function deleteAction($id_space, $id) {
        
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $this->model->delete($id);
        $this->redirect("rerespsstatus/".$id_space);
    }

}
