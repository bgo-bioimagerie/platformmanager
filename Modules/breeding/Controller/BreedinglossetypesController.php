<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrLosseType.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedinglossetypesController extends CoresecureController {
    
    /**
     * model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrLosseType ();
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
        //$table->setTitle(BreedingTranslator::lossetypes($lang), 3);
        $table->addLineEditButton("brlossetypeedit/" . $id_space);
        $table->addDeleteButton("brlossetypedelete/" . $id_space);
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
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        if (!$id) {
            $lossetype = array("id" => 0, "name" => "");
        }
        else{
            $lossetype = $this->model->get($id);
        }

        // form
        // build the form
        $form = new Form($this->request, "lossetype/edit");
        $form->setTitle(BreedingTranslator::Edit_lossetype($lang), 3);
        $form->addHidden("id", $lossetype["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $lossetype["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "brlossetypeedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brlossetypes/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), 
                    $id_space, 
                    $form->getParameter("name")
                );   
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("brlossetypes/" . $id_space);
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
        $this->redirect("brlossetypes/" . $id_space);
    }
}
