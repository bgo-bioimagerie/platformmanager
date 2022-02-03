<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Errors.php';
require_once 'Framework/Constants.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/users/Model/UsersInfo.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreTranslator.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoreusersController extends CoresecureController {

    public function mainMenu() {
        if (!str_contains($_SERVER['REQUEST_URI'], "coremyaccount")) {
            return null;
        }
        $lang = $this->getLanguage();
        $dataView = [
            'bgcolor' => Constants::COLOR_WHITE,
            'color' => Constants::COLOR_BLACK,
            'My_Account' => CoreTranslator::My_Account($lang),
            'Informations' => CoreTranslator::Informations($lang),
            'Password' => CoreTranslator::Password($lang),
        ];
        return $this->twig->render("Modules/core/View/Coreusers/navbar.twig", $dataView);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $lang = $this->getLanguage();

        $table = new TableView();
        $table->addLineEditButton("coreusersedit");
        $table->addDeleteButton("coreusersdelete");

        $header = array("login" => CoreTranslator::Login($lang),
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "email" => CoreTranslator::Email($lang),
            "status" => CoreTranslator::Status($lang),
            "source" => CoreTranslator::Source($lang),
            "is_active" => CoreTranslator::Is_user_active($lang),
            "date_last_login" => CoreTranslator::Last_connection($lang),
            "created_at" => CoreTranslator::DateCreated($lang)
        );
        $modelUser = new CoreUser();
        $data = $modelUser->selectAll() ?? [];

        $smap = [ 1 => CoreStatus::$USER, 2 => CoreStatus::$ADMIN];
        $users = [];
        for ($i = 0; $i < count($data); $i++) {
            $users[] = $data[$i];
            $data[$i]["status"] = CoreTranslator::Translate_status($lang, $smap[$data[$i]["status_id"]]);
            if ($data[$i]["is_active"] == 1) {
                $data[$i]["is_active"] = CoreTranslator::yes($lang);
            } else {
                $data[$i]["is_active"] = CoreTranslator::no($lang);
            }

            $data[$i]["date_last_login"] = CoreTranslator::dateFromEn($data[$i]["date_last_login"], $lang);
            unset($data[$i]['password']);
            unset($data[$i]['apikey']);

        }

        $tableHtml = $table->view($data, $header);
        return $this->render(array("tableHtml" => $tableHtml, "lang" => $lang, "data" => ["users" => $users]));
    }

    public function editAction($id) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $modelUser = new CoreUser();
        $modelUsersInfo = new UsersInfo();
        if ($id > 0) {
            $user = $modelUser->getUser($id);
            $userInfo = $modelUsersInfo->get($id);
        } else {
            $user = $modelUser->getEmpty();
            $userInfo = [];
        }

        $lang = $this->getLanguage();
        $form = new Form($this->request, "editForm");
        if ($id > 0) {
            $form->setTitle(CoreTranslator::Edit_User($lang));
        } else {
            $form->setTitle(CoreTranslator::Add_User($lang));
        }
        $form->addHidden("id", $user["id"]);
        $isLoginLocked = (!$id) ? false : true;
        $form->addText("login", CoreTranslator::Login($lang), !$isLoginLocked, $user["login"], readonly: $isLoginLocked, checkUnicity: !$isLoginLocked, suggestLogin: !$isLoginLocked);
        if (!$id) {
            $form->addPassword("pwd", CoreTranslator::Password($lang));
            $form->addPassword("pwdconfirm", CoreTranslator::Password($lang));
        }
        $form->addText("name", CoreTranslator::Name($lang), true, $user["name"]);
        $form->addText("firstname", CoreTranslator::Firstname($lang), true, $user["firstname"]);
        $form->addEmail("email", CoreTranslator::Email($lang), true, $user["email"], true);
        $form->addText("unit", CoreTranslator::Unit($lang), false, $userInfo["unit"] ?? "", true);
        $form->addText("organization", CoreTranslator::Organization($lang), false, $userInfo["organization"] ?? "", true);

        $modelStatus = new CoreStatus();
        $status = $modelStatus->allStatusInfo();
        $statusNames = array();
        $statusId = array();
        foreach ($status as $statu) {
            $statusNames[] = CoreTranslator::Translate_status($lang, $statu["name"]);
            $statusId[] = $statu["id"];
        }
        $form->addSelect("status_id", CoreTranslator::Status($lang), $statusNames, $statusId, $user["status_id"]);
        $form->addDate("date_end_contract", CoreTranslator::Date_end_contract($lang), false, $user["date_end_contract"]);
        $form->addSelect("is_active", CoreTranslator::Is_user_active($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $user["is_active"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "coreusersedit/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "coreusers");
        $form->setButtonsWidth(3, 8);


        if ($id > 0) {
            $formPwd = new Form($this->request, "coreuseretidpwd");
            $formPwd->addHidden("id", $user["id"]);
            $formPwd->setTitle(CoreTranslator::Change_password($lang));
            $formPwd->addPassword("pwd", CoreTranslator::New_password($lang));
            $formPwd->addPassword("pwdconfirm", CoreTranslator::New_password($lang));
            $formPwd->setValidationButton(CoreTranslator::Save($lang), "coreusersedit/" . $id);
            $formPwd->setButtonsWidth(3, 8);
        }
        $script = "";
        if ($form->check()) {
            $canEditUser = true;

            if(!$form->getParameter("email") || !$modelUser->isEmailFormat($form->getParameter("email"))) {
                $canEditUser = false;
                $this->displayFormWarnings("EmailInvalid", $id, $lang);
                return;
            }
            if (!$id) {
                // creating a new user
                if ($modelUser->isLogin($this->request->getParameter('login'))) {
                    $canEditUser = false;
                    $this->displayFormWarnings("LoginAlreadyExists", $id, $lang);
                    return;
                }
                if($modelUser->isEmail($form->getParameter("email"))) {
                    // if email already exists, warn user
                    $canEditUser = false;
                    $this->displayFormWarnings("EmailAlreadyExists", $id, $lang);
                    return;
                }
            } else {
                // updating an existing user
                if ($modelUser->isEmail($form->getParameter("email")) && ($form->getParameter("email") != $user["email"])) {
                    // if email, excepting user's one, already exists, warn user
                    $canEditUser = false;
                    $this->displayFormWarnings("EmailAlreadyExists", $id, $lang);
                    return;
                }
            }

            if ($canEditUser) {
                $_SESSION["flash"] = (!$id)
                    ? CoreTranslator::AccountHasBeenCreated($lang)
                    : CoreTranslator::AccountHasBeenModified($lang);
                $_SESSION["flashClass"] = "success";
                $id_user = $this->editQuery($form, $lang);
                $user = $modelUser->getInfo($id_user);
                return $this->redirect("coreusers", [], ['user' => $user]);
            }
        }
        
        if ($id > 0 && $formPwd->check()) {
            $this->editPwdQuery($form, $lang);
            $this->redirect("coreusers");
            return;
        }

        $formPwdHtml = "";
        if ($id > 0) {
            $formPwdHtml = $formPwd->getHtml($lang);
        }

        return $this->render(array(
            "formHtml" => $form->getHtml($lang),
            "formPwdHtml" => $formPwdHtml,
            "script" => $script,
            "data" => ['user' => $user]
        ));
    }

    protected function displayFormWarnings($cause, $id, $lang) {
        $_SESSION["flash"] = CoreTranslator::$cause($lang);
        $_SESSION["flashClass"] = "danger";
        $this->redirect("coreusersedit/" . $id ?? 0);
    }

    protected function editPwdQuery($formPwd, $lang) {
        $modelUser = new CoreUser();
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $pwd = $formPwd->getParameter("pwd");
        $pwdconfirm = $formPwd->getParameter("pwdconfirm");
        if ($pwd != $pwdconfirm) {
            $_SESSION['flash'] = CoreTranslator::TheTwoPasswordAreDifferent($lang);
            $_SESSION["flashClass"] = "danger";
        } else {
            $modelUser->changePwd($formPwd->getParameter("id"), $pwd);
            $_SESSION['flash'] = CoreTranslator::PasswordHasBeenChanged($lang);
            $_SESSION["flashClass"] = "success";
        }
    }

    protected function editQuery($form, $lang) {
        $modelUser = new CoreUser();
        $modelUsersInfo = new UsersInfo();
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $id = $form->getParameter("id");
        if (!$id) {
            $pwd = $form->getParameter("pwd");
            $pwdconfirm = $form->getParameter("pwdconfirm");
            if ($pwd != $pwdconfirm) {
                $_SESSION['flash'] = CoreTranslator::TheTwoPasswordAreDifferent($lang);
                $_SESSION["flashClass"] = "danger";
                $this->redirect("coreusers");
            } else {
                $id = $modelUser->add(
                    $form->getParameter("login"),
                    $form->getParameter("pwd"),
                    $form->getParameter("name"),
                    $form->getParameter("firstname"),
                    $form->getParameter("email"),
                    $form->getParameter("status_id"),
                    $form->getParameter("date_end_contract"),
                    $form->getParameter("is_active")
                );
                $modelUsersInfo->set(
                    $id,
                    "",
                    $form->getParameter("unit"), 
                    $form->getParameter("organization")
                );
            }
        } else {
            $modelUser->edit(
                $id,
                $form->getParameter("name"),
                $form->getParameter("firstname"),
                $form->getParameter("email"),
                $form->getParameter("status_id"),
                $form->getParameter("date_end_contract"),
                $form->getParameter("is_active")
            );
            $modelUsersInfo->set(
                $id,
                "",
                $form->getParameter("unit"), 
                $form->getParameter("organization")
            );
        }
        return $id;
    }

    public function deleteAction($id) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        if (!$this->isLinkedToAnySpace($id)) {
            $modelPending = new CorePendingAccount();
            $modelPending->deleteByUser($id);
            $modelUser = new CoreUser();
            $modelUser->delete($id);
        } else {
            $_SESSION['flash'] = CoreTranslator::UserIsMemberOfSpace($this->getLanguage());
        }
        $this->redirect("coreusers");
    }

    /**
     * 
     * Returns true if user is pending or active in any space
     * 
     * @param int $id_user
     * 
     * @return bool
     */
    public function isLinkedToAnySpace($idUser) {
        $coreSpaceModel = new CoreSpaceUser();
        $corePendingModel = new CorePendingAccount();
        return (
            $coreSpaceModel->getUserSpaceInfo($idUser) ||
            $corePendingModel->isActuallyPendingInAnySpace($idUser)
        );
    }

    public function myaccountAction() {
        $lang = $this->getLanguage();
        $id = $_SESSION["id_user"];
        $modelUser = new CoreUser();
        $modelUser->getUser($id);

        $formPwd = new Form($this->request, "coremyaccount");
        $formPwd->addHidden("id", $id);
        $formPwd->setTitle(CoreTranslator::Change_password($lang));
        $formPwd->addPassword("curentpwd", CoreTranslator::Curent_password($lang));
        $formPwd->addPassword("pwd", CoreTranslator::New_password($lang));
        $formPwd->addPassword("confirm", CoreTranslator::Confirm($lang));
        $formPwd->setValidationButton(CoreTranslator::Save($lang), "coremyaccount");
        $formPwd->setButtonsWidth(3, 8);

        if ($formPwd->check()) {

            $this->myaccountquery($modelUser, $formPwd, $id, $lang);
            $this->redirect("coretiles");
            return;
        }

        $this->render(array(
            "lang" => $lang,
            "formHtml" => $formPwd->getHtml($lang)
        ));
    }

    protected function myaccountquery($modelUser, $formPwd, $id, $lang) {
        $previouspwddb = $modelUser->getpwd($id);
        $previouspwd = $formPwd->getParameter("curentpwd");

        if ($previouspwddb['pwd'] == md5($previouspwd)) {

            $pwd = $formPwd->getParameter("pwd");
            $pwdc = $formPwd->getParameter("confirm");
            if ($pwd == $pwdc) {
                $modelUser->changePwd($id, $pwd);
            } else {
                throw new PfmAuthException(CoreTranslator::TheTwoPasswordAreDifferent($lang), 403);
            }
        } else {
            throw new PfmAuthException(CoreTranslator::The_curent_password_is_not_correct($lang), 403);
        }
    }

    /**
     * 
     * Generates form for users to choose their default language
     * 
     * @return view default language editing screen
     * 
     */
    public function languageeditAction() {
        // language form
        $id_user = $_SESSION["id_user"];
        $userSettingsModel = new CoreUserSettings();

        $lang = $this->getLanguage();
        $choicesview = array(CoreTranslator::English($lang), CoreTranslator::French($lang));
        $choicesidview = array("en", "fr");

        $form = new Form($this->request, "languageForm");
        $form->setTitle(CoreTranslator::Default_language($lang));  
        $form->addSelect(
            "language",
            CoreTranslator::Default_language($lang),
            $choicesview,
            $choicesidview,
            $lang
        );
        $form->setButtonsWidth(4, 8);
        $form->setValidationButton(CoreTranslator::Ok($lang), "coreuserslanguageedit");
        $form->setCancelButton(CoreTranslator::Cancel($lang), "coresettings");

        if ($form->check()){
            $lang = $this->request->getParameter("language");
            $userSettingsModel->setSettings($id_user, "language", $lang);
            $userSettingsModel->updateSessionSettingVariable();
            $this->redirect("coresettings");
        }

        return $this->render(array(
            'lang' => $lang,
            'form' => $form->getHtml($lang)
        ));
    }

}
