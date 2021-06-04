<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CorehomeController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->checkAuthorization(CoreStatus::$USER);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();
        
        // get the first menu
        $modelMainMenu = new CoreMainMenu();
        $menus = $modelMainMenu->getAll();
        
        if (!empty($menus)) {  
            $this->redirect("coretiles/1/" . $menus[0]["id"]);            
        }
        else{
            return $this->render(array(
                'lang' => $lang,
                'metadesc' => 'pfm space list'    
            ));
        }
    }

}
