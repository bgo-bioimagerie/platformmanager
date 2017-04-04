<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoretilesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorization(CoreStatus::$USER);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $navController = new CorenavbarController();
        $toolMenu = $navController->getMenu();
        $toolAdmin = $navController->getAdminMenu();
        
        $modelConfig = new CoreConfig();
        $navcolor = $modelConfig->getParam("navbar_bg_highlight");
        $navcolortxt = $modelConfig->getParam("navbar_text_highlight");

        $lang = $this->getLanguage();
        $this->render(array('toolMenu' => $toolMenu, 'toolAdmin' => $toolAdmin, 
            "lang" => $lang, "navcolor" => $navcolor, "navcolortxt" => $navcolortxt));
        
    }

}
