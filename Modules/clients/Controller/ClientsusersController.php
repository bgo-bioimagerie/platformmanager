<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Controller/ClientsController.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientsusersController extends ClientsController {

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
    public function indexAction($id_space, $id_client) {

        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        $modelClient = new ClClient();
        $clientName = $modelClient->getName($id_space, $id_client);

        $modelUsers = new CoreUser();
        $users = $modelUsers->getSpaceActiveUsersForSelect($id_space, "name");

        $modelClientUser = new ClClientUser();

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::UsersForAccount($lang) . ": " . $clientName);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"]);
        $form->setValidationButton(CoreTranslator::Add($lang), "clclientusers/" . $id_space . "/" . $id_client);
        $form->setButtonsWidth(4, 8);
        
        if ($form->check()) {

            $modelClientUser->set($id_space, $id_client, $form->getParameter("id_user"));

            $_SESSION["message"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
            $this->redirect("clclientusers/" . $id_space . "/" . $id_client);
            return;
        }

        $data = $modelClientUser->getUsersInfo($id_space, $id_client);

        $table = new TableView();
        $table->setTitle(ClientsTranslator::ClientUsers($lang));
        $table->addDeleteButton("clclientuserdelete/".$id_space."/" .$id_client);
        $headers = array("name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "tableHtml" => $tableHtml
                )
        );
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id_client, $id_user) {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);

        //echo 'delete client user ' . $id . "<br/>";
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($id_space, $id_client, $id_user);

        $this->redirect("clclientusers/" . $id_space . "/" . $id_client);
    }

}
