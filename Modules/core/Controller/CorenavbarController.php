<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';

/**
 * Mather class for controller using secure connection
 * @deprecated replaced by framework Controller/Navbar
 * @author Sylvain Prigent
 */
class CorenavbarController extends CoresecureController
{
    /**
     *
     */
    public function indexAction()
    {
    }

    /**
     * Get the navbar content
     * @return string
     */
    public function navbar()
    {
        $login = '';
        if (isset($_SESSION["login"])) {
            $login = $_SESSION["login"];
        }
        $menu = $this->buildNavBar($login);
        return $menu;
    }

    /**
     * Get the tool menu
     * @return multitype: tool menu content
     */
    public function getMenu()
    {
        $modelMainMenus = new CoreMainMenu();
        $mainMenu = $modelMainMenus->getAll();
        /*
        $modelMainSubMenus = new CoreMainSubMenu();
        $submenus = $modelMainSubMenus->getAll();
        $submap = [];
        foreach($submenus as $submenu) {
            $submap[$submenu['id_main_menu']][] = $submenu;
        }

        for($i = 0 ; $i < count($mainMenu) ; $i++){
            //$mainMenu[$i]["items"] = $modelMainSubMenus->getForMenu($mainMenu[$i]["id"]);
            $mainMenu[$i]["items"] = $submap[$mainMenu[$i]['id']] ?? [];
        }
        */
        return $mainMenu;
    }

    /**
     * Get the admin menu
     * @return multitype: Amdin menu
     */
    public function getAdminMenu()
    {
        if (!isset($_SESSION["user_status"])) {
            return null;
        }
        $user_status_id = $_SESSION["user_status"];

        $toolAdmin = null;
        if ($user_status_id >= CoreStatus::$ADMIN) {
            $modulesModel = new CoreAdminMenu();
            $toolAdmin = $modulesModel->getAdminMenus();
        }
        return $toolAdmin;
    }

    /**
     * Get the navbar view
     * @param string $login User login
     * @return string: Menu view (html)
     */
    public function buildNavBar($login)
    {
        $userName = $login;
        $lang = $this->getLanguage();
        $toolMenu = $this->getMenu();
        $toolAdmin = $this->getAdminMenu();
        //print_r($toolMenu);

        // get the view menu,fill it, and return the content
        $view = $this->generateNavfile(
            array('userName' => $userName,
                'toolMenu' => $toolMenu,
                'toolAdmin' => $toolAdmin,
                'impersonate' => $_SESSION['logged_login'] ?? null,
                'lang' => $lang)
        );
        // Send the view
        return $view;
    }

    /**
     * Internal method to build the navbar into HTML
     * @param  $data navbar content
     * @throws Exception
     * @return string Menu view (html)
     */
    private function generateNavfile($data)
    {
        $file = 'Modules/core/View/navbar.php';
        if (file_exists($file)) {
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new PfmFileException("unable to find the file: '$file' ", 404);
        }
    }
}
