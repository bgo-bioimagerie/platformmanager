<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreMenu.php';
require_once 'Modules/core/Model/CoreLdap.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreSpace.php';


/**
 * Controler managing the user connection 
 * 
 * @author Sylvain Prigent
 */
class CoreconnectionController extends Controller {

    private $user;

    /**
     * Connstructor
     */
    public function __construct() {
        parent::__construct();
        $this->user = new CoreUser();
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


        $this->render(array("msgError" => $message, "admin_email" => $admin_email, "logo" => $logo,
            "home_title" => $home_title, "home_message" => $home_message,
            "redirection" => $redirection,
            "urlCarousel1" => $urlCarousel1,
            "urlCarousel2" => $urlCarousel2,
            "urlCarousel3" => $urlCarousel3,
            "language" => $language,
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
            $pwd = $this->request->getparameter("pwd");

            if ($login == "--") {
                $this->generateView(array('msgError' => 'Login not correct', "admin_email" => $admin_email), "index");
                return;
            }

            $connect = $this->connect($login, $pwd);
            //print_r($connect);
            if ($connect == "allowed") {

                // open the session
                session_unset();
                $user = $this->user->getUserByLogin($login);
                $this->request->getSession()->setAttribut("id_user", $user['idUser']);
                $this->request->getSession()->setAttribut("login", $user['login']);
                $this->request->getSession()->setAttribut("company", Configuration::get("name"));
                $this->request->getSession()->setAttribut("user_status", $user['status_id']);

                // add the user settings to the session
                $modelUserSettings = new CoreUserSettings();
                $settings = $modelUserSettings->getUserSettings($user['idUser']);
                $this->request->getSession()->setAttribut("user_settings", $settings);

                // update the user last connection
                $this->user->updateLastConnection($user['idUser']);

                // update user active base if the user is manager or admin
                $this->runModuleConnectionActions();
                $redirectPath = $this->getRedirectPath();
                $this->redirect($redirectPath);
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
        if ($redirectController == ""){
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
     * 
     */
    public function runModuleConnectionActions() {
        $modules = Configuration::get("modules");
        foreach ($modules as $module) {
            $controllerName = $module."connectscript";
            $classController = ucfirst(strtolower($controllerName)) . "Controller";
            $fileController = 'Modules/' . $module . "/Controller/" . $classController . ".php";
            //echo "controller file = " . $fileController . "<br/>";
            if (file_exists($fileController)) {
                // Instantiate controler
                require ($fileController);
                $controller = new $classController ();
                $controller->setRequest($this->request);
                $controller->runAction($module, "index");
            }
        }
    }

    /**
     * Logout (delete the session)
     */
    public function logoutAction() {
        $this->request->getSession()->destroy();
        $this->redirect("coretiles");
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
            //echo "found local user <br/>";
            return $this->user->connect($login, $pwd);
        }

        // search for LDAP account
        else {
            //echo "into LDap <br/>";
            $modelCoreConfig = new CoreConfig();
            if ($modelCoreConfig->getParam("useLdap") == true) {

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
                    foreach ($spacesToActivate as $spa){
                        $modelSpace->setUserIfNotExist($userInfo['idUser'], $spa['id'], $status);
                    }
                    
                    return $this->user->isActive($login);
                }
            }
        }

        return "Login or password not correct";
    }

}
