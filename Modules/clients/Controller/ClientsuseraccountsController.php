<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';

/**
 *
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientsuseraccountsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space, $id_user) {

        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFUllName($id_user);

        $modelClientUser = new ClClientUser();
        $accounts = $modelClientUser->getUserClientAccounts($id_user, $id_space);

        $modelClient = new ClClient();
        $clients = $modelClient->getForList($id_space);

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::addClientAccountFor($lang) . ": " . $userFullName);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"]);
        $form->setValidationButton(CoreTranslator::Add($lang), "clientsuseraccounts/" . $id_space . "/" . $id_user);
        $form->setButtonsWidth(4, 8);

        if ($form->check()) {

            $modelClientUser->set($form->getParameter("id_client"), $id_user);

            $_SESSION["message"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
            $this->redirect("clientsuseraccounts/" . $id_space . "/" . $id_user);
            return;
        }

        $table = new TableView();
        $table->setTitle(ClientsTranslator::ClientAccountsFor($lang) . $userFullName);
        $table->addDeleteButton("clientsuseraccountsdelete/" . $id_space . "/" . $id_user);
        $tableHtml = $table->view($accounts, array(
            "name" => ClientsTranslator::Identifier($lang)
        ));

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "formHtml" => $form->getHtml($lang)
                )
        );
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id_user, $id) {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);

        //echo 'delete client user ' . $id . "<br/>";
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($id, $id_user);

        $this->redirect("clientsuseraccounts/" . $id_space . "/" . $id_user);
    }

}
