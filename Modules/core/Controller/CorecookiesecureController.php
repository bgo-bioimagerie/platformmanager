<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreStatus.php';

require_once 'Modules/core/Model/CoreUserSettings.php';

/**
 * Mother class for controller using secure connection
 * 
 * @author Sylvain Prigent
 */
abstract class CorecookiesecureController extends Controller {

    function initSession($login) {
        
        // open the session
        session_unset();
        $modelUser = new CoreUser();
        $sessuser = $modelUser->getUserByLogin($login);
        
        /*
        $_SESSION["id_user"] = $sessuser['idUser'];
        $_SESSION["login"] = $sessuser['login'];
        $_SESSION["company"] = Configuration::get("name");
        $_SESSION["user_status"] = $sessuser['status_id'];
        */
        
        $this->request->getSession()->setAttribut("id_user", $sessuser['idUser']);
        $this->request->getSession()->setAttribut("login", $sessuser['login']);
        $this->request->getSession()->setAttribut("company", Configuration::get("name"));
        $this->request->getSession()->setAttribut("user_status", $sessuser['status_id']);
        

        // add the user settings to the session
        $modelUserSettings = new CoreUserSettings();
        $settings = $modelUserSettings->getUserSettings($sessuser['idUser']);
        //$_SESSION["user_settings"] = $settings;
        $this->request->getSession()->setAttribut("user_settings", $settings);

        // update the user last connection
        $modelUser->updateLastConnection($sessuser['idUser']);

        // update user active base if the user is manager or admin
        $this->runModuleConnectionActions();
        
        // if user admin a space, update the user list
        $modelSpace = new CoreSpace();
        if( $sessuser['status_id'] > 1 || $modelSpace->doesManageSpace($sessuser['idUser']) ){
            
            $moselSettings = new CoreConfig();
            $desactivateSetting = $moselSettings->getParam("user_desactivate");
            $modelUser->disableUsers($desactivateSetting);
            
        }
        
        
        return $sessuser;
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
                $controller = new $classController ($this->request);
                //$controller->setRequest($this->request);
                $controller->runAction($module, "index");
            }
        }
    }

    function generateRandomKey() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * 
     * @param type $minimumStatus
     * @throws Exception
     */
    public function checkAuthorization($minimumStatus) {
        $auth = $this->isUserAuthorized($minimumStatus);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
        if ($auth == -1) {
            $this->redirect("coreconnection");
        }
    }

    /**
     * 
     * @param type $status
     * @return boolean
     */
    public function isUserStatus($status) {
        if (intval($_SESSION["user_status"]) >= intval($status)) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $menuName
     * @throws Exception
     */
    public function checkAuthorizationMenu($menuName) {
        $auth = $this->isUserMenuAuthorized($menuName);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
        if ($auth == -1) {
            $this->redirect("coreconnection");
        }
    }

    /**
     * 
     * @param type $menuName
     * @param type $id_space
     * @param type $id_user
     * @throws Exception
     */
    public function checkAuthorizationMenuSpace($menuName, $id_space, $id_user) {
        $modelSpace = new CoreSpace();
        $auth = $modelSpace->isUserMenuSpaceAuthorized($menuName, $id_space, $id_user);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
    }

    /**
     * 
     * @param type $menuName
     * @param type $id_space
     * @param type $id_user
     * @throws Exception
     */
    public function checkAuthorizationMenuSpaceNoException($menuName, $id_space, $id_user) {
        $modelSpace = new CoreSpace();
        $auth = $modelSpace->isUserMenuSpaceAuthorized($menuName, $id_space, $id_user);
        if ($auth == 0) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $minimumStatus
     * @return int
     */
    public function isUserAuthorized($minimumStatus) {
        if (isset($_SESSION["user_status"])) {
            if (intval($_SESSION["user_status"]) >= intval($minimumStatus)) {
                return 1;
            }
            return 0;
        }
        return -1;
    }

    /**
     * 
     * @param type $menuName
     * @return type
     */
    public function isUserMenuAuthorized($menuName) {
        $controllerMenu = new CoreMenu();
        $minimumStatus = $controllerMenu->getMenuStatusByName($menuName);
        return $this->isUserAuthorized($minimumStatus);
    }

    /**
     * 
     * @param type $id_space
     * @param type $id_user
     * @return int
     */
    public function getUserSpaceStatus($id_space, $id_user) {
        $modelUser = new CoreUser();
        $userAppStatus = $modelUser->getStatus($id_user);
        if ($userAppStatus > 1) {
            return 4;
        }
        $modelSpace = new CoreSpace();
        $spaceRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        return $spaceRole;
    }

    /**
     * 
     * @param type $id_space
     * @param type $id_user
     * @return boolean
     * @throws Exception
     */
    public function checkSpaceAdmin($id_space, $id_user) {

        $modelUser = new CoreUser();
        $userAppStatus = $modelUser->getStatus($id_user);
        if ($userAppStatus > 1) {
            return true;
        }
        $modelSpace = new CoreSpace();
        $spaceRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($spaceRole < 4) {
            throw new Exception("Error 503: Permission denied");
        }
    }

}
