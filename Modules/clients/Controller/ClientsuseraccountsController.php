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
class ClientsuseraccountsController extends ClientsController {
    private $pageUrl = "clientsuseraccounts";
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space, $id_user) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = $this->generateForm($id_space, $id_user);
        if ($form->check()) {
            $this->validateForm($id_space, $id_user, $form);
        }

        $tableHtml = $this->generateTableHtml($id_space, $id_user);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "formHtml" => $form->getHtml($lang)
        ));
    }

    protected function generateForm($id_space, $id_user, $validationUrl = false) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFUllName($id_user);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($id_space);

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::addClientAccountFor($lang) . ": " . $userFullName);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"]);
        // TODO: change validation button if external request
        $form->setValidationButton(CoreTranslator::Add($lang), $this->pageUrl . "/" . $id_space . "/" . $id_user);
        $form->setButtonsWidth(4, 8);
        return $form;
    }

    protected function generateTableHtml($id_space, $id_user) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFUllName($id_user);
        $accounts = $modelClientUser->getUserClientAccounts($id_user, $id_space);
        $table = new TableView();
        $table->setTitle(ClientsTranslator::ClientAccountsFor($lang) . $userFullName);
        $table->addDeleteButton($this->pageUrl . "delete/" . $id_space . "/" . $id_user);
        return $table->view($accounts, array(
            "name" => ClientsTranslator::Identifier($lang)
        ));
    }

    protected function validateForm($id_space, $id_user, $form) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        $modelClientUser->set($id_space, $form->getParameter("id_client"), $id_user);
        $_SESSION["flash"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
        $_SESSION["flashClass"] = "success";
        // $this->redirect($this->pageUrl ."/" . $id_space . "/" . $id_user);
        $this->redirect("coreaccessuseredit" ."/" . $id_space . "/" . $id_user, ["origin" => $this->pageUrl]);
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id_user, $id) {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($id_space, $id, $id_user);
        $this->redirect($this->pageUrl . "/" . $id_space . "/" . $id_user);
    }

    public function getClientsUserFormAction($id_space, $id_user) {
        $lang = $this->getLanguage();
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $params = $this->request->params();
        $validationUrl = $params['url'];
        $form = $this->generateForm($id_space, $id_user, $validationUrl);
        $tableHtml = $this->generateTableHtml($id_space, $id_user);

        $this->render(["data" => array(
                "id_space" => $id_space,
                "lang" => $lang,
                "tableHtml" => $tableHtml,
                "formHtml" => $form->getHtml($lang)
            )]
        );
    }

}
