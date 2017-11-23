<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrClient.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingclientsController extends CoresecureController {
    
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
        $providersArray = $this->clientModel->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("brclientedit/" . $id_space);
        $table->addDeleteButton("brclientdelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang), 
            "pricing_name" => BreedingTranslator::Pricing($lang) 
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

        // get client
        $client = $this->clientModel->get($id);

        // pricings
        $modelPricing = new BrPricing();
        $pricings = $modelPricing->getForList($id_space);
        
        // form
        // build the form
        $form = new Form($this->request, "client/edit");
        $form->setTitle(BreedingTranslator::Edit_Client($lang), 3);
        $form->addHidden("id", $client["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $client["name"]);
        $form->addText("contact_name", BreedingTranslator::ContactName($lang), false, $client["contact_name"]);
        $form->addText("institution", BreedingTranslator::Institution($lang), false, $client["institution"]);
        $form->addText("building_floor", BreedingTranslator::BuildingFloor($lang), false, $client["building_floor"]);
        $form->addText("service", BreedingTranslator::Service($lang), false, $client["service"]);
        $form->addTextArea("address", BreedingTranslator::Address($lang), false, $client["address"]);
        $form->addText("zip_code", BreedingTranslator::Zip_code($lang), false, $client["zip_code"]);
        $form->addText("city", BreedingTranslator::City($lang), false, $client["city"]);
        $form->addText("country", BreedingTranslator::Country($lang), false, $client["country"]);
        $form->addText("phone", BreedingTranslator::Phone($lang), false, $client["phone"]);
        $form->addText("email", BreedingTranslator::Email($lang), false, $client["email"]);
        
        $form->addSelect("pricing", BreedingTranslator::Pricing($lang), $pricings["names"], $pricings["ids"], $client["pricing"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "brclientedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brclients/" . $id_space);

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
            $this->redirect("brclients/" . $id_space);
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
        $this->clientModel->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brclients/" . $id_space);
    }
}
