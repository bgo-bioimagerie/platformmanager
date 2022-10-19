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

require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespaceaccessController extends CoresecureController
{
    public function sideMenu()
    {
        $idSpace = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("core", $idSpace);

        $dataView = [
            'id_space' => $idSpace,
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
        return $this->twig->render("Modules/core/View/Corespaceaccess/navbar.twig", $dataView);
    }

    public function impersonateAction($idSpace, $idUser)
    {
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($idSpace, $_SESSION['id_user']);
        if ($role <= CoreSpace::$MANAGER) {
            throw new PfmAuthException("Error 403: Permission denied, not manager", 403);
        }
        $role = $modelSpace->getUserSpaceRole($idSpace, $idUser);
        if ($role != CoreSpace::$USER) {
            throw new PfmAuthException("Error 403: Permission denied, user not with User role", 403);
        }

        $this->request->getSession()->setAttribut("logged_id_user", $_SESSION['id_user']);
        $this->request->getSession()->setAttribut("logged_login", $_SESSION['login']);
        $this->request->getSession()->setAttribut("logged_email", $_SESSION['email']);
        $this->request->getSession()->setAttribut("logged_user_status", $_SESSION['user_status']);
        $this->request->getSession()->setAttribut("logged_id_space", $idSpace);

        $modelUser = new CoreUser();
        $user = $modelUser->getInfo($idUser);

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

    public function unimpersonateAction($idSpace)
    {
        if (!isset($_SESSION['logged_id_user'])) {
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

    public function notifsAction($idSpace)
    {
        try {
            $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        } catch(Exception) {
            // no need to raise an exception for that
            $this->render(['data' => ['notifs' => 0]]);
        }
        $modelSpacePending = new CorePendingAccount();
        $count = $modelSpacePending->countPendingForSpace($idSpace);
        $this->render(['data' => ['notifs' => $count['total']]]);
    }

    public function downloadConventionAction($idSpace, $idUser)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $m = new CoreSpaceUser();
        $user = $m->getUserSpaceInfo2($idSpace, $idUser);
        if (!$user) {
            throw new PfmParamException('user not found', 404);
        }
        $path = $user['convention_url'];
        $file = explode('/', $path);
        if ($path == null || !file_exists($path)) {
            Configuration::getLogger()->warning('file not found', ['file' => $path]);
            throw new PfmFileException('file does not exists', 404);
        }
        $mime = mime_content_type($path);
        header('Content-Description: File Transfer');
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$file[count($file)-1].'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function doexpireAction($idSpace)
    {
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('space admin only access');
        }
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);
        $u = new CoreUser();
        $remove = ($space['on_user_desactivate'] == CoreConfig::$ONEXPIRE_REMOVE);
        $users = $u->disableUsers($space['user_desactivate'], $remove, $idSpace, false);
        $this->redirect("/corespaceaccess/$idSpace/user/expire", [], ['users' => $users]);
    }

    public function expireAction($idSpace)
    {
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('space admin only access');
        }
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $u = new CoreUser();
        $users = $u->disableUsers($space['user_desactivate'], false, $idSpace, true);

        $tableContent = array(
            "id" => "ID",
            "login" => CoreTranslator::Login($lang),
            "fullname" => CoreTranslator::Name($lang),
            "email" => CoreTranslator::Email($lang),
            "date_contract_end" => CoreTranslator::Date_end_contract($lang),
            "date_last_login" => CoreTranslator::Last_connection($lang)
        );

        $table = new TableView();
        $table->addLineButton("corespaceuseredit/" . $idSpace, "id", CoreTranslator::Access($lang));
        $tableHtml = $table->view($users, $tableContent);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml,
            'space' => $space,
            'data' => ['users' => $users]
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace, $letter = "A", $active = "")
    {
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        // check input letter
        if ($letter == "") {
            $letter = isset($_SESSION["user_last_letter"])
                ? $_SESSION["user_last_letter"]
                : "A";
        }

        // check input active
        $_SESSION["user_last_letter"] = $letter;
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        if ($active == "") {
            $active = isset($_SESSION["users_lastvisited"])
                ? $_SESSION["users_lastvisited"]
                : "active";
        }

        $clm = new ClClientUser();
        $clus = $clm->getForSpace($idSpace);
        $cmap = [];
        foreach ($clus as $cl) {
            if (!array_key_exists($cl['id_user'], $cmap)) {
                $cmap[$cl['id_user']] = 0;
            }
            $cmap[$cl['id_user']]++;
        }

        // get user list

        $usersArray = array();
        $isActive = ($active === "active") ? 1 : 0;
        $modelSpaceUser = new CoreSpaceUser();
        $users = $modelSpaceUser->getUsersOfSpaceByLetter($idSpace, $letter, $isActive);
        foreach ($users as $user) {
            $user["date_convention"] = CoreTranslator::dateFromEn($user["date_convention"], $lang);
            $user["date_contract_end"] = CoreTranslator::dateFromEn($user["date_contract_end"], $lang);
            $user["convention_url"] = $user['convention_url'] ? sprintf('/core/spaceaccess/%s/users/%s/convention', $idSpace, $user['id']) : '';
            $user["clients"] = $cmap[$user['id']] ?? 0;
            array_push($usersArray, $user);
        }

        // table view
        $table = new TableView();
        $table->addLineButton("corespaceuseredit/" . $idSpace, "id", CoreTranslator::Access($lang));
        $table->addLineButton("corespaceaccess/" . $idSpace . "/impersonate", "id", "Impersonate");


        /* $modelOptions = new CoreSpaceAccessOptions();
        $options = $modelOptions->getAll($idSpace);
        foreach($options as $option){
            try {
                $translatorName = ucfirst($option["module"]).'Translator';
                require_once 'Modules/'.$option["module"].'/Model/'.$translatorName.'.php';
                $toolname = $option["toolname"];
                $table->addLineButton($option["url"]."/" . $idSpace, "id", $translatorName::$toolname($lang));
            } catch(Throwable $e) {
                Configuration::getLogger()->error('Option not found', ['option' => $option, 'error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            }
        } */

        $tableContent = array(
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "login" => CoreTranslator::Login($lang),
            "email" => CoreTranslator::Email($lang),
            "unit" => CoreTranslator::Unit($lang),
            "organization" => CoreTranslator::Organization($lang),
            "phone" => CoreTranslator::Phone($lang),
            "date_convention" => CoreTranslator::Date_convention($lang),
            "date_contract_end" => CoreTranslator::Date_end_contract($lang),
            "convention_url" => array("title" => CoreTranslator::Convention($lang),
                                   "type" => "download",
                                   "text" => CoreTranslator::Download($lang)
            ),
            "clients" => ClientsTranslator::clients($lang),
            "id" => "ID",
        );

        $tableHtml = $table->view($usersArray, $tableContent);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml,
            'active' => $active,
            'letter' => $letter,
            'space' => $space,
            'data' => ['users' => $users]
        ), "indexAction");
    }

    public function usersAction($idSpace, $letter = "")
    {
        $_SESSION["users_lastvisited"] = "active";
        $this->indexAction($idSpace, $letter, "active");
    }

    public function usersinactifAction($idSpace, $letter = "")
    {
        $_SESSION["users_lastvisited"] = "unactive";
        $this->indexAction($idSpace, $letter, "unactive");
    }


    public function useraddAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "createuseraccountform");
        $form->setTitle(CoreTranslator::CreateAccount($lang));

        $form->addText("name", CoreTranslator::Name($lang), true);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true);
        $form->addText("login", CoreTranslator::Login($lang), true, checkUnicity: true);
        $form->addEmail("email", CoreTranslator::email($lang), true, checkUnicity: true);
        $form->addText("phone", CoreTranslator::Phone($lang), false);

        $form->setValidationButton(CoreTranslator::Ok($lang), "corespaceaccessuseradd/".$idSpace);

        $formjoin = new Form($this->request, "joinuseraccountform");
        $formjoin->setTitle(CoreTranslator::JoinAccount($lang));

        $formjoin->addText("login", CoreTranslator::Login($lang), true);
        $modelSpace = new CoreSpace();
        $roles = $modelSpace->roles($lang);
        $formjoin->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], "");

        $todo = $this->request->getParameterNoException('redirect');
        $formJoinValidationUrl = "corespaceaccessuseradd/".$idSpace;
        if ($todo) {
            $formJoinValidationUrl .= "?redirect=todo";
        }

        $formjoin->setValidationButton(CoreTranslator::Ok($lang), $formJoinValidationUrl);

        if ($formjoin->check()) {
            $modelCoreUser = new CoreUser();
            $user = null;
            try {
                $user = $modelCoreUser->getUserByLogin($this->request->getParameter('login'));
            } catch (PfmAuthException) {
                $this->displayFormWarnings("LoginDoesNotExists", $idSpace, $lang);
                return;
            }

            $pendingModel = new CorePendingAccount();
            if ($pendingModel->isActuallyPending($idSpace, $user['idUser'])) {
                $this->displayFormWarnings("PendingUserAccount", $idSpace, $lang);
                return;
            }

            $modelUserSpace = new CoreSpaceUser();
            $modelUserSpace->setRole($user['idUser'], $idSpace, $form->getParameter("role"));

            $_SESSION["flash"] = CoreTranslator::UserAccountAdded($user['login'], $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                return $this->redirect('corespaceaccessusers/'. $idSpace);
            }
        }

        if ($form->check()) {
            $modelCoreUser = new CoreUser();
            $canEditUser = true;
            if ($modelCoreUser->isLogin($this->request->getParameter('login'))) {
                $canEditUser = false;
                $this->displayFormWarnings("LoginAlreadyExists", $idSpace, $lang);
                return;
            }

            if (!$form->getParameter("email") || !$modelCoreUser->isEmailFormat($form->getParameter("email"))) {
                $canEditUser = false;
                $this->displayFormWarnings("EmailInvalid", $idSpace, $lang);
                return;
            }

            if ($modelCoreUser->isEmail($form->getParameter("email"))) {
                // if email alreday exists, warn user
                $canEditUser = false;
                $this->displayFormWarnings("EmailAlreadyExists", $idSpace, $lang);
                return;
            }

            if ($canEditUser) {
                $pwd = $modelCoreUser->generateRandomPassword();

                $idUser = $modelCoreUser->createAccount(
                    $form->getParameter("login"),
                    $pwd,
                    $form->getParameter("name"),
                    $form->getParameter("firstname"),
                    $form->getParameter("email")
                );
                $modelCoreUser->setPhone($idUser, $form->getParameter("phone"));
                $modelCoreUser->validateAccount($idUser);
                $spaceModel = new CoreSpace();
                $spaceName = $spaceModel->getSpaceName($idSpace);

                $mailParams = [
                    "email" => $form->getParameter("email"),
                    "login" => $form->getParameter("login"),
                    "pwd" => $pwd,
                    "id_space" => $idSpace,
                    "space_name" => $spaceName
                ];
                $email = new Email();
                $email->notifyUserByEmail($mailParams, "add_new_user", $lang);

                $modelSpacePending = new CorePendingAccount();
                $modelSpacePending->add($idUser, $idSpace);

                $_SESSION["flash"] = CoreTranslator::AccountHasBeenCreated($lang);
                $_SESSION["flashClass"] = "success";

                $newUser = $modelCoreUser->getInfo($idUser);
                return $this->redirect("corespacependingusers/".$idSpace, [], ['user' => $newUser]);
            }
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'space' => $space,
            "formHtml" => $form->getHtml($lang),
            "formJoinHtml" => $formjoin->getHtml($lang)
        ));
    }

    protected function displayFormWarnings($cause, $idSpace, $lang)
    {
        $_SESSION["flash"] = CoreTranslator::$cause($lang);
        $_SESSION["flashClass"] = "danger";
        $this->redirect("corespaceaccessuseradd/" . $idSpace);
    }

    /**
     * @deprecated
     */
    public function usereditAction($idSpace, $id)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = $this->generateSpaceAccessForm($idSpace, $id);

        if ($form->check()) {
            $this->validateSpaceAccessForm($idSpace, $id, $form);
        }
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'formHtml' => $form->getHtml($lang),
            "space" => $space
        ));
    }

    public function generateSpaceAccessForm($idSpace, $idUser, $todo=false)
    {
        // $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFullName($idUser);

        $modelUserSpace = new CoreSpaceUser();
        $spaceUserInfo = $modelUserSpace->getUserSpaceInfo2($idSpace, $idUser);

        $roles = $modelSpace->roles($lang);

        $form = new Form($this->request, "coreaccessusereditform");
        $form->setTitle(CoreTranslator::AccessFor($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $spaceUserInfo["status"] ?? "");
        $form->addDate("date_contract_end", CoreTranslator::Date_end_contract($lang), false, $spaceUserInfo["date_contract_end"] ?? "");
        $form->addDate("date_convention", CoreTranslator::Date_convention($lang), false, $spaceUserInfo["date_convention"] ?? "");
        $form->addUpload("convention", CoreTranslator::Convention($lang), $spaceUserInfo["convention_url"] ?? "");

        $validationUrl = "corespaceuseredit/".$idSpace."/".$idUser;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }
        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl);
        $form->setDeleteButton(CoreTranslator::Delete($lang), "corespaceuserdelete/".$idSpace, $idUser);
        return $form;
    }

    public function validateSpaceAccessForm($idSpace, $idUser, $form)
    {
        // $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelUserSpace = new CoreSpaceUser();

        $modelUserSpace->setRole($idUser, $idSpace, $form->getParameter("role"));
        $modelUserSpace->setDateEndContract($idUser, $idSpace, CoreTranslator::dateToEn($form->getParameter("date_contract_end"), $lang));
        $modelUserSpace->setDateConvention($idUser, $idSpace, CoreTranslator::dateToEn($form->getParameter("date_convention"), $lang));

        // upload convention
        $target_dir = "data/conventions/";
        if ($_FILES["convention"]["name"] != "") {
            $ext = pathinfo($_FILES["convention"]["name"], PATHINFO_EXTENSION);

            $url = $idSpace . "_" . $idUser . "." . $ext;
            FileUpload::uploadFile($target_dir, "convention", $url);

            $modelUserSpace->setConventionUrl($idUser, $idSpace, $target_dir . $url);
        }

        $_SESSION["flash"] = CoreTranslator::UserAccessHasBeenSaved($lang);
        $_SESSION["flashClass"] = "success";
    }

    /**
     *
     * Delete user account from a given space
     *
     * @param type $idSpace
     * @param type $idUser
     */
    public function userdeleteAction($idSpace, $idUser)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $spaceUserModel = new CoreSpaceUser();
        $spaceUserModel->delete($idSpace, $idUser);
        $_SESSION['flash'] = CoreTranslator::UserAccountHasBeenDeleted($lang);
        $_SESSION["flashClass"] = 'success';

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);
        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'formHtml' => "",
            "space" => $space
        ));
    }

    public function pendingusersAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $modelSpacePending = new CorePendingAccount();
        $pendingUsers = [];
        $data = $modelSpacePending->getPendingForSpace($idSpace);
        for ($i = 0; $i < count($data); $i++) {
            $pendingUsers[] = $data[$i];
            $data[$i]['fullname'] = $data[$i]['name'] . " " . $data[$i]['firstname'];
        }

        $table = new TableView();
        $table->setTitle(CoreTranslator::PendingUserAccounts($lang));
        $table->addLineButton("corespacependinguseredit/".$idSpace, "id", CoreTranslator::Activate($lang));
        $table->addLineButton("corespacependinguserdelete/".$idSpace, "id", CoreTranslator::Delete($lang));

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
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml,
            "space" => $space,
            'data' => ["users" => $data]
        ));
    }

    /**
     * Activate a user with a role in space
     *
     * @param int $idSpace id of the space
     * @param int $id id of core_pending_accounts
     */
    public function pendingusereditAction($idSpace, $id)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $modelPending = new CorePendingAccount();
        $pendingInfo = $modelPending->get($id);
        $userId = $pendingInfo["id_user"];

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFullName($userId);
        $modelStatus = new CoreSpace();
        $roles = $modelStatus->roles($lang);
        $userRole = $modelStatus->getUserSpaceRole($idSpace, $userId);

        $form = new Form($this->request, "pendingusereditactionform");
        $form->setTitle(CoreTranslator::Activate($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $userRole);
        $form->setValidationButton(CoreTranslator::Save($lang), "corespacependinguseredit/".$idSpace."/".$id);

        if ($form->check()) {
            $modelUser->validateAccount($userId);
            $modelSpace->setUserIfNotExist($userId, $idSpace, $form->getParameter("role"));
            $modelPending->validate($id, $_SESSION["id_user"]);

            $mailParams = [
                "id_space" => $idSpace,
                "id_user" => $userId,
                "space_name" => $space["name"]
            ];

            $email = new Email();
            $email->notifyUserByEmail($mailParams, "accept_pending_user", $this->getLanguage());

            $_SESSION["flash"] = CoreTranslator::UserAccountHasBeenActivated($lang);
            $_SESSION["flashClass"] = "success";
            return $this->redirect("corespaceuseredit/".$idSpace."/" . $userId, ["origin" => "spaceaccess"], ['message' => 'user activated']);
        }

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'formHtml' => $form->getHtml($lang),
            "space" => $space
        ));
    }

    /**
     *
     * Reject a pending user
     * (sets validate=0 && validated_by=<logged user id> in core_pending_accounts)
     *
     * @param int $idSpace
     * @param int $id pending account id
     */
    public function pendinguserdeleteAction($idSpace, $id)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $modelPending = new CorePendingAccount();
        $idUser = $modelPending->get($id)["id_user"];
        $modelPending->invalidate($id, $_SESSION["id_user"]);
        $modelPending->updateWhenUnjoin($idUser, $idSpace);
        $modelSpace = new CoreSpace();
        $mailParams = [
            "id_space" => $idSpace,
            "id_user" => $idUser,
            "space_name" => $modelSpace->getSpaceName($idSpace),
        ];
        $email = new Email();
        $email->notifyUserByEmail($mailParams, "reject_pending_user", $this->getLanguage());
        $this->redirect("corespacependingusers/".$idSpace);
    }
}
