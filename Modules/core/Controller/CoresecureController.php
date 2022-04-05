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
            if (isset($_COOKIE['auth'])) {
                $auth = $_COOKIE['auth'];
                $authArray = explode('-', $auth);
                $modelUser = new CoreUser();
                if (!$modelUser->isUserId($authArray[0])) {
                    $this->redirect("coreconnection");
                    return 1;
                }

                $key = $modelUser->getRememberKey($authArray[0]);
                if ($key == $authArray[1]) {
                    // update the cookie
                    $key = hash('sha512', $this->generateRandomKey());
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
                    $this->redirectNoRemoveHeader("coreconnection");
                    return 0;
                }
            } else {
                return 0;
            }
        }
        return 0;
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
            return parent::runAction($module, $action, $args);
        } else if ($cookieCheck == 1) {
            return null;
        }

        // Check by API Key
        if(isset($_SERVER["HTTP_X_API_KEY"])) {
            $modelUser = new CoreUser();
            $apiUser = $modelUser->getByApiKey($_SERVER["HTTP_X_API_KEY"]);
            if($apiUser != null) {
                Configuration::getLogger()->debug('[api][auth]', ['login' => $apiUser['login']]);
                $this->initSession($apiUser['login']);
                return parent::runAction($module, $action, $args);
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
                return  parent::runAction($module, $action, $args);
            } else {
                if($this->request->getSession()->getAttribut("id_user") == -1) {
                    Configuration::getLogger()->debug("[core] anonymous");
                    return parent::runAction($module, $action, $args);
                }
                Configuration::getLogger()->debug("[core] unknown user, redirect to login");                
                return $this->redirect("coreconnection");
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

            return parent::runAction($module, $action, $args);
        }
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

    protected function menusactivation($id_space, $module, $icon, $basemodule=null) {
        if($basemodule == null) {
            $basemodule = $module;
        }
        $modelSpace = new CoreSpace();
        $modelSpace->setSpaceMenu($id_space, $basemodule, $module, "bi-".$icon, 
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
