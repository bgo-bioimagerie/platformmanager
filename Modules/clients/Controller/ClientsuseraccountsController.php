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
class ClientsuseraccountsController extends ClientsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($idSpace, $idUser)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->generateClientsUserForm($idSpace, $idUser);

        if ($form->check()) {
            $this->validateClientsUserform($idSpace, $idUser, $form->getParameter("id_client"));
        }

        $tableHtml = $this->generateClientsUserTable($idSpace, $idUser);

        $this->render(
            array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "formHtml" => $form->getHtml($lang)
                )
        );
    }

    /**
     * Returns a table listing a one user's client accounts
     */
    public function generateClientsUserTable($idSpace, $idUser)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFullName($idUser);
        $accounts = $modelClientUser->getUserClientAccounts($idUser, $idSpace);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($idSpace);

        if (empty($clients['ids'])) {
            $_SESSION['flash'] = ClientsTranslator::Client_needed($lang);
            $_SESSION['flashClass'] = 'warning';
        }
        $table = new TableView("clientsUser");
        $table->setTitle(ClientsTranslator::ClientAccountsFor($lang) . $userFullName);
        $table->addDeleteButton("clientsuseraccounts" . "delete/" . $idSpace . "/" . $idUser);
        return $table->view($accounts, array(
            "name" => ClientsTranslator::Identifier($lang)
        ));
    }

    /**
     * Returns a form in which user is given and we can select clients to link them to
     */
    public function generateClientsUserForm($idSpace, $idUser, $todo=false)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFullName($idUser);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($idSpace);

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::addClientAccountFor($lang) . ": " . $userFullName);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"]);

        $validationUrl = "corespaceuseredit/".$idSpace."/".$idUser;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Add($lang), $validationUrl);

        return $form;
    }

    public function validateClientsUserForm($idSpace, $idUser, $id_client)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        if (!$modelClientUser->exists($idSpace, $id_client, $idUser)) {
            $modelClientUser->set($idSpace, $id_client, $idUser);
            $_SESSION["flash"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
            $_SESSION["flashClass"] = "success";
        } else {
            $_SESSION["flash"] = ClientsTranslator::UserAlreadyLinkedToClient($lang);
            $_SESSION["flashClass"] = "warning";
        }
    }

    /**
     * Remove a provider
     */
    public function deleteAction($idSpace, $idUser, $id_client)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($idSpace, $id_client, $idUser);
        $this->redirect("corespaceuseredit/" . $idSpace . "/" . $idUser, ["origin" => "clientsuseraccounts"]);
    }
}
