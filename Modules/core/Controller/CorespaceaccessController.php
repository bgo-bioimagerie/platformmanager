<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Download.php';


require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';

require_once 'Modules/mailer/Model/MailerSend.php';


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

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $letter = "A", $active = "") {

        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        // check input letter
        if( $letter == ""){
            if (isset($_SESSION["user_last_letter"])){
                $letter = $_SESSION["user_last_letter"];
            }
            else{
                $letter = "A";
            }
        }

         // check input active
        $_SESSION["user_last_letter"] = $letter;
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        if ($active == "") {
            if (isset($_SESSION["users_lastvisited"])) {
                $active = $_SESSION["users_lastvisited"];
            } else {
                $active = "active";
            }
        }

        // get user list
        $modelUser = new CoreUser();
        $usersArray = array();
        
        if ($active == "active") {
            if($letter == "All"){
                $usersArray = $modelUser->getActiveUsersInfo(1);
            }
            else{
                $usersArray = $modelUser->getActiveUsersInfoLetter($letter, 1);
            }
        } else {
            if($letter == "All"){
                $usersArray = $modelUser->getActiveUsersInfo(0);
            }
            else{
                $usersArray = $modelUser->getActiveUsersInfoLetter($letter, 0);
            }

        }

        $modelSpaceUser = new CoreSpaceUser();
        for ($i = 0; $i < count($usersArray); $i++) {
            $userSpaceInfo = $modelSpaceUser->getUserSpaceInfo2($id_space, $usersArray[$i]["id"]);
            $usersArray[$i]["date_convention"] = CoreTranslator::dateFromEn($userSpaceInfo["date_convention"], $lang);
            $usersArray[$i]["convention_url"] = $userSpaceInfo["convention_url"];
            $usersArray[$i]["date_contract_end"] = CoreTranslator::dateFromEn($userSpaceInfo["date_contract_end"], $lang);
            $usersArray[$i]['spaces'] = $modelSpace->getUserSpacesRolesSummary($usersArray[$i]['id']);
        }

        $usersArray = $this->getUsersOfSpace($id_space, $usersArray);

        // table view
        $table = new TableView();
        $table->addLineButton("coreaccessuseredit/" . $id_space, "id", CoreTranslator::Access($lang));

        $modelOptions = new CoreSpaceAccessOptions();
        $options = $modelOptions->getAll($id_space);
        foreach($options as $option){
            $translatorName = ucfirst($option["module"]).'Translator';
            require_once 'Modules/'.$option["module"].'/Model/'.$translatorName.'.php';
            $table->addLineButton($option["url"]."/" . $id_space, "id", $translatorName::$option["toolname"]($lang));
        }

        $tableContent = array(
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "login" => CoreTranslator::Login($lang),
            "email" => CoreTranslator::Email($lang),
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
            'space' => $space
                ), "indexAction");

    }

    /**
     * gherve: function added for multi-tenant feature
     * 
     * @param int $id_space id of space
     * @param array $usersArray users from different spaces
     * @return array users from space of id $space_id
     */
    public function getUsersOfSpace($id_space, $usersArray) {
        $result = array();
        $modelSpaceUser = new CoreSpaceUser();
        
        for ($i = 0; $i < count($usersArray); $i++) {
            if ($modelSpaceUser->exists($usersArray[$i]['id'], $id_space)) {
                array_push($result, $usersArray[$i]);
            }            
        }
        return $result;
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
        $form->addText("login", CoreTranslator::Login($lang), true);
        $form->addEmail("email", CoreTranslator::email($lang), true);
        $form->addText("phone", CoreTranslator::Phone($lang), false);

        $form->setValidationButton(CoreTranslator::Ok($lang), "corespaceaccessuseradd/".$id_space);

        if ($form->check()) {

            $modelCoreUser = new CoreUser();

            if ($modelCoreUser->isLogin($form->getParameter("login"))) {
                $_SESSION["message"] = CoreTranslator::Error($lang) . ":" . CoreTranslator::LoginAlreadyExists($lang);
            } else {
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
                
                $mailParams = ["email" => $form->getParameter("email"), "login" => $form->getParameter("login"), "pwd" => $pwd];
                $this->notifyUserByEmail($mailParams, "add_new_user_to_space");

                $modelSpacePending = new CorePendingAccount();
                $modelSpacePending->add($id_user, $id_space);

                $_SESSION["message"] = CoreTranslator::AccountHasBeenCreated($lang);

                $this->redirect("corespaceaccessuseradd/".$id_space);
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
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $spaceUserInfo["status"]);
        $form->addDate("date_contract_end", CoreTranslator::Date_end_contract($lang), false, CoreTranslator::dateFromEn($spaceUserInfo["date_contract_end"], $lang));
        $form->addDate("date_convention", CoreTranslator::Date_convention($lang), false, CoreTranslator::dateFromEn($spaceUserInfo["date_convention"], $lang));
        $form->addUpload("convention", CoreTranslator::Convention($lang), $spaceUserInfo["convention_url"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "coreaccessuseredit/".$id_space."/".$id);
        $form->setDeleteButton(CoreTranslator::Delete($lang), "spaceconfigdeleteuser/".$id_space, $id);
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

    public function pendingusersAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelSpacePending = new CorePendingAccount();
        $modelUser = new CoreUser();

        $data = $modelSpacePending->getPendingForSpace($id_space);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["fullname"] = $modelUser->getUserFUllName($data[$i]["id_user"]);
            $data[$i]["date_created"] = $modelUser->getDateCreated($data[$i]["id_user"]);
        }

        $table = new TableView();
        $table->setTitle(CoreTranslator::PendingUserAccounts($lang));
        $table->addLineButton("corespacependinguseredit/".$id_space, "id", CoreTranslator::Activate($lang));
        $table->addLineButton("corespacependinguserdelete/".$id_space, "id", CoreTranslator::Delete($lang));

        $headers = array(
            'fullname' => CoreTranslator::Name($lang),
            'date_created' => CoreTranslator::DateCreated($lang)
        );
        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            "space" => $space
        ));
    }

    public function pendingusereditAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelPending = new CorePendingAccount();
        $pendingInfo = $modelPending->get($id);

        $modelUser = new CoreUser();
        $fullname = $modelUser->getUserFUllName($pendingInfo["id_user"]);
        $modelStatus = new CoreSpace();
        $roles = $modelStatus->roles($lang);

        $form = new Form($this->request, "pendingusereditactionform");
        $form->setTitle(CoreTranslator::Activate($lang) . ": " . $fullname);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "corespacependinguseredit/".$id_space."/".$id);

        if ( $form->check() ){

            $modelUser->validateAccount($pendingInfo["id_user"]);
            $modelSpace->setUserIfNotExist($pendingInfo["id_user"], $id_space, $form->getParameter("role"));
            $modelPending->validate($id, $_SESSION["id_user"]);

            $mailParams = ["id_space" => $id_space, "id_user" => $pendingInfo["id_user"]];
            $this->notifyUserByEmail($mailParams, "accept_pending_user");

            $_SESSION["message"] = CoreTranslator::UserAccountHasBeenActivated($lang);
            $this->redirect("corespacependinguseredit/".$id_space."/".$id);
        }

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang),
            "space" => $space
        ));
    }

    /**
     * Reject a pending user
     * (sets validate=0 && validated_by=<logged user id> in core_pending_accounts)
     * 
     * @param int $id_space
     * @param int $id_pendingAccount
     */
    public function pendinguserdeleteAction($id_space, $id_pendingAccount) {
        $this->checkAuthorization(CoreStatus::$ADMIN);
        $modelPending = new CorePendingAccount();
        $modelPending->invalidate($id_pendingAccount, $_SESSION["id_user"]);
        $mailParams = ["id_space" => $id_space, "id_user" => $modelPending->get($id_pendingAccount)["id_user"]];
        $this->notifyUserByEmail($mailParams, "reject_pending_user");
        $this->redirect("corespacependingusers/".$id_space);
    }

    /**
     * 
     * Send an Email to user. Manage the following cases :
     * - user account has been created
     * - user is accepted as a member of space
     * - user request to join a space is rejected
     *
     * @param array $params required to fill sendEmail() parameters. Depends on why we want to notify user
     * @param string $origin determines how to get sendEmail() paramters from $params
     */
    public function notifyUserByEmail($params, $origin) {
        $lang = $this->getLanguage();
        $fromName = "Platform-Manager";
        $mail_from = Configuration::get('smtp_from');
        $from = (!empty($mail_from)) ? $mail_from : "support@platform-manager.com";

        if ($origin === "add_new_user_to_space") {
            $fromName = "Platform-Manager";
            $toAdress = $params["email"];
            $subject = CoreTranslator::Account($lang);
            $content = CoreTranslator::AccountCreatedEmail($lang, $params["login"], $params["pwd"]);
        } else if ($origin === "accept_pending_user" || $origin === "reject_pending_user") {
            $accepted = ($origin === "accept_pending_user") ? true : false;
            $spaceModel = new CoreSpace();
            $userModel = new CoreUser();
            $pendingUser = $userModel->getInfo($params["id_user"]);
            $userFullName = $pendingUser["firstname"] . " " . $pendingUser["name"];
            $spaceName = $spaceModel->getSpace($params["id_space"])["name"];
            $subject = CoreTranslator::JoinResponseSubject($spaceName, $lang);
            $content = CoreTranslator::JoinResponseEmail($userFullName, $spaceName, $accepted, $lang);
            $toAdress = $pendingUser["email"];
        } else {
            Configuration::getLogger()->debug("notifyUserByEmail", ["message" => "origin parameter is not set properly", "origin" => $origin]);
        }

        $mailer = new MailerSend();
        $mailer->sendEmail($from, $fromName, $toAdress, $subject, $content, false);
    }

}
