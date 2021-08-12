<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';

require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CorecookiesecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreStatus.php';


/**
 * Mother class for controller using secure connection
 * 
 * @author Sylvain Prigent
 */
abstract class CoresecureController extends CorecookiesecureController {

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->checkRememberMeCookie();
    }

    protected function checkRememberMeCookie() {
        // check if use a remember me

        if (!isset($_SESSION["id_user"])) {
            //echo "check the cookie <br/>";
            if (isset($_COOKIE['auth'])) {
                $auth = $_COOKIE['auth'];
                //echo "cookie auth = " . $auth . "<br/>";
                $authArray = explode('-', $auth);
                //print_r($authArray);
                $modelUser = new CoreUser();
                if (!$modelUser->isUserId($authArray[0])) {
                    //echo "user not found <br/>";
                    $this->redirect("coreconnection");
                    return 1;
                }

                $key = $modelUser->getRememberKey($authArray[0]);
                //echo "database key = " . $key . "<br/>"; 
                if ($key == $authArray[1]) {
                    //echo "cookie good<br/>";
                    // update the cookie
                    $key = sha1($this->generateRandomKey());
                    $cookieSet = setcookie("auth", $authArray[0] . "-" . $key, time() + 3600 * 24 * 3);
                    if (!$cookieSet) {
                        throw new PfmAuthException('cannot set the cookie in coresecure <br>', 403);
                    }
                    $modelUser->setRememberKey($authArray[0], $key);

                    $this->initSession($modelUser->getUserLogin($authArray[0]));

                    // redirect
                    return 2;
                } else {

                    setcookie('auth', '', time() - 3600);
                    //echo "cookie not good <br/>";
                    $this->redirectNoRemoveHeader("coreconnection");
                    return 0;
                }
            } else {
                //echo "cookie not found";
                return 0;
            }
        }
        return 0;
        //echo "check cookie <br/>";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::runAction()
     */
    public function runAction($module, $action, $args = array()) {
        $modelConfig = new CoreConfig();
        if ($modelConfig->getParam("is_maintenance") && ($this->request->getSession()->getAttribut("user_status") < 4)) {
                throw new PfmException($modelConfig->getParam("maintenance_message"), 503);
        }

        $cookieCheck = $this->checkRememberMeCookie();
        if ($cookieCheck == 2) {
            parent::runAction($module, $action, $args);
            return;
        } else if ($cookieCheck == 1) {
            return;
        }

        // Check by API Key
        if(isset($_SERVER["HTTP_X_API_KEY"])) {
            $modelUser = new CoreUser();
            $apiUser = $modelUser->getByApiKey($_SERVER["HTTP_X_API_KEY"]);
            if($apiUser != null) {
                Configuration::getLogger()->debug('[api][auth]', ['login' => $apiUser['login']]);
                $this->initSession($apiUser['login']);
                parent::runAction($module, $action, $args);
                return;
            }
        }

        // check if there is a session    
        if ($this->request->getSession()->isAttribut("id_user")) {
            $logged_in_space = 0;
            if($this->request->getSession()->isAttribut("logged_id_space")) {
                $logged_in_space = $this->request->getSession()->getAttribut("logged_id_space");
            }
            if(array_key_exists('id_space', $args) && $args['id_space'] > 0 && $logged_in_space > 0 && $logged_in_space != $args['id_space']) {
                throw new PfmException("Space not allowed with impersonification", 403);
            }

            $login = $this->request->getSession()->getAttribut("login");
            $company = $this->request->getSession()->getAttribut("company");

            $modelUser = new CoreUser();

            //$connect = $modelUser->connect2($login, $pwd);
            //echo "connect = " . $connect . "</br>";
            if ($modelUser->isUser($login) && Configuration::get("name") == $company) {
                parent::runAction($module, $action, $args);
                return;
            } else {
                //$this->callAction("connection");
                $this->redirect("coreconnection");
                return;
            }
        } else {
            Configuration::getLogger()->debug('no session');
            if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == 'application/json')  {
                throw new PfmAuthException('not connected', 401);
            }
            $this->redirect("coreconnection");
            //$this->callAction("connection");
            return;
        }
    }

    /**
     * 
     * @param type $minimumStatus
     * @throws Exception
     */
    public function checkAuthorization($minimumStatus) {
        $auth = $this->isUserAuthorized($minimumStatus);
        if ($auth == 0) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
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
            throw new PfmAuthException("Error 403: Permission denied", 403);
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
        if($this->isUserAuthorized(5)) {
            return true;
        }
        $modelSpace = new CoreSpace();
        $auth = $modelSpace->isUserMenuSpaceAuthorized($menuName, $id_space, $id_user);
        if ($auth == 0) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
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
        if($this->isUserAuthorized(5)) {
            return true;
        }
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
    /*
    public function isUserMenuAuthorized($menuName) {
        $controllerMenu = new CoreMenu();
        $minimumStatus = $controllerMenu->getMenuStatusByName($menuName);
        return $this->isUserAuthorized($minimumStatus);
    }
    */

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
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

        /**
     * 
     * @param type $id_space
     * @param type $id_user
     * @return boolean
     */
    public function isSpaceAdmin($id_space, $id_user) {

        $modelUser = new CoreUser();
        $userAppStatus = $modelUser->getStatus($id_user);
        if ($userAppStatus > 1) {
            return true;
        }
        $modelSpace = new CoreSpace();
        $spaceRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($spaceRole < 4) {
            return false;
        }
        return true;
    }

}
