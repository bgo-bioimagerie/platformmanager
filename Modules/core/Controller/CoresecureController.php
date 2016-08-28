<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreStatus.php';


/**
 * Mother class for controller using secure connection
 * 
 * @author Sylvain Prigent
 */
abstract class CoresecureController extends Controller {

    /**
     * (non-PHPdoc)
     * @see Controller::runAction()
     */
    public function runAction($module, $action, $args = array()) {

        $modelConfig = new CoreConfig();
        if ($modelConfig->getParam("is_maintenance")) {
            if ($this->request->getSession()->getAttribut("user_status") < 4) {
                throw new Exception($modelConfig->getParam("maintenance_message"));
            }
        }

        if ($this->request->getSession()->isAttribut("id_user")) {

            $login = $this->request->getSession()->getAttribut("login");
            $company = $this->request->getSession()->getAttribut("company");

            $modelUser = new CoreUser();

            //$connect = $modelUser->connect2($login, $pwd);
            //echo "connect = " . $connect . "</br>";
            if ($modelUser->isUser($login) && Configuration::get("name") == $company) {
                parent::runAction($module, $action, $args);
            } else {
                //$this->callAction("connection");
                $this->redirect("coreconnection");
            }
        } else {
            $this->redirect("coreconnection");
            //$this->callAction("connection");
        }
    }

    public function checkAuthorization($minimumStatus){
        $auth = $this->isUserAuthorized($minimumStatus);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
        if ($auth == -1) {
            $this->redirect("coreconnection");
        }
    }
    
    public function isUserStatus($status){
        if (intval ($_SESSION["user_status"]) >= intval($status)){
            return true;
        }
        return false;
    }
    
    public function checkAuthorizationMenu($menuName){
        $auth = $this->isUserMenuAuthorized($menuName);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
        if ($auth == -1) {
            $this->redirect("coreconnection");
        }
    }
    
    public function checkAuthorizationMenuSpace($menuName, $id_space, $id_user){
        $modelSpace = new CoreSpace();
        $auth = $modelSpace->isUserMenuSpaceAuthorized($menuName, $id_space, $id_user);
        if ($auth == 0) {
            throw new Exception("Error 503: Permission denied");
        }
    }
    
    public function isUserAuthorized($minimumStatus){
        if ( isset($_SESSION["user_status"])){
            if (intval ($_SESSION["user_status"]) >= intval($minimumStatus)){
                return 1;
            }
            return 0;
        }
        return -1;
    }
    
    public function isUserMenuAuthorized($menuName){
        $controllerMenu = new CoreMenu();
        $minimumStatus = $controllerMenu->getMenuStatusByName($menuName);
        return $this->isUserAuthorized($minimumStatus);
    }
    
    public function checkSpaceAdmin($id_space, $id_user){
        
        $modelUser = new CoreUser();
        $userAppStatus = $modelUser->getStatus($id_user);
        if ($userAppStatus > 1){
            return true;
        }
        $modelSpace = new CoreSpace();
        $spaceRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($spaceRole < 4) {
            throw new Exception("Error 503: Permission denied");
        }
    }
}
