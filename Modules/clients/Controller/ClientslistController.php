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
class ClientslistController extends ClientsController
{
    /**
     * User model object
     */
    private $clientModel;
    private $clientEditUrl;
    private $clientsListUrl;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->clientModel = new ClClient();
        $this->clientEditUrl = "clclientedit/";
        $this->clientsListUrl = "clclients/";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($idSpace)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->clientModel->getAll($idSpace);

        $table = new TableView();

        $table->addLineEditButton($this->clientEditUrl . $idSpace);
        $table->addLineButton("clclientusers/" . $idSpace, "id", CoreTranslator::Users($lang));
        $table->addDeleteButton("clclientdelete/" . $idSpace);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang),
            "pricing_name" => ClientsTranslator::Pricing($lang)
        ));

        $pageTitle = ClientsTranslator::Clients($lang);

        // render the View
        return $this->render(array(
            'id_space' => $idSpace,
            'pageTitle' => $pageTitle,
            'tableHtml' => $tableHtml,
            'data' => ['clients' => $providersArray]
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($idSpace, $id)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();
        $client = $this->clientModel->get($idSpace, $id);

        // get client, invoice and institution forms
        $formClient = $this->generateClientInfosForm($idSpace, $client);
        $formInvoice = $this->generateInvoiceAddressForm($idSpace, $client);
        $formInstitution = $this->generateInstitutionForm($idSpace, $client);

        // Check if a form has been validated
        if ($formClient->check()) {
            return $this->validateClientForm($idSpace, $id, $formClient);
        }
        if ($formInvoice->getForm()->check()) {
            return $this->validateClientInvoiceForm($idSpace, $id, $formInvoice);
        }
        if ($formInstitution->getForm()->check()) {
            return $this->validateClientInstitutionForm($idSpace, $id, $formInstitution);
        }

        // get html forms for rendering
        $forms['client'] = $formClient->getHtml($lang);
        $forms['invoice'] = $formInvoice->getHtml($lang);
        $forms['institution'] = $formInstitution->getHtml($lang);

        // Set form to display
        $origin = $this->request->getParameterNoException('origin');
        $defaultForm = ($origin && $origin != "") ? $origin : 'client';
        $accessAddressForms = $id > 0;

        // render the view
        return $this->render(array(
            'id_space' => $idSpace,
            'lang' => $lang,
            'formsHtml' => $forms,
            'data' => [
                'client'  => $client,
                'formsHtml' => $forms,
                'defaultForm' => $defaultForm,
                'accessAddressForms' => $accessAddressForms
            ]
        ));
    }

    protected function validateClientForm($idSpace, $id, $formClient)
    {
        $lang = $this->getLanguage();
        // run the database query
        $idNew = $this->clientModel->set(
            $id,
            $idSpace,
            $formClient->getParameter("name"),
            $formClient->getParameter("contact_name"),
            $formClient->getParameter("phone"),
            $formClient->getParameter("email"),
            $formClient->getParameter("pricing"),
            $formClient->getParameter("invoice_send_preference")
        );
        $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
        $_SESSION["flashClass"] = 'success';
        return $this->redirect($this->clientEditUrl . $idSpace . "/" . $idNew, ['origin' => 'client'], ['client' => ['id' => $idNew]]);
    }

    protected function validateClientInvoiceForm($idSpace, $id, $formClientInvoice)
    {
        $lang = $this->getLanguage();
        $id_adress = $formClientInvoice->save();
        $this->clientModel->setAddressInvoice($idSpace, $id, $id_adress);
        $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
        $_SESSION["flashClass"] = 'success';
        return $this->redirect($this->clientEditUrl . $idSpace . "/" . $id, ['origin' => 'invoice'], ['client' => ['id' => $id]]);
    }

    protected function validateClientInstitutionForm($idSpace, $id, $formClientInstitution)
    {
        $lang = $this->getlanguage();
        $id_adress = $formClientInstitution->save();
        $this->clientModel->setAddressDelivery($idSpace, $id, $id_adress);
        $_SESSION['flash'] = ClientsTranslator::Data_has_been_saved($lang);
        $_SESSION['flashClass'] = 'success';
        return $this->redirect($this->clientEditUrl . $idSpace . "/" . $id, ['origin' => 'institution'], ['client' => ['id' => $id]]);
    }

    protected function generateClientInfosForm($idSpace, $client)
    {
        $lang = $this->getLanguage();

        // pricings
        $modelPricing = new ClPricing();
        $pricings = $modelPricing->getForList($idSpace);

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

        $form->setValidationButton(CoreTranslator::Save($lang), $this->clientEditUrl . $idSpace . "/" . $client['id']);
        $form->setColumnsWidth(3, 9);
        $form->setCancelButton(CoreTranslator::Cancel($lang), $this->clientsListUrl . $idSpace);
        return $form;
    }

    protected function generateInvoiceAddressForm($idSpace, $client): AddressForm
    {
        $lang = $this->getLanguage();
        $modelAdress = new ClAddress();
        $addressInvoice = $modelAdress->get($idSpace, $client["address_invoice"]);

        // Address invoice
        $formAddressInvoice = new AddressForm($this->request, "formAddressInvoice", $this->clientEditUrl . $idSpace . "/" . $client['id'], $this->clientsListUrl . $idSpace);
        $formAddressInvoice->setLang($lang);
        $formAddressInvoice->setTitle(ClientsTranslator::AddressInvoice($lang));
        $formAddressInvoice->setSpace($idSpace);
        $formAddressInvoice->setData($addressInvoice);
        $formAddressInvoice->render();
        return $formAddressInvoice;
    }

    protected function generateInstitutionForm($idSpace, $client): AddressForm
    {
        $lang = $this->getLanguage();
        $modelAdress = new ClAddress();
        $addressDelivery = $modelAdress->get($idSpace, $client["address_delivery"]);

        // Address delivery
        $formAddressDelivery = new AddressForm($this->request, "formAddressDelivery", $this->clientEditUrl . $idSpace . "/" . $client['id'], $this->clientsListUrl . $idSpace);
        $formAddressDelivery->setLang($lang);
        $formAddressDelivery->setTitle(ClientsTranslator::AddressDelivery($lang));
        $formAddressDelivery->setSpace($idSpace);
        $formAddressDelivery->setData($addressDelivery);
        $formAddressDelivery->render();
        return $formAddressDelivery;
    }

    /**
     * Returns client's address
     */
    public function getAddressAction($idSpace, $id_client)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $modelClient = new ClClient();
        $address = $modelClient->getAddressInvoice($idSpace, $id_client) ?: "";
        $this->render(['data' => ['elements' => $address]]);
    }

    /**
     * Remove a provider
     */
    public function deleteAction($idSpace, $id)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);

        // remove users from client
        $clu = new ClClientUser();
        $clu->deleteClientUsers($id, $idSpace);

        // query to delete the provider
        $this->clientModel->delete($idSpace, $id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect($this->clientsListUrl . $idSpace);
    }
}
