<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Model/ClAddress.php';

require_once 'Modules/clients/Form/AddressForm.php';

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
        $this->clientModel = new ClClient ();
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
        $table->addLineButton("clclientusers/" . $id_space, "id", CoreTranslator::Users($lang));
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
        $modelPricing = new ClPricing();
        $pricings = $modelPricing->getForList($id_space);
        
        // preferences
        $preferences = array(
            "ids" => array(1,2),
            "names" => array(ClientsTranslator::Email($lang), ClientsTranslator::Letter($lang))
            );

        // form
        // build the form
        $form = new Form($this->request, "client/edit");
        $form->setTitle(ClientsTranslator::Edit_Client($lang), 3);
        $form->addHidden("id", $client["id"]);
        $form->addText("name", ClientsTranslator::Identifier($lang), true, $client["name"]);
        $form->addText("contact_name", ClientsTranslator::ContactName($lang), false, $client["contact_name"]);
        $form->addText("phone", ClientsTranslator::Phone($lang), false, $client["phone"]);
        $form->addEmail("email", ClientsTranslator::Email($lang), false, $client["email"]);

        $form->addSelect("pricing", ClientsTranslator::Pricing($lang), $pricings["names"], $pricings["ids"], $client["pricing"]);
        $form->addSelect("invoice_send_preference", ClientsTranslator::invoice_send_preference($lang), $preferences["names"], $preferences["ids"], $client["invoice_send_preference"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "clclientedit/" . $id_space . "/" . $id);
        $form->setColumnsWidth(3, 9);
        $form->setButtonsWidth(4, 8);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "clclients/" . $id_space);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $idNew = $this->clientModel->set(
                    $id, $id_space, $form->getParameter("name"), $form->getParameter("contact_name"), 
                    $form->getParameter("phone"), 
                    $form->getParameter("email"), $form->getParameter("pricing"),
                    $form->getParameter("invoice_send_preference")
            );

            $_SESSION["message"] = ClientsTranslator::Data_has_been_saved($lang);

            // after the provider is saved we redirect to the providers list page
            $this->redirect("clclienteditinvoice/" . $id_space . "/" . $idNew);
            return;
        }

        // render the view
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function editInvoiceAddressAction($id_space, $id){
        
                // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // get client
        $client = $this->clientModel->get($id);
        
        $modelAdress = new ClAddress();
        $addressInvoice = $modelAdress->get($client["address_invoice"]);
        
        // Address invoice
        $formAddressInvoice = new AddressForm($this->request, "formAddressInvoice", "clclienteditinvoice/" . $id_space . "/" . $id);
        $formAddressInvoice->setLang($lang);
        $formAddressInvoice->setTitle(ClientsTranslator::AddressInvoice($lang));
        $formAddressInvoice->setSpace($id_space);
        $formAddressInvoice->setData($addressInvoice);
        $formAddressInvoice->render();
        $formi = $formAddressInvoice->getForm();
        if ($formi->check()) {
            $id_adress = $formAddressInvoice->save();
            
            $this->clientModel->setAddressInvoice($id, $id_adress);
            $_SESSION["message"] = ClientsTranslator::Data_has_been_saved($lang);
            $this->redirect("clclienteditdelivery/" . $id_space . "/" . $id);
            return;
        }
        
        // render the view
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $formi->getHtml($lang),
        ));
        
    }
    
    public function editdeliveryaddressAction($id_space, $id){
        
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // get client
        $client = $this->clientModel->get($id);
        
        $modelAdress = new ClAddress();
        $addressDelivery = $modelAdress->get($client["address_delivery"]);
        
        // Address delivery
        $formAddressDelivery = new AddressForm($this->request, "formAddressDelivery", "clclienteditdelivery/" . $id_space . "/" . $id);
        $formAddressDelivery->setLang($lang);
        $formAddressDelivery->setTitle(ClientsTranslator::AddressDelivery($lang));
        $formAddressDelivery->setSpace($id_space);
        $formAddressDelivery->setData($addressDelivery);
        $formAddressDelivery->render();
        $formd = $formAddressDelivery->getForm();
        if ($formd->check()) {
            $id_adress = $formAddressDelivery->save();
            
            $this->clientModel->setAddressDelivery($id, $id_adress);
            $_SESSION["message"] = ClientsTranslator::Data_has_been_saved($lang);
            $this->redirect("clclients/" . $id_space);
            return;
        }
        
        // render the view
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $formd->getHtml($lang),
        ));
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
