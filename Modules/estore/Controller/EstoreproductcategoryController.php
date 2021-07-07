<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsProductCategory.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstoreproductcategoryController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new EsProductCategory();
        $_SESSION["openedNav"] = "estore";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("esproductcategoryedit/" . $id_space);
        $table->addDeleteButton("esproductcategorydelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang)
        ));

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        $data = $this->model->get($id_space ,$id);

        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(EstoreTranslator::Edit_Category($lang), 3);
        $form->addText("name", EstoreTranslator::Name($lang), true, $data["name"]);
        $form->addTextArea("description", EstoreTranslator::Description($lang), false, $data["description"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "esproductcategoryedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "esproductcategories/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $newId = $this->model->set($id, $id_space, 
                    $form->getParameter("name"), 
                    $form->getParameter("description")
            );

            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            // after the provider is saved we redirect to the providers list page
            $this->redirect("esproductcategoryedit/" . $id_space . "/" . $newId);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);

        // query to delete the provider
        $this->model->delete($id_space ,$id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("esproductcategories/" . $id_space);
    }

}
