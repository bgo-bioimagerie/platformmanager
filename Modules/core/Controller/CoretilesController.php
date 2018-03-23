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
class CoretilesController extends CoresecureController {

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
    public function indexAction($level = 1, $id = -1) {

        if ( $id < 0 ){
            $this->redirect("coretilesdoc");
        }
        
        if ( $level == 1 ){
            $this->showMainMenu($id);
        }
        else if ($level == 2){
            $this->showMainSubMenu($id);
        }
        else{
            $this->redirect("corehome");
        }
        
    }
    
    public function showMainMenu($id){
        $modelMenu = new CoreMainMenu();
        
        if ($id < 0){
            $id = $modelMenu->getFirstIdx();
        }
        // get default sub menu
        $id_sub = $modelMenu->getFirstSubMenu($id);
        
        $this->showMainSubMenu($id_sub);
    }
    
    public function showMainSubMenu($id){
        
        $modelSubMenu = new CoreMainSubMenu();
        
        if ($id < 0){
            $id = $modelSubMenu->getFirstIdx();
        }
        
        $modelMainMenuItem = new CoreMainMenuItem();
        $mainSubMenus = $modelSubMenu->getForMenu($modelSubMenu->getMainMenu($id));
        
        $showSubBar = false;
        if ( $modelMainMenuItem->haveAllSingleItem($mainSubMenus) ){
            $items = $modelMainMenuItem->getSpacesFromSingleItemList($mainSubMenus);
            $title = $modelSubMenu->getMainMenuName($id);
        }
        else{
            if (count($mainSubMenus) > 1){
                $showSubBar = true;
            }
            $items = $modelMainMenuItem->getSpacesFromSubMenu($id);
            $title = $modelSubMenu->getName($id);
        }
        
        $lang = $this->getLanguage();
        $modelCoreConfig = new CoreConfig();
        $this->render(array(
            'lang' => $lang,
            'iconType' => $modelCoreConfig->getParam("space_icon_type"),
            'showSubBar' => $showSubBar,
            'items' => $items,
            'mainSubMenus' => $mainSubMenus,
            'title' => $title
        ), "indexAction");
    }
    
    public function docAction(){
        
        $this->render(array(
            "lang" => $this->getLanguage()
        ));
    }

}
