<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CorePendingAccount.php';


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
        else {
            if (count($mainSubMenus) > 1){
                $showSubBar = true;
            }
            $items = $modelMainMenuItem->getSpacesFromSubMenu($id);
            $title = $modelSubMenu->getName($id);
        }
        
        $lang = $this->getLanguage();
        $modelCoreConfig = new CoreConfig();

        $userSpaces = $this->getUserSpaces($_SESSION["id_user"]);

        return $this->render(array(
            'lang' => $lang,
            'iconType' => $modelCoreConfig->getParam("space_icon_type"),
            'showSubBar' => $showSubBar,
            'items' => $items,
            'mainSubMenus' => $mainSubMenus,
            'title' => $title,
            // multi-tenant feature: used as a condition to display or not a join button within a space tile
            'userSpaces' => $userSpaces
        ), "indexAction");
    }
    
    public function docAction(){
        
        return $this->render(array(
            "lang" => $this->getLanguage()
        ));
    }


    /**
     * gherve: function added for multi-tenant feature
     * 
     * @param int $id_user
     * @return array of space ids from which user is a member
     */
    public function getUserSpaces($id_user) {
        /*
        To be improved:
        problem: CoreSpaceUser()->getUserSpaceInfo($id_user) returns only one line from core_j_spaces_user!
        hypothesis: because there's no primary (composite in this case) key?
        Temporary solution: we had to use CoreSpaceUser()->getUserSpaceInfo2($id_space, $id_user))
        */
        $spaceUser = new CoreSpaceUser();
        $coreSpace = new CoreSpace();
        $modelSpacePending = new CorePendingAccount();
        $existingSpaces = $coreSpace->getSpaces("id");
        $userSpaceIds = array();
        foreach ($existingSpaces as $space) {
            try {
                if (
                    $spaceUser->getUserSpaceInfo2($space["id"], $id_user)
                    || $modelSpacePending->getBySpaceIdAndUserId($space["id"], $id_user)
                ) {
                    array_push($userSpaceIds, $space["id"]);
                }
            } catch (exception $e) {
                Configuration::getLogger()->debug('CoretilesController', ["exception" => $e]);
                continue;
            }
        }
        return $userSpaceIds;
    }

    /**
     * gherve: function added for multi-tenant feature
     * 
     * Manage actions resulting from user request to join a space
     *
     * @param int $id_space 
     * @param int $id_user
     */
    public function joinSpaceAction($id_space, $id_user) { 
        // missing feature: send an email to space admin ?
        Configuration::getLogger()->debug('JOINING', ["space" => $id_space]);
        $lang = $this->getLanguage();
        $modelSpacePending = new CorePendingAccount();
        $modelSpacePending->add($id_user, $id_space);
        $_SESSION["message"] = CoreTranslator::JoinRequest($lang);
        $this->redirect("coretiles");
    }

}
