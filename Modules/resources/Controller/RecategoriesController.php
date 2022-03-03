<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class RecategoriesController extends ResourcesBaseController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->categoryModel = new ReCategory();
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
        $table->setTitle(ResourcesTranslator::Categories($lang), 3);
        
        $table->addLineEditButton("recategoriesedit/".$id_space);
        $table->addDeleteButton("recategoriesdelete/".$id_space);
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $categories = $this->categoryModel->getBySpace($id_space);
        $tableHtml = $table->view($categories, $headers);
        return $this->render(array("data" => ["recategories" => $categories], "id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
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

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "recategoriesedit/".$id_space."/".$id;
        if ($todo) {
            $validationUrl .= "&redirect=todo";
        }
        
        $form->setValidationButton(CoreTranslator::Ok($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "recategories/".$id_space);

        if ($form->check()) {
            // run the database query
            $model = new ReCategory();
            $id_cat = $model->set($form->getParameter("id"), $form->getParameter("name"), $id_space);

            $flashMessage = ResourcesTranslator::Item_created("category", $lang);
            $flashClass = "success";

            if (!$todo) {
                $_SESSION["flash"] = $flashMessage;
                $_SESSION["flashClass"] = $flashClass;
                return $this->redirect("recategories/".$id_space, [], ['recategory' => ['id' => $id_cat]]);
            } else {
                $this->redirect("spaceadminedit/" . $id_space, ["flash" => $flashMessage, "flashClass" => $flashClass, "showTodo" => true]);
            }
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['recategory' => $data]
            ));
        }
    }
    
    public function deleteAction($id_space, $id) {
        $lang = $this->getLanguage();
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);

        // check if category is linked to resources. If yes, deletion is not authorized => warn the user
        $resourceModel = new ResourceInfo();
        $linkedResources = $resourceModel->resourcesForCategory($id_space, $id);

        $error = null;
        if ($linkedResources == null || empty($linkedResources)) {
            // not linked to resources, deletion is authorized
            $this->categoryModel->delete($id_space, $id);
        } else {
            // linked to resources, notify the user
            $_SESSION['flash'] = ResourcesTranslator::DeletionNotAuthorized(ResourcesTranslator::Category($lang), $lang);
            $error = 'deletionnotauthorized';
        }
        return $this->redirect("recategories/".$id_space, [], ['error' => $error]);
    }
}
