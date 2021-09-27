<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReCategory.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RecategoriesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->categoryModel = new ReCategory();
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
        $table->setTitle(ResourcesTranslator::Categories($lang), 3);
        
        $table->addLineEditButton("recategoriesedit/".$id_space);
        $table->addDeleteButton("recategoriesdelete/".$id_space);
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $categories = $this->categoryModel->getBySpace($id_space);
        $tableHtml = $table->view($categories, $headers);
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        $data = array("id" => 0, "name" => "", "id_space" => $id_space);
        if ($id > 0) {
            $data = $this->categoryModel->get($id_space, $id);
        }
        
        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "recategoriesedit/".$id_space);
        $form->setTitle(ResourcesTranslator::Edit_Category($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "recategoriesedit/".$id_space."/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "recategories/".$id_space);

        if ($form->check()) {
            // run the database query
            $model = new ReCategory();
            $model->set($form->getParameter("id"), $form->getParameter("name"), $id_space);
            $this->redirect("recategories/".$id_space);
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
    
    public function deleteAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $this->categoryModel->delete($id_space, $id);
        $this->redirect("recategories/".$id_space);
    }
}
