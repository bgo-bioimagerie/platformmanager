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
class CoreconnectionController extends CorecookiesecureController
{
    private $user;
    private $logger;

    /**
     * Connstructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->user = new CoreUser();
        $this->logger = Configuration::getLogger();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     */
    public function indexAction($message = "", $redirection = "")
    {
        $language = $this->getLanguage();

        $modelConfig = new CoreConfig();
        $admin_email = $modelConfig->getParam("admin_email");
        $logo = $modelConfig->getParam("logo");
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");

        $openid_providers = Configuration::get("openid", []);
        $providers = [];
        if (!empty($openid_providers)) {
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

        if (isset($_GET['redirect_url'])) {
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
    public function loginAction()
    {
        $lang = $this->getLanguage();
        if ($this->request->isParameter("login") && $this->request->isParameter("pwd")) {
            $login = $this->request->getParameter("login");
            $pwd = $this->request->getParameter("pwd", false);

            $redirection = '';
            if ($this->request->isParameter('redirection')) {
                $redirection = $this->request->getParameter('redirection');
            }
            try {
                $login = $this->userConnection($login, $pwd);
                $loggedUser = $this->initSession($login);
            } catch (PfmAuthException $e) {
                return $this->loginError($redirection, $e->getMessage());
            }

            // generate the remember me cookie
            if ($this->request->isParameter("remember")) {
                $key = hash('sha512', $this->generateRandomKey());
                $cookieSet = setcookie("auth", $loggedUser['idUser'] . "-" . $key, time() + 3600 * 24 * 3);
                if (!$cookieSet) {
                    throw new PfmException("failed to set cookie with key " . $key, 500);
                }
                $modelUser = new CoreUser();
                $modelUser->setRememberKey($loggedUser['idUser'], $key);
            }
            if ($redirection) {
                return $this->redirect($redirection);
            }
            // redirect
            $redirectPath = $this->getRedirectPath();
            $this->redirectNoRemoveHeader($redirectPath);
        } else {
            throw new PfmAuthException(CoreTranslator::UndefinedCredentials($lang), 401);
        }
        return null;
    }

    /**
     * In case of connection failure,
     * redirects and sets flash message depending on connection error message
     *
     * @param string $redirection redirection string
     * @param string $connection_error error returned at connection failure
     *
     */
    private function loginError($redirection, $connection_error = 0)
    {
        $lang = $this->getLanguage();
        $_SESSION['flashClass'] = "danger";
        $_SESSION['flash'] = CoreTranslator::ConnectionError($lang, $connection_error);
        return $this->redirect('/coreconnection?redirect_url='.$redirection);
    }

    /**
     *
     * @return type
     */
    public function getRedirectPath()
    {
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
    public function logoutAction()
    {
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
    private function userConnection($login, $pwd)
    {
        // Check if localUser
        try {
            $localUser = $this->user->isLocalUser($login);
            if ($localUser) {
                $this->logger->debug('[auth] local user', ['user' => $localUser['login']]);
                $this->user->connect($localUser['login'], $pwd);
                return $localUser['login'];
            } else {
                // search for LDAP account
                $this->logger->debug('[auth] check ldap', ['ldap' => CoreLdapConfiguration::get('ldap_use', 0)]);
                if (CoreLdapConfiguration::get('ldap_use', 0)) {
                    $this->logger->debug('[auth] ldap user', ['user' => $login]);
                    $modelLdap = new CoreLdap();
                    $ldapResult = $modelLdap->getUser($login, $pwd);
                    if ($ldapResult == "error") {
                        throw new PfmAuthException(CoreUser::$CNX_INVALID_LDAP);
                    } else {
                        // update the user infos
                        $this->user->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);
                        $userInfo = $this->user->getUserByLogin($login);
                        if (!$userInfo['apikey']) {
                            $this->user->newApiKey($userInfo['idUser']);
                        }
                        try {
                            $this->user->isActive($login);
                        } catch (PfmAuthException $e) {
                            throw new PfmAuthException($e->getMessage());
                        }
                        return $login;
                    }
                }
                throw new PfmAuthException(CoreUser::$CNX_INVALID_LOGIN);
            }
        } catch(PfmAuthException $e) {
            throw new PfmAuthException($e->getMessage());
        }
    }

    public function passwordforgottenAction()
    {
        $lang = $this->getLanguage();
        $form = new Form($this->request, 'formpasswordforgottern');
        $form->addEmail("email", CoreTranslator::Email($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "corepasswordforgotten");

        $_SESSION['flash'] = CoreTranslator::PasswordForgotten($lang);
        $_SESSION["flashClass"] = 'info';
        if ($form->check()) {
            $email = $this->request->getParameter("email");
            $model = new CoreUser();
            $userByEmail = $model->getUserByEmail($email);
            if ($userByEmail) {
                if ($userByEmail["source"] == "ext") {
                    $_SESSION['flash'] = CoreTranslator::ExtAccountMessage($lang);
                } else {
                    $modelCoreUser = new CoreUser();
                    $newPassword = $modelCoreUser->generateRandomPassword();
                    $model->changePwd($userByEmail["id"], $newPassword);

                    $mailer = new Email();
                    $from = Configuration::get('smtp_from');
                    $fromName = "Platform-Manager";
                    $toAdress = $email;
                    $subject = CoreTranslator::AccountPasswordReset($lang);
                    $content = CoreTranslator::AccountPasswordResetMessage($lang) . "'" . $newPassword . "'";
                    $mailer->sendEmail($from, $fromName, $toAdress, $subject, $content, false);
                    $_SESSION['flash'] = CoreTranslator::ResetPasswordMessageSend($lang);
                    $_SESSION["flashClass"] = 'success';
                }
            } else {
                $_SESSION['flash'] = CoreTranslator::UserNotFoundWithEmail($lang);
                $_SESSION["flashClass"] = 'danger';
            }
        }

        $modelConfig = new CoreConfig();
        $home_title = $modelConfig->getParam("home_title");
        $home_message = $modelConfig->getParam("home_message");


        return $this->render(array("home_title" => $home_title,
            "home_message" => $home_message,
            "formHtml" => $form->getHtml($lang)));
    }
}
