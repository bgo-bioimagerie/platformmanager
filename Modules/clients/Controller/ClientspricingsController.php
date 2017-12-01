<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClPricing.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientspricingsController extends CoresecureController {
    
    /**
     * User model object
     */
    private $pricingModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->pricingModel = new ClPricing ();
        $_SESSION["openedNav"] = "clients";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->pricingModel->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("clpricingedit/" . $id_space);
        $table->addDeleteButton("clpricingdelete/" . $id_space);
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
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        if ($id == 0) {
            $pricing = array("id" => 0, "name" => "");
        }
        else{
            $pricing = $this->pricingModel->get($id);
        }

        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(ClientsTranslator::Edit_Pricing($lang), 3);
        $form->addHidden("id", $pricing["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $pricing["name"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "clpricingedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "clpricings/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $newId = $this->pricingModel->set($form->getParameter("id"), 
                    $id_space, 
                    $form->getParameter("name")
                );   
            
            $_SESSION["message"] = ClientsTranslator::Data_has_been_saved($lang);
            // after the provider is saved we redirect to the providers list page
            $this->redirect("clpricingedit/" . $id_space . "/" . $newId);
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
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        
        // query to delete the provider
        $this->pricingModel->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("clpricings/" . $id_space);
    }
}
