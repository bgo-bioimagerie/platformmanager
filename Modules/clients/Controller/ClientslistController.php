<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Model/ClAddress.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Form/AddressForm.php';
require_once 'Modules/clients/Controller/ClientsController.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientslistController extends ClientsController {

    /**
     * User model object
     */
    private $clientModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->clientModel = new ClClient ();

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

        $pageTitle = ClientsTranslator::Clients($lang);

        // render the View
        return $this->render(array(
            'id_space' => $id_space,
            'pageTitle' => $pageTitle,
            'tableHtml' => $tableHtml,
            'data' => ['clients' => $providersArray]
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
        $client = $this->clientModel->get($id_space, $id);

        // get client, invoice and institution forms
        

        $formClient = $this->generateClientInfosForm($id_space, $client);
        $formInvoice = $this->generateInvoiceAddressForm($id_space, $client);
        $formInstitution = $this->generateInstitutionForm($id_space, $client);
        
        // Check if the form has been validated
        if ($formClient->check()) {
            // run the database query
            $idNew = $this->clientModel->set(
                    $id,
                    $id_space,
                    $formClient->getParameter("name"),
                    $formClient->getParameter("contact_name"), 
                    $formClient->getParameter("phone"), 
                    $formClient->getParameter("email"),
                    $formClient->getParameter("pricing"),
                    $formClient->getParameter("invoice_send_preference")
            );

            $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
            $_SESSION["flashClass"] = 'success';

            // after the provider is saved we redirect to the providers list page
            return $this->redirect("clclientedit/" . $id_space . "/" . $idNew, ['origin' => 'client'], ['client' => ['id' => $idNew]]);
        }

        if ($formInvoice->getForm()->check()) {
            $id_adress = $formInvoice->save();
            $this->clientModel->setAddressInvoice($id_space, $id, $id_adress);
            $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
            $_SESSION["flashClass"] = 'success';
            return $this->redirect("clclientedit/" . $id_space . "/" . $id, ['origin' => 'invoice'], ['client' => ['id' => $id]]);
        }

        if ($formInstitution->getForm()->check()) {
            $id_adress = $formInstitution->save();
            $this->clientModel->setAddressDelivery($id_space, $id, $id_adress);
            $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
            $_SESSION['flashClass'] = 'success';
            return $this->redirect("clclientedit/" . $id_space . "/" . $id, ['origin' => 'institution'], ['client' => ['id' => $id]]);
        }

        // get html forms for rendering
        $forms['client'] = $formClient->getHtml($lang);
        $forms['invoice'] = $formInvoice->getHtml($lang);
        $forms['institution'] = $formInstitution->getHtml($lang);
        
        // Set form to display
        $origin = $this->request->getParameterNoException('origin');
        $defaultForm = ($origin && $origin != "") ? $origin : 'client';

        // render the view
        return $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formsHtml' => $forms,
            'data' => [
                'client'  => $client,
                'formsHtml' => $forms,
                'defaultForm' => $defaultForm
            ]
        ));
    }

    protected function generateClientInfosForm($id_space, $client) {
        $lang = $this->getLanguage();

        // pricings
        $modelPricing = new ClPricing();
        $pricings = $modelPricing->getForList($id_space);

        if (empty($pricings['ids'])) {
            $_SESSION['flash'] = ClientsTranslator::Pricing_needed($lang);
            $_SESSION["flashClass"] = 'warning';
        }
        
        // preferences
        $preferences = array(
            "ids" => array(1,2),
            "names" => array(ClientsTranslator::Email($lang), ClientsTranslator::Letter($lang))
        );

        $form = new Form($this->request, "client/edit");
        $form->setTitle(ClientsTranslator::Edit_Client($lang), 3);
        $form->addHidden("id", $client["id"]);
        $form->addText("name", ClientsTranslator::Identifier($lang), true, $client["name"]);
        $form->addText("contact_name", ClientsTranslator::ContactName($lang), false, $client["contact_name"]);
        $form->addText("phone", ClientsTranslator::Phone($lang), false, $client["phone"]);
        $form->addEmail("email", ClientsTranslator::Email($lang), true, $client["email"]);

        $form->addSelectMandatory("pricing", ClientsTranslator::Pricing($lang), $pricings["names"], $pricings["ids"], $client["pricing"]);
        $form->addSelect("invoice_send_preference", ClientsTranslator::invoice_send_preference($lang), $preferences["names"], $preferences["ids"], $client["invoice_send_preference"]);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "clclientedit/" . $id_space . "/" . $client['id']);
        $form->setColumnsWidth(3, 9);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "clclients/" . $id_space);
        return $form;
    }

    protected function generateInvoiceAddressForm($id_space, $client): AddressForm {
        $lang = $this->getLanguage();
        $modelAdress = new ClAddress();
        $addressInvoice = $modelAdress->get($id_space, $client["address_invoice"]);
        
        // Address invoice
        $formAddressInvoice = new AddressForm($this->request, "formAddressInvoice", "clclientedit/" . $id_space . "/" . $client['id']);
        $formAddressInvoice->setLang($lang);
        $formAddressInvoice->setTitle(ClientsTranslator::AddressInvoice($lang));
        $formAddressInvoice->setSpace($id_space);
        $formAddressInvoice->setData($addressInvoice);
        $formAddressInvoice->render();
        return $formAddressInvoice;
    }

    protected function generateInstitutionForm($id_space, $client): AddressForm {
        $lang = $this->getLanguage();
        $modelAdress = new ClAddress();
        $addressDelivery = $modelAdress->get($id_space, $client["address_delivery"]);
        
        // Address delivery
        $formAddressDelivery = new AddressForm($this->request, "formAddressDelivery", "clclientedit/" . $id_space . "/" . $client['id']);
        $formAddressDelivery->setLang($lang);
        $formAddressDelivery->setTitle(ClientsTranslator::AddressDelivery($lang));
        $formAddressDelivery->setSpace($id_space);
        $formAddressDelivery->setData($addressDelivery);
        $formAddressDelivery->render();
        return $formAddressDelivery;
    }

    /**
     * Returns client's address
     */
    public function getAddressAction($id_space, $id_client) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $modelClient = new ClClient();
        $address = $modelClient->getAddressInvoice($id_space, $id_client) ?: "";
        $this->render(['data' => ['elements' => $address]]);
    }
            
    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);

        // remove users from client
        $clu = new ClClientUser();
        $clu->deleteClientUsers($id, $id_space);

        // query to delete the provider
        $this->clientModel->delete($id_space, $id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("clclients/" . $id_space);
    }

}
