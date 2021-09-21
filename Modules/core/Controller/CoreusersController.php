<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Errors.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoreusersController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        // $this->checkAuthorization(CoreStatus::$ADMIN);
        //$this->checkAuthorizationMenu("users");
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
            "date_last_login" => CoreTranslator::Last_connection($lang));
        $modelUser = new CoreUser();
        $data = $modelUser->selectAll();
        $modelStatus = new CoreStatus();
        $users = [];
        for ($i = 0; $i < count($data); $i++) {
            $users[] = $data[$i];
            $data[$i]["status"] = CoreTranslator::Translate_status($lang, $modelStatus->getStatusName($data[$i]["status_id"]));
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
        if ($id > 0) {
            $user = $modelUser->getUser($id);
        } else {
            $user = $modelUser->getEmpty();
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
        $form->addText("login", CoreTranslator::Login($lang), !$isLoginLocked, $user["login"], readonly: $isLoginLocked, checkUnicity: !$isLoginLocked);
        if (!$id) {
            $form->addPassword("pwd", CoreTranslator::Password($lang));
            $form->addPassword("pwdconfirm", CoreTranslator::Password($lang));
        }
        $form->addText("name", CoreTranslator::Name($lang), false, $user["name"]);
        $form->addText("firstname", CoreTranslator::Firstname($lang), false, $user["firstname"]);
        $form->addEmail("email", CoreTranslator::Email($lang), false, $user["email"], true);

        $modelStatus = new CoreStatus();
        $status = $modelStatus->allStatusInfo();
        $statusNames = array();
        $statusId = array();
        foreach ($status as $statu) {
            $statusNames[] = CoreTranslator::Translate_status($lang, $statu["name"]);
            $statusId[] = $statu["id"];
        }
        $form->addSelect("status_id", CoreTranslator::Status($lang), $statusNames, $statusId, $user["status_id"]);
        $form->addDate("date_end_contract", CoreTranslator::Date_end_contract($lang), false, CoreTranslator::dateFromEn($user["date_end_contract"], $lang));
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
        $checked = false;
        if ($form->check()) {
            $canEditUser = true;
            if (!$id) {
                if ($modelUser->isLogin($this->request->getParameter('login'))) {
                    $canEditUser = false;
                    $_SESSION["flash"] = CoreTranslator::LoginAlreadyExists($lang);
                    $_SESSION["flashClass"] = "danger";
                }
                if(!$modelUser->isEmailFormat($form->getParameter("email"))) {
                    // if email alreadyy exists, warn user
                    $canEditUser = false;
                    $_SESSION["flash"] = CoreTranslator::EmailInvalid($lang);
                    $_SESSION["flashClass"] = "danger";
                }
                if($modelUser->isEmail($form->getParameter("email"))) {
                    // if email already exists, warn user
                    $canEditUser = false;
                    $_SESSION["flash"] = CoreTranslator::EmailAlreadyExists($lang);
                    $_SESSION["flashClass"] = "danger";
                }
            }

            if ($modelUser->isEmail($form->getParameter("email")) && (($form->getParameter("email") != $user["email"]) || false )) {
                // if email, excepting user's one, already exists, warn user
                $canEditUser = false;
                $_SESSION["flash"] = CoreTranslator::EmailAlreadyExists($lang);
                $_SESSION["flashClass"] = "danger";
            }

            if ($canEditUser) {
                $_SESSION["flash"] = (!$id)
                    ? CoreTranslator::AccountHasBeenCreated($lang)
                    : CoreTranslator::AccountHasBeenModified($lang);
                $_SESSION["flashClass"] = "success";
                $id_user = $this->editQuery($form, $modelUser, $lang);
                $user = $modelUser->getInfo($id_user);
                $this->redirect("coreusers", [], ['user' => $user]);
                return;
            }
                
        }
        if ($id > 0 && $formPwd->check()) {
            $this->editPwdQuery($form, $modelUser, $lang);
            $this->redirect("coreusers");
            return;
        }

        $formPwdHtml = "";
        if ($id > 0) {
            $formPwdHtml = $formPwd->getHtml($lang);
        }
        if ($checked = true) {
            $this->render(array("formHtml" => $form->getHtml($lang), "formPwdHtml" => $formPwdHtml, "script" => $script));
        }
    }

    protected function editPwdQuery($formPwd, $modelUser, $lang) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $pwd = $formPwd->getParameter("pwd");
        $pwdconfirm = $formPwd->getParameter("pwdconfirm");
        if ($pwd != $pwdconfirm) {
            $_SESSION['flash'] = CoreTranslator::TheTwoPasswordAreDifferent($lang);
        } else {
            $modelUser->changePwd($formPwd->getParameter("id"), $pwd);
            $_SESSION['flash'] = CoreTranslator::PasswordHasBeenChanged($lang);
        }
    }

    protected function editQuery($form, $modelUser, $lang) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $id = $form->getParameter("id");
        if (!$id) {
            $pwd = $form->getParameter("pwd");
            $pwdconfirm = $form->getParameter("pwdconfirm");
            if ($pwd != $pwdconfirm) {
                $_SESSION['flash'] = CoreTranslator::TheTwoPasswordAreDifferent($lang);
            }
            $id = $modelUser->add(
                $form->getParameter("login"), $form->getParameter("pwd"), $form->getParameter("name"), $form->getParameter("firstname"), $form->getParameter("email"), $form->getParameter("status_id"), $form->getParameter("date_end_contract"), $form->getParameter("is_active")
            );
        } else {
            $modelUser->edit(
                    $id, $form->getParameter("login"), $form->getParameter("name"), $form->getParameter("firstname"), $form->getParameter("email"), $form->getParameter("status_id"), $form->getParameter("date_end_contract"), $form->getParameter("is_active")
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
            $_SESSION["message"] = CoreTranslator::UserIsMemberOfSpace($this->getLanguage());
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
                throw new PfmException(CoreTranslator::TheTwoPasswordAreDifferent($lang), 403);
            }
        } else {
            throw new PfmException(CoreTranslator::The_curent_password_is_not_correct($lang), 403);
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

    public function isuniqueAction($type, $value, $id_user) {
        $modelUser = new CoreUser();
        $email = "";
        $login = "";
        if ($id_user && $id_user > 0) {
          $user = $modelUser->getInfo($id_user);
          $email = $user['email'];
          $login = $user['login'];
        }
        if ($type === "email") {
            $isUnique = !$modelUser->isEmail($value, $email);
        } else if ($type === "login") {
            $isUnique = !$modelUser->isLogin($value, $login);
        } else {
            $isUnique = "wrong type";
        }
        $this->render(['data' => ['isUnique' => $isUnique]]);
    }

}
