<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/template/Model/TemplateTranslator.php';
require_once 'Modules/template/Model/Provider.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of template module
 */
class ProvidersController extends CoresecureController {
    
    /**
     * User model object
     */
    private $providerModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->providerModel = new Provider ();
        $_SESSION["openedNav"] = "template";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("template", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->providerModel->getAll($id_space);

        $table = new TableView();
        $table->setTitle(TemplateTranslator::Providers($lang), 3);
        $table->addLineEditButton("provideredit/" . $id_space);
        $table->addDeleteButton("providerdelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array("name" => CoreTranslator::Name($lang), "address" => CoreTranslator::Address($lang), "phone" => CoreTranslator::Phone($lang)));

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
        $this->checkAuthorizationMenuSpace("template", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        if ($id == 0) {
            $provider = array("id" => 0, "name" => "", "address" => "", "phone" => "");
        }
        else{
            $provider = $this->providerModel->get($id);
        }

        // form
        // build the form
        $form = new Form($this->request, "provider/edit");
        $form->setTitle(TemplateTranslator::Edit_Provider($lang), 3);
        $form->addHidden("id", $provider["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $provider["name"]);
        $form->addTextArea("address", CoreTranslator::Address($lang), false, $provider["address"]);
        $form->addText("phone", CoreTranslator::Phone($lang), false, $provider["phone"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "provideredit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "providers/" . $id_space);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->providerModel->set($form->getParameter("id"), 
                    $id_space, 
                    $form->getParameter("name"), 
                    $form->getParameter("address"),
                    $form->getParameter("phone"));   
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("providers/" . $id_space);
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
        $this->checkAuthorizationMenuSpace("template", $id_space, $_SESSION["id_user"]);
        
        // query to delete the provider
        $this->providerModel->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("providers/" . $id_space);
    }
}
