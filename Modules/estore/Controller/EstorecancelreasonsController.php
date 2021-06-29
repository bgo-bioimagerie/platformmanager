<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsCancelReason.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstorecancelreasonsController extends CoresecureController {
    
    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new EsCancelReason ();
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
        $table->addLineEditButton("escancelreasonedit/" . $id_space);
        $table->addDeleteButton("escancelreasondelete/" . $id_space);
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

        // default empy provider
        if (!$id) {
            $pricing = array("id" => 0, "name" => "");
        }
        else{
            $pricing = $this->model->get($id);
        }

        // form
        // build the form
        $form = new Form($this->request, "cancelreason/edit");
        $form->setTitle(EstoreTranslator::EditCancelReason($lang), 3);
        $form->addHidden("id", $pricing["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $pricing["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "escancelreasonedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "escancelreasons/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $idNew = $this->model->set($form->getParameter("id"), 
                    $id_space, 
                    $form->getParameter("name")
                );   
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            // after the provider is saved we redirect to the providers list page
            $this->redirect("escancelreasonedit/" . $id_space . "/" . $idNew);
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
        $this->model->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("escancelreasons/" . $id_space);
    }
}
