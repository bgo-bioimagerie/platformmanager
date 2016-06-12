<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReEventType.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ReeventtypesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReEventType();
        $this->checkAuthorizationMenu("resources");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Event_types($lang));
        $table->addLineEditButton("reeventtypesedit");
        $table->addDeleteButton("reeventtypesdelete");
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $categories = $this->model->getAll();
            
        $tableHtml = $table->view($categories, $headers);
        
        $this->render(array("lang" => $lang, "htmlTable" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id) {

        // get belonging info
        $site = array("id" => 0, "name" => "");
        if ($id > 0) {
            $site = $this->model->get($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "reeventtypesedit");
        $form->setTitle(ResourcesTranslator::Edit_Event_Type($lang));
        $form->addHidden("id", $site["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $site["name"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "reeventtypesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reeventtypes");

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"));
            $this->redirect("reeventtypes");
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }
    
    public function deleteAction($id){
        $this->model->delete($id);
        $this->redirect("reeventtypes");
    }

}
