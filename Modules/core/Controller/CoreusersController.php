<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
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
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["status"] = CoreTranslator::Translate_status($lang, $modelStatus->getStatusName($data[$i]["status_id"]));
            if ($data[$i]["is_active"] == 1) {
                $data[$i]["is_active"] = CoreTranslator::yes($lang);
            } else {
                $data[$i]["is_active"] = CoreTranslator::no($lang);
            }

            $data[$i]["date_last_login"] = CoreTranslator::dateFromEn($data[$i]["date_last_login"], $lang);
        }

        $tableHtml = $table->view($data, $header);
        return $this->render(array("tableHtml" => $tableHtml, "lang" => $lang));
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
        // #105: add readonly
        $isLoginLocked = (!$id) ? false : true;
        $form->addText("login", CoreTranslator::Login($lang), !$isLoginLocked, $user["login"], readonly: $isLoginLocked);
        if (!$id) {
            $form->addPassword("pwd", CoreTranslator::Password($lang));
            $form->addPassword("pwdconfirm", CoreTranslator::Password($lang));
        }
        $form->addText("name", CoreTranslator::Name($lang), false, $user["name"]);
        $form->addText("firstname", CoreTranslator::Firstname($lang), false, $user["firstname"]);
        $form->addEmail("email", CoreTranslator::Email($lang), false, $user["email"]);

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
        if ($form->check()) {
            if (!$id && $modelUser->isLogin($this->request->getParameter('login'))) {
                $script .= '<script language="javascript">';
                $script .= 'alert("' . CoreTranslator::LoginAlreadyExists($lang) . '")';
                $script .= '</script>';
            } else {
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
        return $this->render(array("formHtml" => $form->getHtml($lang), "formPwdHtml" => $formPwdHtml, "script" => $script));
    }

    protected function editPwdQuery($formPwd, $modelUser, $lang) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $pwd = $formPwd->getParameter("pwd");
        $pwdconfirm = $formPwd->getParameter("pwdconfirm");
        if ($pwd != $pwdconfirm) {
            throw new Exception(CoreTranslator::TheTwoPasswordAreDifferent($lang));
        }

        $modelUser->changePwd($formPwd->getParameter("id"), $pwd);
    }

    protected function editQuery($form, $modelUser, $lang) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $id = $form->getParameter("id");
        if (!$id) {
            $pwd = $form->getParameter("pwd");
            $pwdconfirm = $form->getParameter("pwdconfirm");
            if ($pwd != $pwdconfirm) {
                throw new Exception(CoreTranslator::TheTwoPasswordAreDifferent($lang));
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

        return $this->render(array(
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
                throw new Exception(CoreTranslator::TheTwoPasswordAreDifferent($lang));
            }
        } else {
            throw new Exception(CoreTranslator::The_curent_password_is_not_correct($lang));
        }
    }

}
