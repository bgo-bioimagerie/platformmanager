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
    public function indexAction($id_space, $id_user)
    {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->generateClientsUserForm($id_space, $id_user);

        if ($form->check()) {
            $this->validateClientsUserform($id_space, $id_user, $form->getParameter("id_client"));
        }

        $tableHtml = $this->generateClientsUserTable($id_space, $id_user);

        $this->render(
            array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "formHtml" => $form->getHtml($lang)
                )
        );
    }

    /**
     * Returns a table listing a one user's client accounts
     */
    public function generateClientsUserTable($id_space, $id_user)
    {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFullName($id_user);
        $accounts = $modelClientUser->getUserClientAccounts($id_user, $id_space);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($id_space);

        if (empty($clients['ids'])) {
            $_SESSION['flash'] = ClientsTranslator::Client_needed($lang);
            $_SESSION['flashClass'] = 'warning';
        }
        $table = new TableView("clientsUser");
        $table->setTitle(ClientsTranslator::ClientAccountsFor($lang) . $userFullName);
        $table->addDeleteButton("clientsuseraccounts" . "delete/" . $id_space . "/" . $id_user);
        return $table->view($accounts, array(
            "name" => ClientsTranslator::Identifier($lang)
        ));
    }

    /**
     * Returns a form in which user is given and we can select clients to link them to
     */
    public function generateClientsUserForm($id_space, $id_user, $todo=false)
    {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFullName($id_user);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($id_space);

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::addClientAccountFor($lang) . ": " . $userFullName);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"]);

        $validationUrl = "corespaceuseredit/".$id_space."/".$id_user;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Add($lang), $validationUrl);

        return $form;
    }

    public function validateClientsUserForm($id_space, $id_user, $id_client)
    {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        if (!$modelClientUser->exists($id_space, $id_client, $id_user)) {
            $modelClientUser->set($id_space, $id_client, $id_user);
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
    public function deleteAction($id_space, $id_user, $id_client)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($id_space, $id_client, $id_user);
        $this->redirect("corespaceuseredit/" . $id_space . "/" . $id_user, ["origin" => "clientsuseraccounts"]);
    }
}
