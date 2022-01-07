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

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
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
                        throw new PfmAuthException('cannot set the cookie in coresecure', 403);
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
        if ($modelConfig->getParam("is_maintenance") && ($this->request->getSession()->getAttribut("user_status") < CoreStatus::$ADMIN)) {
                throw new PfmUserException($modelConfig->getParam("maintenance_message"), 503);
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

            if ($modelUser->isUser($login) && Configuration::get("name") == $company) {
                parent::runAction($module, $action, $args);
            } else {
                if($this->request->getSession()->getAttribut("id_user") == -1) {
                    Configuration::getLogger()->debug("[core] anonymous");
                    parent::runAction($module, $action, $args);
                    return;
                }
                Configuration::getLogger()->debug("[core] unknown user redirect to login");                
                $this->redirect("coreconnection");
            }
        } else {
            Configuration::getLogger()->debug('no session, anonymous user');
            if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == 'application/json')  {
                throw new PfmAuthException('not connected', 401);
            }

            $this->request->getSession()->setAttribut("id_user", -1);
            $this->request->getSession()->setAttribut("login", "anonymous");
            $this->request->getSession()->setAttribut("email", "");
            $this->request->getSession()->setAttribut("company", Configuration::get("name"));
            $this->request->getSession()->setAttribut("user_status", CoreStatus::$USER);

            //$this->redirect("coreconnection");
            parent::runAction($module, $action, $args);
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
            throw new PfmAuthException("Error: Permission denied", 403);
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
            if(isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0) {
                throw new PfmAuthException("Error 403: Permission denied", 403);
            }
            throw new PfmAuthException("Error 401: need to log", 401);
        } else {
            return true;
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
     * @deprecated
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

    protected function menusactivationForm($id_space, $module, $lang) {

        $modelSpace = new CoreSpace();
        $statusMenu = $modelSpace->getSpaceMenusRole($id_space, $module);
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, $module);
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, $module);
        $displayColorTxt = $modelSpace->getSpaceMenusTxtColor($id_space, $module);

        $form = new Form($this->request, $module."menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang). " ($module)");

        $roles = $modelSpace->roles($lang);

        $form->addSelect($module."Menustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusMenu);
        $form->addNumber($module."DisplayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor($module."DisplayColor", CoreTranslator::color($lang), false, $displayColor);
        $form->addColor($module."DisplayColorTxt", CoreTranslator::text_color($lang), false, $displayColorTxt);

        $form->setValidationButton(CoreTranslator::Save($lang), $module."config/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusactivation($id_space, $module, $icon) {
        $modelSpace = new CoreSpace();
        $modelSpace->setSpaceMenu($id_space, $module, $module, "glyphicon-".$icon, 
        $this->request->getParameter($module."Menustatus"),
        $this->request->getParameter($module."DisplayMenu"),
        1,
        $this->request->getParameter($module."DisplayColor"),
        $this->request->getParameter($module."DisplayColorTxt")
        );
    }

    protected function menuNameForm($id_space, $module, $lang) {
        $modelConfig = new CoreConfig();
        $menuName = $modelConfig->getParamSpace($module."menuname", $id_space);

        $form = new Form($this->request, $module."MenuNameForm");
        $form->addSeparator(CoreTranslator::MenuName($lang)." ($module)");

        $form->addText($module."MenuName", CoreTranslator::Name($lang), false, $menuName);

        $form->setValidationButton(CoreTranslator::Save($lang), $module."config/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function setMenuName($id_space, $module) {
        $modelConfig = new CoreConfig();
        $modelConfig->setParam($module."menuname", $this->request->getParameter($module."MenuName"), $id_space);
    }

}
