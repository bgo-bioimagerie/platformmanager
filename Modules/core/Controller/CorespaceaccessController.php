<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Download.php';
require_once 'Framework/Email.php';

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

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespaceaccessController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }


    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("core", $id_space);
        
        $dataView = [
            'id_space' => $id_space,
            'title' => CoreTranslator::Users($lang),
            'glyphicon' => $menuInfo['icon'] ?? '',
            'bgcolor' => $menuInfo['color'] ?? '#000000',
            'color' => $menuInfo['txtcolor'] ?? '#ffffff',
            'PendingUsers' => CoreTranslator::PendingUsers($lang),
            'Active_Users' => CoreTranslator::Active_Users($lang),
            'Inactive' => CoreTranslator::Inactive($lang),
            'Add' => CoreTranslator::Add_User($lang),


        ];
        return $this->twig->render("Modules/core/View/Corespaceaccess/navbar.twig", $dataView);
    }

    public function impersonateAction($id_space, $id_user) {
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if ($role <= CoreSpace::$MANAGER) {
            throw new PfmAuthException("Error 403: Permission denied, not manager", 403);
        }
        $role = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($role != CoreSpace::$USER) {
            throw new PfmAuthException("Error 403: Permission denied, user not with User role", 403);
        }
 
        $this->request->getSession()->setAttribut("logged_id_user", $_SESSION['id_user']);
        $this->request->getSession()->setAttribut("logged_login", $_SESSION['login']);
        $this->request->getSession()->setAttribut("logged_email", $_SESSION['email']);
        $this->request->getSession()->setAttribut("logged_user_status", $_SESSION['user_status']);
        $this->request->getSession()->setAttribut("logged_id_space", $id_space);

        $modelUser = new CoreUser();
        $user = $modelUser->getInfo($id_user);

        Configuration::getLogger()->debug('[impersonate]', [
            'to_id' => $user['id'], 'to_login' => $user['login'],
            'from_id' => $_SESSION['id_user'], 'from_login' => $_SESSION['login']
        ]);


        $this->request->getSession()->setAttribut("id_user", $user['id']);
        $this->request->getSession()->setAttribut("login", $user['login']);
        $this->request->getSession()->setAttribut("email", $user['email']);
        $this->request->getSession()->setAttribut("user_status", CoreStatus::$USER);

        $this->redirect("coretiles");
    }

    public function unimpersonateAction($id_space) {
        if(!isset($_SESSION['logged_id_user'])) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }

        Configuration::getLogger()->debug('[unimpersonate]', [
            'to_id' => $_SESSION['logged_id_user'], 'to_login' => $_SESSION['logged_login'],
            'from_id' => $_SESSION['id_user'], 'from_login' => $_SESSION['login']
        ]);
 
        $this->request->getSession()->setAttribut("id_user", $_SESSION['logged_id_user']);
        $this->request->getSession()->setAttribut("login", $_SESSION['logged_login']);
        $this->request->getSession()->setAttribut("email", $_SESSION['logged_email']);
        $this->request->getSession()->setAttribut("user_status", $_SESSION['logged_user_status']);

        $this->request->getSession()->unset("logged_id_user");
        $this->request->getSession()->unset("logged_login");
        $this->request->getSession()->unset("logged_email");
        $this->request->getSession()->unset("logged_user_status");
        $this->request->getSession()->unset("logged_id_space");

        $this->redirect("coretiles");


    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $letter = "A", $active = "") {

        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        // check input letter
        if ($letter == "") {
            $letter = isset($_SESSION["user_last_letter"])
                ? $_SESSION["user_last_letter"]
                : "A";
        }

        // check input active
        $_SESSION["user_last_letter"] = $letter;
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        if ($active == "") {
            $active = isset($_SESSION["users_lastvisited"])
                ? $_SESSION["users_lastvisited"]
                : "active";
        }

        // get user list
        
        $usersArray = array();
        $isActive = ($active === "active") ? 1 : 0;
        $modelSpaceUser = new CoreSpaceUser();
        $users = $modelSpaceUser->getUsersOfSpaceByLetter($id_space, $letter, $isActive);
        foreach ($users as $user) {
            $user["date_convention"] = CoreTranslator::dateFromEn($user["date_convention"], $lang);
            $user["date_contract_end"] = CoreTranslator::dateFromEn($user["date_contract_end"], $lang);
            array_push($usersArray, $user);
        }

        // table view
        $table = new TableView();
        $table->addLineButton("coreaccessuseredit/" . $id_space, "id", CoreTranslator::Access($lang));
        $table->addLineButton("corespaceaccess/" . $id_space . "/impersonate" , "id", "Impersonate");


        $modelOptions = new CoreSpaceAccessOptions();
        $options = $modelOptions->getAll($id_space);
        foreach($options as $option){
            try {
                $translatorName = ucfirst($option["module"]).'Translator';
                require_once 'Modules/'.$option["module"].'/Model/'.$translatorName.'.php';
                $toolname = $option["toolname"];
                $table->addLineButton($option["url"]."/" . $id_space, "id", $translatorName::$toolname($lang));
            } catch(Throwable $e) {
                Configuration::getLogger()->error('Option not found', ['option' => $option, 'error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            }
        }

        $tableContent = array(
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "login" => CoreTranslator::Login($lang),
            "email" => CoreTranslator::Email($lang),
            "unit" => CoreTranslator::Unit($lang),
            "organization" => CoreTranslator::Organization($lang),
            "phone" => CoreTranslator::Phone($lang),
            // "spaces" => CoreTranslator::Spaces($lang),
            "date_convention" => CoreTranslator::Convention($lang),
            "date_contract_end" => CoreTranslator::Date_end_contract($lang),
            "convention_url" => array("title" => CoreTranslator::Convention($lang),
                                   "type" => "download",
                                   "text" => CoreTranslator::Download($lang),
                                   "action" => "transfersimplefiledownload"),
            "id" => "ID",
        );

        $tableHtml = $table->view($usersArray, $tableContent);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            'active' => $active,
            'letter' => $letter,
            'space' => $space,
            'data' => ['users' => $users]
        ), "indexAction");
    }

    public function usersAction($id_space, $letter = "") {
        $_SESSION["users_lastvisited"] = "active";
        $this->indexAction($id_space, $letter, "active");
    }

    public function usersinactifAction($id_space, $letter = "") {
        $_SESSION["users_lastvisited"] = "unactive";
        $this->indexAction($id_space, $letter, "unactive");
    }

    public function useraddAction($id_space){
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "createuseraccountform");
        $form->setTitle(CoreTranslator::CreateAccount($lang));

        $form->addText("name", CoreTranslator::Name($lang), true);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true);
        $form->addText("login", CoreTranslator::Login($lang), true, checkUnicity: true);
        $form->addEmail("email", CoreTranslator::email($lang), true, checkUnicity: true);
        $form->addText("phone", CoreTranslator::Phone($lang), false);

        $form->setValidationButton(CoreTranslator::Ok($lang), "corespaceaccessuseradd/".$id_space);

        if ($form->check()) {
            $modelCoreUser = new CoreUser();
            $canEditUser = true;
            if ($modelCoreUser->isLogin($this->request->getParameter('login'))) {
                $canEditUser = false;
                $this->displayFormWarnings("LoginAlreadyExists", $id_space, $lang);
                return;
            }

            if(!$form->getParameter("email") || !$modelCoreUser->isEmailFormat($form->getParameter("email"))) {
                $canEditUser = false;
                $this->displayFormWarnings("EmailInvalid", $id_space, $lang);
                return;
            }

            if($modelCoreUser->isEmail($form->getParameter("email"))) {
                // if email alreday exists, warn user
                $canEditUser = false;
                $this->displayFormWarnings("EmailAlreadyExists", $id_space, $lang);
                return;
            }

            if ($canEditUser) {
                $pwd = $modelCoreUser->generateRandomPassword();

                $id_user = $modelCoreUser->createAccount(
                        $form->getParameter("login"),
                        $pwd,
                        $form->getParameter("name"),
                        $form->getParameter("firstname"),
                        $form->getParameter("email")
                );
                $modelCoreUser->setPhone($id_user, $form->getParameter("phone"));
                $modelCoreUser->validateAccount($id_user);
                $spaceModel = new CoreSpace();
                $spaceName = $spaceModel->getSpaceName($id_space);
                
                $mailParams = [
                    "email" => $form->getParameter("email"),
                    "login" => $form->getParameter("login"),
                    "pwd" => $pwd,
                    "id_space" => $id_space,
                    "space_name" => $spaceName
                ];
                $email = new Email();
                $email->notifyUserByEmail($mailParams, "add_new_user", $lang);

                $modelSpacePending = new CorePendingAccount();
                $modelSpacePending->add($id_user, $id_space);

                $_SESSION["flash"] = CoreTranslator::AccountHasBeenCreated($lang);
                $_SESSION["flashClass"] = "success";

                $modelCoreUser->getInfo($id_user);
                $this->redirect("corespacependingusers/".$id_space, [], []);
                return;
            }
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'space' => $space,
            "formHtml" => $form->getHtml($lang)
        ));
    }

    protected function displayFormWarnings($cause, $id_space, $lang) {
        $_SESSION["flash"] = CoreTranslator::$cause($lang);
        $_SESSION["flashClass"] = "danger";
        $this->redirect("corespaceaccessuseradd/" . $id_space);
    }

    public function usereditAction($id_space, $id){
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFUllName($id);

        $modelUserSpace = new CoreSpaceUser();
        $spaceUserInfo = $modelUserSpace->getUserSpaceInfo2($id_space, $id);

        $roles = $modelSpace->roles($lang);

        $form = new Form($this->request, "coreaccessusereditform");
        $form->setTitle(CoreTranslator::AccessFor($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $spaceUserInfo["status"] ?? "");
        $form->addDate("date_contract_end", CoreTranslator::Date_end_contract($lang), false, CoreTranslator::dateFromEn($spaceUserInfo["date_contract_end"] ?? "", $lang));
        $form->addDate("date_convention", CoreTranslator::Date_convention($lang), false, CoreTranslator::dateFromEn($spaceUserInfo["date_convention"] ?? "", $lang));
        $form->addUpload("convention", CoreTranslator::Convention($lang), $spaceUserInfo["convention_url"] ?? "");

        $form->setValidationButton(CoreTranslator::Save($lang), "coreaccessuseredit/".$id_space."/".$id);
        $form->setDeleteButton(CoreTranslator::Delete($lang), "corespaceuserdelete/".$id_space, $id);
        if ( $form->check() ){

            $modelUserSpace->setRole($id, $id_space, $form->getParameter("role"));
            $modelUserSpace->setDateEndContract($id, $id_space, CoreTranslator::dateToEn($form->getParameter("date_contract_end"), $lang));
            $modelUserSpace->setDateConvention($id, $id_space,  CoreTranslator::dateToEn($form->getParameter("date_convention"), $lang));

            // upload convention
            $target_dir = "data/conventions/";
            if ($_FILES["convention"]["name"] != "") {
                $ext = pathinfo($_FILES["convention"]["name"], PATHINFO_EXTENSION);

                $url = $id_space . "_" . $id . "." . $ext;
                FileUpload::uploadFile($target_dir, "convention", $url);

                $modelUserSpace->setConventionUrl($id, $id_space, $target_dir . $url);
            }

            $_SESSION["message"] = CoreTranslator::UserAccessHasBeenSaved($lang);
            $this->redirect("coreaccessuseredit/".$id_space."/".$id);
            return;
        }

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang),
            "space" => $space
        ));
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

    public function pendingusersAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelSpacePending = new CorePendingAccount();
        $pendingUsers = [];
        $data = $modelSpacePending->getPendingForSpace($id_space);
        for ($i = 0; $i < count($data); $i++) {
            $pendingUsers[] = $data[$i];
            $data[$i]['fullname'] = $data[$i]['name'] . " " . $data[$i]['firstname'];
        }

        $table = new TableView();
        $table->setTitle(CoreTranslator::PendingUserAccounts($lang));
        $table->addLineButton("corespacependinguseredit/".$id_space, "id", CoreTranslator::Activate($lang));
        $table->addLineButton("corespacependinguserdelete/".$id_space, "id", CoreTranslator::Delete($lang));

        $headers = array(
            'fullname' => CoreTranslator::Name($lang),
            'email' => CoreTranslator::Email($lang),
            'unit' => CoreTranslator::Unit($lang),
            'organization' => CoreTranslator::Organization($lang),
            'date_created' => CoreTranslator::DateCreated($lang)
        );
        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            "space" => $space,
            'data' => ["users" => $data]
        ));
    }

    /**
     * Activate a user with a role in space
     * 
     * @param int $id_space id of the space
     * @param int $id id of core_pending_accounts
     */
    public function pendingusereditAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
    
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelPending = new CorePendingAccount();
        $pendingInfo = $modelPending->get($id);
        $userId = $pendingInfo["id_user"];

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFUllName($userId);
        $modelStatus = new CoreSpace();
        $roles = $modelStatus->roles($lang);
        $userRole = $modelStatus->getUserSpaceRole($id_space, $userId);

        $form = new Form($this->request, "pendingusereditactionform");
        $form->setTitle(CoreTranslator::Activate($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $userRole);
        $form->setValidationButton(CoreTranslator::Save($lang), "corespacependinguseredit/".$id_space."/".$id);

        if ( $form->check() ){

            $modelUser->validateAccount($userId);
            $modelSpace->setUserIfNotExist($userId, $id_space, $form->getParameter("role"));
            $modelPending->validate($id, $_SESSION["id_user"]);

            $mailParams = [
                "id_space" => $id_space,
                "id_user" => $userId,
                "space_name" => $space["name"]
            ];

            $email = new Email();
            $email->notifyUserByEmail($mailParams, "accept_pending_user", $this->getLanguage());

            $_SESSION["flash"] = CoreTranslator::UserAccountHasBeenActivated($lang);
            $_SESSION["flashClass"] = "success";
            $this->redirect("corespaceaccess/".$id_space."/All/active", [], ['message' => 'user activated']);
            return;
        }

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang),
            "space" => $space
        ));
    }

    /**
     * 
     * Reject a pending user
     * (sets validate=0 && validated_by=<logged user id> in core_pending_accounts)
     * 
     * @param int $id_space
     * @param int $id pending account id
     */
    public function pendinguserdeleteAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $modelPending = new CorePendingAccount();
        $id_user = $modelPending->get($id)["id_user"];
        $modelPending->invalidate($id, $_SESSION["id_user"]);
        $modelPending->updateWhenUnjoin($id_user, $id_space);
        $modelSpace = new CoreSpace();
        $mailParams = [
            "id_space" => $id_space,
            "id_user" => $id_user,
            "space_name" => $modelSpace->getSpaceName($id_space),
        ];
        $email = new Email();
        $email->notifyUserByEmail($mailParams, "reject_pending_user", $this->getLanguage());
        $this->redirect("corespacependingusers/".$id_space);
    }

}
