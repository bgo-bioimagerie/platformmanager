<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientslistController extends CoresecureController {
    
    /**
     * User model object
     */
    private $clientModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->clientModel = new BrClient ();
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
        $providersArray = $this->clientModel->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("clclientedit/" . $id_space);
        $table->addDeleteButton("clclientdelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang), 
            "pricing_name" => ClientsTranslator::Pricing($lang) 
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

        // get client
        $client = $this->clientModel->get($id);

        // pricings
        $modelPricing = new BrPricing();
        $pricings = $modelPricing->getForList($id_space);
        
        // form
        // build the form
        $form = new Form($this->request, "client/edit");
        $form->setTitle(ClientsTranslator::Edit_Client($lang), 3);
        $form->addHidden("id", $client["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $client["name"]);
        $form->addText("contact_name", ClientsTranslator::ContactName($lang), false, $client["contact_name"]);
        $form->addText("institution", ClientsTranslator::Institution($lang), false, $client["institution"]);
        $form->addText("building_floor", ClientsTranslator::BuildingFloor($lang), false, $client["building_floor"]);
        $form->addText("service", ClientsTranslator::Service($lang), false, $client["service"]);
        $form->addTextArea("address", ClientsTranslator::Address($lang), false, $client["address"]);
        $form->addText("zip_code", ClientsTranslator::Zip_code($lang), false, $client["zip_code"]);
        $form->addText("city", ClientsTranslator::City($lang), false, $client["city"]);
        $form->addText("country", ClientsTranslator::Country($lang), false, $client["country"]);
        $form->addText("phone", ClientsTranslator::Phone($lang), false, $client["phone"]);
        $form->addText("email", ClientsTranslator::Email($lang), false, $client["email"]);
        
        $form->addSelect("pricing", ClientsTranslator::Pricing($lang), $pricings["names"], $pricings["ids"], $client["pricing"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "clclientedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "clclients/" . $id_space);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->clientModel->set(
                $id, 
                $id_space, 
                $form->getParameter("name"), 
                $form->getParameter("contact_name"), 
                $form->getParameter("institution"), 
                $form->getParameter("building_floor"), 
                $form->getParameter("service"), 
                $form->getParameter("address"), 
                $form->getParameter("zip_code"), 
                $form->getParameter("city"), 
                $form->getParameter("country"), 
                $form->getParameter("phone"), 
                $form->getParameter("email"), 
                $form->getParameter("pricing")
            ); 
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("clclients/" . $id_space);
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
        $this->clientModel->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("clclients/" . $id_space);
    }
}
