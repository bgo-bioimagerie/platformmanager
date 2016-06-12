<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReState.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RestatesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReState();
        $this->checkAuthorizationMenu("resources");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::States($lang));
        $table->addLineEditButton("restatesedit");
        $table->addDeleteButton("restatesdelete");
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang)
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
        $data = array("id" => 0, "name" => "", "color" => "#ffffff");
        if ($id > 0) {
            $data = $this->model->get($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "restatesedit");
        $form->setTitle(ResourcesTranslator::Edit_Event_Type($lang));
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $data["color"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "restatesedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "restates");

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("color"));
            $this->redirect("restates");
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
        $this->redirect("restates");
    }

}
