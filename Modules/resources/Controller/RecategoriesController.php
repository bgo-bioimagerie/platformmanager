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
class RecategoriesController extends ResourcesBaseController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->categoryModel = new ReCategory();
        //$this->checkAuthorizationMenu("resources");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Categories($lang), 3);

        $table->addLineEditButton("recategoriesedit/".$idSpace);
        $table->addDeleteButton("recategoriesdelete/".$idSpace);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );

        $categories = $this->categoryModel->getBySpace($idSpace);
        $tableHtml = $table->view($categories, $headers);
        return $this->render(array("data" => ["recategories" => $categories], "id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml));
    }

      /**
     * Edit form
     */
    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // get belonging info
        $data = array("id" => 0, "name" => "", "id_space" => $idSpace);
        if ($id > 0) {
            $data = $this->categoryModel->get($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "recategoriesedit/".$idSpace);
        $form->setTitle(ResourcesTranslator::Edit_Category($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "recategoriesedit/".$idSpace."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Ok($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "recategories/".$idSpace);

        if ($form->check()) {
            // run the database query
            $model = new ReCategory();
            $id_cat = $model->set($form->getParameter("id"), $form->getParameter("name"), $idSpace);

            $_SESSION["flash"] = ResourcesTranslator::Item_created("category", $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                return $this->redirect("recategories/".$idSpace, [], ['recategory' => ['id' => $id_cat]]);
            }
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['recategory' => $data]
            ));
        }
    }

    public function deleteAction($idSpace, $id)
    {
        $lang = $this->getLanguage();
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // check if category is linked to resources. If yes, deletion is not authorized => warn the user
        $resourceModel = new ResourceInfo();
        $linkedResources = $resourceModel->resourcesForCategory($idSpace, $id);

        $error = null;
        if ($linkedResources == null || empty($linkedResources)) {
            // not linked to resources, deletion is authorized
            $this->categoryModel->delete($idSpace, $id);
        } else {
            // linked to resources, notify the user
            $_SESSION['flash'] = ResourcesTranslator::DeletionNotAuthorized(ResourcesTranslator::Category($lang), $lang);
            $error = 'deletionnotauthorized';
        }
        return $this->redirect("recategories/".$idSpace, [], ['error' => $error]);
    }
}
