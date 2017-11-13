<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrProduct.php';
require_once 'Modules/breeding/Model/BrCategory.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingproductsController extends CoresecureController {
    
    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrProduct ();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("brproductedit/" . $id_space);
        $table->addDeleteButton("brproductdelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang),
            "code" => BreedingTranslator::Code($lang),
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
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        $data = $this->model->get($id);
        
        $modelCategory = new BrCategory();
        $categories = $modelCategory->getForList($id_space);
        
        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::Edit_product($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", BreedingTranslator::Name($lang), true, $data["name"]);
        $form->addText("code", BreedingTranslator::Code($lang), false, $data["code"]);
        $form->addSelect("id_category", BreedingTranslator::Category($lang), $categories["names"], $categories["ids"], $data["id_category"]);
        $form->addTextArea("description", BreedingTranslator::Description($lang), true, $data["description"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "brproductedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brproducts/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->model->set(
                    $form->getParameter("id"), 
                    $id_space, 
                    $form->getParameter("name"),
                    $form->getParameter("code"),
                    $form->getParameter("description"),
                    $form->getParameter("id_category")
                );   
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("brproducts/" . $id_space);
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
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        
        // query to delete the provider
        $this->model->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brproducts/" . $id_space);
    }
}
