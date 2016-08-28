<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreMenu.php';

/**
 * Mather class for controller using secure connection
 * 
 * @author Sylvain Prigent
 */
class CorenavbarController extends CoresecureController {

    public function __construct() {
        parent::__construct();
        $this->checkAuthorization(CoreStatus::$USER);
    }
    
    public function indexAction() {
        
    }

    /**
     * Get the navbar content
     * @return string
     */
    public function navbar() {
        
        $menu = $this->buildNavBar($_SESSION["login"]);
        return $menu;
    }

    /**
     * Get the tool menu
     * @return multitype: tool menu content
     */
    public function getMenu() {
        $modulesModel = new CoreMenu();
        $toolMenu = $modulesModel->getMenus("name");
        
        for($i = 0 ; $i < count($toolMenu) ; $i++){
            $toolMenu[$i]["items"] = $modulesModel->getItemsFormMenu($toolMenu[$i]["id"]);
        }
        return $toolMenu;
    }
    
    /**
     * Get the admin menu
     * @return multitype: Amdin menu
     */
    public function getAdminMenu() {
        $user_status_id = $_SESSION["user_status"];

        $toolAdmin = null;
        if ($user_status_id >= CoreStatus::$ADMIN) {
            $modulesModel = new CoreMenu();
            $toolAdmin = $modulesModel->getAdminMenus();
        }
        return $toolAdmin;
    }

    /**
     * Get the navbar view
     * @param string $login User login
     * @return string: Menu view (html) 
     */
    public function buildNavBar($login) {
        $userName = $login;
        $lang = $this->getLanguage();
        $toolMenu = $this->getMenu();
        $toolAdmin = $this->getAdminMenu();

        // get the view menu,fill it, and return the content
        $view = $this->generateNavfile(
                array('userName' => $userName,
                    'toolMenu' => $toolMenu, 
                    'toolAdmin' => $toolAdmin,
                    'lang' => $lang));
        // Send the view
        return $view;
    }

    /**
     * Internal method to build the navbar into HTML
     * @param  $data navbar content
     * @throws Exception
     * @return string Menu view (html) 
     */
    private function generateNavfile($data) {
        $file = 'Modules/core/View/navbar.php';
        if (file_exists($file)) {
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new Exception("unable to find the file: '$file' ");
        }
    }

}
