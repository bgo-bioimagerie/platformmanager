<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ReeventtypesController extends ResourcesBaseController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new ReEventType();
        $_SESSION["openedNav"] = "resources";
        //$this->checkAuthorizationMenu("resources");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Event_types($lang), 3);
        $table->addLineEditButton("reeventtypesedit/".$id_space);
        $table->addDeleteButton("reeventtypesdelete/".$id_space);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $categories = $this->model->getForSpace($id_space);

        $tableHtml = $table->view($categories, $headers);

        $this->render(array( "id_space" => $id_space, "lang" => $lang, "htmlTable" => $tableHtml));
    }

    /**
     * Edit form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        $site = array("id" => 0, "name" => "", "id_space" => $id_space);
        if ($id > 0) {
            $site = $this->model->get($id_space, $id);
        }

        // lang
        $lang = $this->getLanguage();
        
        // form
        // build the form
        $form = new Form($this->request, "reeventtypesedit");
        $form->setTitle(ResourcesTranslator::Edit_Event_Type($lang), 3);
        $form->addHidden("id", $site["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $site["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "reeventtypesedit/".$id_space ."/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reeventtypes/".$id_space);

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), $id_space);
            $this->redirect("reeventtypes/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $this->model->delete($id_space ,$id);
        $this->redirect("reeventtypes/".$id_space);
    }

}
