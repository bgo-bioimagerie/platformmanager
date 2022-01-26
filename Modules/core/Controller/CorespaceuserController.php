<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Download.php';
require_once 'Framework/Email.php';
require_once 'Framework/Constants.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespaceuserController extends CoresecureController {

    // should inherit that
    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("core", $id_space);
        
        $dataView = [
            'id_space' => $id_space,
            'title' => CoreTranslator::Users($lang),
            'glyphicon' => $menuInfo['icon'] ?? '',
            'bgcolor' => $menuInfo['color'] ?? Constants::COLOR_BLACK,
            'color' => $menuInfo['txtcolor'] ?? Constants::COLOR_WHITE,
            'PendingUsers' => CoreTranslator::PendingUsers($lang),
            'Active_Users' => CoreTranslator::Active_Users($lang),
            'Inactive' => CoreTranslator::Inactive($lang),
            'Add' => CoreTranslator::Add_User($lang),
            'Expire' => CoreTranslator::Expiring($lang)
        ];
        return $this->render($dataView);
    }

    // space access section

    public function editAction($id_space, $id_user) {
        Configuration::getLogger()->debug("[TEST]", ["in usereditAction"]);
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => json_encode($this->request->getParameterNoException("origin"))];
        if ($origin['page'] == "") {
            $origin = false;
        }

        $spaceAccessForm = $this->generateSpaceAccessForm($id_space, $id_user);
        $clientsUserForm = $this->generateClientsUserForm($id_space, $id_user);
        $tableHtml = $this->generateClientsUserTable($id_space, $id_user);
        
        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($id_space, $id_user, $spaceAccessForm);
        }
        if ($clientsUserForm->check()) {
            $this->validateClientsUserform($id_space, $id_user, $clientsUserForm);
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        Configuration::getLogger()->debug("[TEST]", ["before rendering"]);
        // TODO: solve here !!!
        $dataView = [
            'id_space' => $id_space,
            'id_user' => $id_user,
            'lang' => $lang,
            "space" => $space,
            'origin' => json_encode($origin),
            'spaceAccessForm' => $spaceAccessForm->getHtml($lang),
            'clientsUserForm' => $clientsUserForm->getHtml($lang),
            "clientsUserTable" => $tableHtml,
        ];
        return $this->twig->render("Modules/core/View/Corespaceuser/editAction.twig", $dataView);
    }

    protected function generateSpaceAccessForm($id_space, $id_user) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFUllName($id_user);

        $modelUserSpace = new CoreSpaceUser();
        $spaceUserInfo = $modelUserSpace->getUserSpaceInfo2($id_space, $id_user);

        $roles = $modelSpace->roles($lang);

        $form = new Form($this->request, "coreaccessusereditform");
        $form->setTitle(CoreTranslator::AccessFor($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $spaceUserInfo["status"] ?? "");
        $form->addDate("date_contract_end", CoreTranslator::Date_end_contract($lang), false, $spaceUserInfo["date_contract_end"] ?? "");
        $form->addDate("date_convention", CoreTranslator::Date_convention($lang), false, $spaceUserInfo["date_convention"] ?? "");
        $form->addUpload("convention", CoreTranslator::Convention($lang), $spaceUserInfo["convention_url"] ?? "");

        $form->setValidationButton(CoreTranslator::Save($lang), "coreaccessuseredit/".$id_space."/".$id_user);
        $form->setDeleteButton(CoreTranslator::Delete($lang), "corespaceuserdelete/".$id_space, $id_user);
        return $form;
    }

    protected function validateSpaceAccessForm($id_space, $id_user, $form) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUserSpace = new CoreSpaceUser();

        $modelUserSpace->setRole($id_user, $id_space, $form->getParameter("role"));
        $modelUserSpace->setDateEndContract($id_user, $id_space, CoreTranslator::dateToEn($form->getParameter("date_contract_end"), $lang));
        $modelUserSpace->setDateConvention($id_user, $id_space,  CoreTranslator::dateToEn($form->getParameter("date_convention"), $lang));

        // upload convention
        $target_dir = "data/conventions/";
        if ($_FILES["convention"]["name"] != "") {
            $ext = pathinfo($_FILES["convention"]["name"], PATHINFO_EXTENSION);

            $url = $id_space . "_" . $id_user . "." . $ext;
            FileUpload::uploadFile($target_dir, "convention", $url);

            $modelUserSpace->setConventionUrl($id_user, $id_space, $target_dir . $url);
        }

        $_SESSION["message"] = CoreTranslator::UserAccessHasBeenSaved($lang);
        $this->redirect("coreaccessuseredit/".$id_space."/".$id_user, ["origin" => "spaceaccess"]);
    }

    /**
     * 
     * Delete user account from a given space
     * 
     * @param type $id_space
     * @param type $id_user
     */
    public function userdeleteAction($id_space, $id_user) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $lang = $this->getLanguage();
        $spaceUserModel = new CoreSpaceUser();
        $spaceUserModel->delete($id_space, $id_user);
        $_SESSION["message"] = CoreTranslator::UserAccountHasBeenDeleted($lang);

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => "",
            "space" => $space
        ));
    }

    // clientsUserAccounts section

    private $pageUrl = "clientsuseraccounts";
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function clientsUserEditAction($id_space, $id_user) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = $this->generateClientsUserForm($id_space, $id_user);
        if ($form->check()) {
            $this->validateClientsUserform($id_space, $id_user, $form);
        }

        $tableHtml = $this->generateClientsUserTable($id_space, $id_user);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "clientsUserTable" => $tableHtml,
            "clientsUserForm" => $form->getHtml($lang)
        ));
    }

    protected function generateClientsUserForm($id_space, $id_user) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUser = new CoreUser();
        $userFullName = $modelUser->getUserFUllName($id_user);
        $modelClient = new ClClient();
        $clients = $modelClient->getForList($id_space);

        $form = new Form($this->request, "clientsusersform");
        $form->setTitle(ClientsTranslator::addClientAccountFor($lang) . ": " . $userFullName);
        $form->addSelect("id_client", ClientsTranslator::ClientAccount($lang), $clients["names"], $clients["ids"]);
        $form->setValidationButton(CoreTranslator::Add($lang), $this->pageUrl . "/" . $id_space . "/" . $id_user);
        $form->setButtonsWidth(4, 8);
        return $form;
    }

    protected function generateClientsUserTable($id_space, $id_user) {
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

    protected function validateClientsUserForm($id_space, $id_user, $form) {
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelClientUser = new ClClientUser();
        $modelClientUser->set($id_space, $form->getParameter("id_client"), $id_user);
        $_SESSION["flash"] = ClientsTranslator::UserHasBeenAddedToClient($lang);
        $_SESSION["flashClass"] = "success";
        $this->redirect("coreaccessuseredit" ."/" . $id_space . "/" . $id_user, ["origin" => $this->pageUrl]);
    }

    /**
     * Remove a provider
     */
    public function deleteClientsUserAction($id_space, $id_user, $id) {
        // security
        $this->checkAuthorizationMenuSpace("clients", $id_space, $_SESSION["id_user"]);
        $modelClientUser = new ClClientUser();
        $modelClientUser->deleteClientUser($id_space, $id, $id_user);
        $this->redirect($this->pageUrl . "/" . $id_space . "/" . $id_user);
    }

}
