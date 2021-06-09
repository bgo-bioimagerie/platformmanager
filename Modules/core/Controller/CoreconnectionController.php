<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CorecookiesecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreLdap.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/mailer/Model/MailerSend.php';

/**
 * Controler managing the user connection
 *
 * @author Sylvain Prigent
 */
class CoreconnectionController extends CorecookiesecureController {

    private $user;
    private $logger;

    /**
     * Connstructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->user = new CoreUser();
        $this->logger = Configuration::getLogger();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     */
    public function indexAction($message = "", $redirection = "") {
        $language = $this->getLanguage();

        $modelConfig = new CoreConfig();
        $admin_email = $modelConfig->getParam("admin_email");
        $logo = $modelConfig->getParam("logo");
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");
        $urlCarousel1 = $modelConfig->getParam("connection_carousel1");
        $urlCarousel2 = $modelConfig->getParam("connection_carousel2");
        $urlCarousel3 = $modelConfig->getParam("connection_carousel3");
        $viewCarousel = $modelConfig->getParam("home_view_carousel");


        return $this->render(array("msgError" => $message, "admin_email" => $admin_email, "logo" => $logo,
            "home_title" => $home_title, "home_message" => $home_message,
            "redirection" => $redirection,
            "urlCarousel1" => $urlCarousel1,
            "urlCarousel2" => $urlCarousel2,
            "urlCarousel3" => $urlCarousel3,
            "language" => $language,
            "metadesc" => 'platform manager login page',
            "viewCarousel" => $viewCarousel), "indexAction");
    }

    /**
     * Shows the login page
     * @throws Exception
     */
    public function loginAction() {
        $modelConfig = new CoreConfig();
        $admin_email = $modelConfig->getParam("admin_email");

        if ($this->request->isparameter("login") && $this->request->isParameter("pwd")) {
            $login = $this->request->getParameter("login");
            $pwd = $this->request->getparameter("pwd", false);

            if ($login == "--") {
                $this->generateView(array('msgError' => 'Login not correct', "admin_email" => $admin_email), "index");
                return;
            }

            $connect = $this->connect($login, $pwd);
            //print_r($connect);
            if ($connect == "allowed") {

                $loggedUser = $this->initSession($login);

                // generate the remember me cookie
                if ($this->request->isparameter("remember")) {
                    //throw new Exception("Set cookie <br/>");
                    $key = sha1($this->generateRandomKey());
                    //echo "set cookie with id = " . $key . "<br/>";
                    $cookieSet = setcookie("auth", $loggedUser['idUser'] . "-" . $key, time() + 3600 * 24 * 3);
                    if (!$cookieSet) {
                        //die("die failed to set cookie with key " . $key . "<br/>");
                        throw new Exception("failed to set cookie with key " . $key . "<br/>");
                    }
                    $modelUser = new CoreUser();
                    $modelUser->setRememberKey($loggedUser['idUser'], $key);
                }

                // redirect
                $redirectPath = $this->getRedirectPath();
                $this->redirectNoRemoveHeader($redirectPath);
            } else {
                $this->indexAction($connect);
            }
        } else {
            throw new Exception("Action not allowed : login or passeword undefined");
        }
    }

    /**
     *
     * @return type
     */
    public function getRedirectPath() {
        $modelConfig = new CoreConfig();
        $redirectController = $modelConfig->getParam("default_home_path");
        if ($redirectController == "") {
            $redirectController = "coretiles";
        }
        if (isset($_SESSION["user_settings"]["homepage"])) {
            $redirectController = $_SESSION["user_settings"]["homepage"];
        }
        $redirectionForm = $this->request->getParameter('redirection');
        if ($redirectionForm != "") {
            $redirectController = $redirectionForm;
        }
        return $redirectController;
    }

    /**
     * Logout (delete the session)
     */
    public function logoutAction() {

        setcookie('auth', '', -1);

        $this->request->getSession()->destroy();
        $this->redirectNoRemoveHeader("coretiles");
    }

    /**
     * Connect a user to the application
     * @param string $login User login
     * @param string $pwd User pssword
     * @return string Error message
     */
    private function connect($login, $pwd) {

        // test if local account
        if ($this->user->isLocalUser($login)) {
            $this->logger->debug('[auth] local user', ['user' => $login]);
            //echo "found local user <br/>";
            return $this->user->connect($login, $pwd);
        }

        // search for LDAP account
        else {
            //echo "into LDap <br/>";
            $modelCoreConfig = new CoreConfig();
            $this->logger->debug('[auth] check ldap', ['ldap' => $modelCoreConfig->getParam("useLdap")]);
            if ($modelCoreConfig->getParam("useLdap")) {
                $this->logger->debug('[auth] ldap user', ['user' => $login]);
                $modelLdap = new CoreLdap();
                $ldapResult = $modelLdap->getUser($login, $pwd);
                if ($ldapResult == "error") {
                    return "Cannot connect to ldap using the given login and password";
                } else {
                    // update the user infos
                    $status = $modelCoreConfig->getParam("ldapDefaultStatus");
                    $this->user->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);

                    $userInfo = $this->user->getUserByLogin($login);
                    //print_r($userInfo);

                    $modelSpace = new CoreSpace();
                    $spacesToActivate = $modelSpace->getSpaces('id');
                    foreach ($spacesToActivate as $spa) {
                        $modelSpace->setUserIfNotExist($userInfo['idUser'], $spa['id'], $status);
                    }

                    return $this->user->isActive($login);
                }
            }
        }

        return "Login or password not correct";
    }

    public function passwordforgottenAction() {

        $lang = $this->getLanguage();
        $form = new Form($this->request, 'formpasswordforgottern');
        $form->addText("email", CoreTranslator::Email($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "corepasswordforgotten");

        $_SESSION["message"] = CoreTranslator::PasswordForgotten($lang);
        if ($form->check()) {
            $email = $this->request->getParameter("email");
            $model = new CoreUser();
            $userByEmail = $model->getUserByEmail($email);
            if ($userByEmail) {

                if ($userByEmail["source"] == "ext") {
                    $_SESSION["message"] = CoreTranslator::ExtAccountMessage(lang);
                } else {

                    $newPassWord = $this->randomPassword();
                    $model->changePwd($userByEmail["id"], $newPassWord);

                    $mailer = new MailerSend();
                    $from = Configuration::get('smtp_from');
                    $fromName = "Platform-Manager";
                    $toAdress = $email;
                    $subject = CoreTranslator::AccountPasswordReset($lang);
                    $content = CoreTranslator::AccountPasswordResetMessage($lang) . "'" . $newPassWord . "'";
                    $mailer->sendEmail($from, $fromName, $toAdress, $subject, $content, false);
                    $_SESSION["message"] = CoreTranslator::ResetPasswordMessageSend($lang);
                }

            }
            else{
                $_SESSION["message"] = CoreTranslator::UserNotFoundWithEmail($lang);
            }
        }

        $modelConfig = new CoreConfig();
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");


        return $this->render(array("home_title" => $home_title,
            "home_message" => $home_message,
            "formHtml" => $form->getHtml($lang)));
    }

    protected function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

}
