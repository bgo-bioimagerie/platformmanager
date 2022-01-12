<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Form.php';
require_once 'Framework/Email.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CorecookiesecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreLdap.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreSpace.php';

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
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
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

        if(isset($_SESSION['message'])) {
            $message =  $_SESSION['message'];
        }
        unset($_SESSION['message']);

        $openid_providers = Configuration::get("openid", []);
        $providers = [];
        if(!empty($openid_providers)) {
            foreach ($openid_providers as $openid_provider) {
                $nonce = uniqid("pfm");
                $provider = [
                    "name" => $openid_provider,
                    "url" => Configuration::get("openid_${openid_provider}_url"),
                    "icon" => Configuration::get("openid_${openid_provider}_icon"),
                    "login" => Configuration::get("openid_${openid_provider}_login"),
                    "client_id" => Configuration::get("openid_${openid_provider}_client_id"),
                    "client_secret" => Configuration::get("openid_${openid_provider}_client_secret"),
                    "callback" => Configuration::get("public_url")."/ooc/$openid_provider/authorized",
                    "nonce" => $nonce,
                ];
                $providers[] = $provider;
            }
        }
        $_SESSION["redirect"] = "coretiles";

        if(isset($_GET['redirect_url'])) {
            $redirection = urldecode($_GET['redirect_url']);
        }


        return $this->render(array("msgError" => $message, "admin_email" => $admin_email, "logo" => $logo,
            "home_title" => $home_title, "home_message" => $home_message,
            "redirection" => $redirection,
            "language" => $language,
            "metadesc" => 'platform manager login page',
            "providers" => $providers), "indexAction");
    }

    /**
     * Shows the login page
     * @throws Exception
     */
    public function loginAction() {
        $lang = $this->getLanguage();
        if ($this->request->isparameter("login") && $this->request->isParameter("pwd")) {
            $login = $this->request->getParameter("login");
            $pwd = $this->request->getparameter("pwd", false);

            $redirection = '';
            if($this->request->isparameter('redirection')) {
                $redirection = $this->request->getParameter('redirection');
            }

            $connect = $this->connect($login, $pwd);
            if ($connect === "allowed") {                
                $loggedUser = $this->initSession($login);
                // generate the remember me cookie
                if ($this->request->isparameter("remember")) {
                    $key = sha1($this->generateRandomKey());
                    $cookieSet = setcookie("auth", $loggedUser['idUser'] . "-" . $key, time() + 3600 * 24 * 3);
                    if (!$cookieSet) {
                        throw new PfmException("failed to set cookie with key " . $key, 500);
                    }
                    $modelUser = new CoreUser();
                    $modelUser->setRememberKey($loggedUser['idUser'], $key);
                }
                if($redirection) {
                    $this->redirect($redirection);
                    return;
                }
                // redirect
                $redirectPath = $this->getRedirectPath();
                $this->redirectNoRemoveHeader($redirectPath);
            } else {
                $this->loginError($redirection, $connect);
            }
        } else {
            throw new PfmAuthException(CoreTranslator::UndefinedCredentials($lang), 401);
        }
    }

    /**
     * In case of connection failure,
     * redirects and sets flash message depending on connection error message
     * 
     * @param string $redirection redirection string
     * @param string $connection_error error returned at connection failure
     * 
     */
    private function loginError($redirection, $connection_error = "") {
        $lang = $this->getLanguage();
        $_SESSION['flashClass'] = "danger";
        switch ($connection_error) {
            case "inactive":
                $msg = CoreTranslator::AccountInactive($lang);
                break;
            case "invalid_password":
                $msg = CoreTranslator::InvalidPassword($lang);
                break;
            case "invalid_login":
                $msg = CoreTranslator::InvalidLogin($lang);
                break;
            default:
                $msg = ($connection_error != "") ? $connection_error : CoreTranslator::ConnectionError($lang);
                break;
        }
        $_SESSION['flash'] = $msg;
        $this->redirect('/coreconnection?redirect_url='.$redirection);
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
            return $this->user->connect($login, $pwd);
        } else {
            // search for LDAP account
            $this->logger->debug('[auth] check ldap', ['ldap' => CoreLdapConfiguration::get('ldap_use', 0)]);
            if (CoreLdapConfiguration::get('ldap_use', 0)) {
                $this->logger->debug('[auth] ldap user', ['user' => $login]);
                $modelLdap = new CoreLdap();
                $ldapResult = $modelLdap->getUser($login, $pwd);
                if ($ldapResult == "error") {
                    return "Cannot connect to ldap using the given login and password";
                } else {
                    // update the user infos
                    $status = CoreLdapConfiguration::get('ldap_default_status', 1);
                    $this->user->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);

                    $userInfo = $this->user->getUserByLogin($login);
                    if(!$userInfo['apikey']) {
                        $this->user->newApiKey($userInfo['idUser']);
                    }
                    $modelSpace = new CoreSpace();
                    $spacesToActivate = $modelSpace->getSpaces('id');
                    foreach ($spacesToActivate as $spa) {
                        $modelSpace->setUserIfNotExist($userInfo['idUser'], $spa['id'], $status);
                    }
                    return $this->user->isActive($login);
                }
            }
        }
        return "invalid_login";
    }

    public function passwordforgottenAction() {

        $lang = $this->getLanguage();
        $form = new Form($this->request, 'formpasswordforgottern');
        $form->addEmail("email", CoreTranslator::Email($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "corepasswordforgotten");

        $_SESSION["message"] = CoreTranslator::PasswordForgotten($lang);
        if ($form->check()) {
            $email = $this->request->getParameter("email");
            $model = new CoreUser();
            $userByEmail = $model->getUserByEmail($email);
            if ($userByEmail) {

                if ($userByEmail["source"] == "ext") {
                    $_SESSION["message"] = CoreTranslator::ExtAccountMessage($lang);
                } else {

                    $newPassWord = $this->randomPassword();
                    $model->changePwd($userByEmail["id"], $newPassWord);

                    $mailer = new Email();
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
