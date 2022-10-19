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
class ClientsusersController extends ClientsController
{
    /**
     * User model object
     */
    private $pricingModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->pricingModel = new ClPricing();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($idSpace, $id_client)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        $modelClient = new ClClient();
        $clientName = $modelClient->getName($idSpace, $id_client);

        $modelUsers = new CoreUser();
        $users = $modelUsers->getSpaceActiveUsersForSelect($idSpace, "name");

        $modelClientUser = new ClClientUser();

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::UsersForAccount($lang) . ": " . $clientName);
        $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"]);
        $form->setValidationButton(CoreTranslator::Add($lang), "clclientusers/" . $idSpace . "/" . $id_client);


        if ($form->check()) {
            $modelClientUser->set($idSpace, $id_client, $form->getParameter("id_user"));

            $_SESSION["flash"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
            $_SESSION["flashClass"] = 'success';
            $this->redirect("clclientusers/" . $idSpace . "/" . $id_client);
            return;
        }

        $data = $modelClientUser->getUsersInfo($idSpace, $id_client);

        $table = new TableView();
        $table->setTitle(ClientsTranslator::ClientUsers($lang));
        $table->addDeleteButton("clclientuserdelete/".$idSpace."/" .$id_client);
        $headers = array("name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang)
        );
        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "tableHtml" => $tableHtml,
            "data" => ["clientsusers" => $data]
        ));
    }

    /**
     * Remove a provider
     */
    public function deleteAction($idSpace, $id_client, $idUser)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        //echo 'delete client user ' . $id . "<br/>";
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($idSpace, $id_client, $idUser);
        $_SESSION["flash"] = ClientsTranslator::UserHasBeenDeletedFromClient($lang);
        $_SESSION["flashClass"] = 'success';
        $this->redirect("clclientusers/" . $idSpace . "/" . $id_client);
    }

    public function getUserClientsAction($idSpace, $idUser)
    {
        $this->checkAuthorizationMenuSpace("booking", $idSpace, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $this->render(['data' => ['elements' => $modelClientUser->getUserClientAccounts($idUser, $idSpace)]]);
    }

    public function getClientUsersAction($idSpace, $id_client)
    {
        $this->checkAuthorizationMenuSpace("booking", $idSpace, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $this->render(['data' => ['elements' => $modelClientUser->getClientUsersAccounts($id_client, $idSpace)]]);
    }
}
