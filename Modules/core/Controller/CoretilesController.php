<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Email.php';

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
        $userSpaces = $this->getUserSpaces();

        return $this->render(array(
            'lang' => $lang,
            'iconType' => $modelCoreConfig->getParam("space_icon_type"),
            'showSubBar' => $showSubBar,
            'items' => $items,
            'mainSubMenus' => $mainSubMenus,
            'title' => $title,
            'userSpaces' => $userSpaces['userSpaceIds'],
            'userPendingSpaces' => $userSpaces['userPendingSpaceIds'],
            'spacesUserIsAdminOf' => $userSpaces['spacesUserIsAdminOf']
        ), "indexAction");
    }
    
    public function docAction(){
        
        return $this->render(array(
            "lang" => $this->getLanguage()
        ));
    }


    /**
     * Distinctly list spaces:
     * - of which user is member
     * - in which user has a pending request to join
     * - of which user is admin
     * 
     * @return array of arrays: [userSpaceIds, userPendingSpaceIds, SpacesUserIsAdminOf]
     */
    public function getUserSpaces() {
        $modelSpacePending = new CorePendingAccount();
        $data = $modelSpacePending->getSpaceIdsForPending($_SESSION["id_user"]);
        $userPendingSpaceIds = array();

        if ($data && count($data) > 0) {
            foreach ($data as $space) {
                array_push($userPendingSpaceIds, $space["id_space"]);
            }
        }

        $modelSpaceUser = new CoreSpaceUser();
        $data = $modelSpaceUser->getUserSpaceInfo($_SESSION["id_user"]);
        $userSpaceIds = array();
        $spacesUserIsAdminOf = array();

        if ($data && count($data) > 0) {
            foreach ($data as $space) {
                array_push($userSpaceIds, $space["id_space"]);
                if ($space["status"] === "4") {
                    array_push($spacesUserIsAdminOf, $space["id_space"]);
                }
            }
        }

        return array(
            "userSpaceIds" => $userSpaceIds,
            "userPendingSpaceIds" => $userPendingSpaceIds,
            "spacesUserIsAdminOf" => $spacesUserIsAdminOf
        );
    }

    /**
     * 
     * Manage actions resulting from user request to join or leave a space
     * If user is a member of space, then leaves, else join
     *
     * @param int $id_space
     * @param bool $isMemberOfSpace
     */
    public function selfJoinSpaceAction($space_id) {
        $modelSpaceUser = new CoreSpaceUser();
        $id_user = $_SESSION["id_user"];
        $isMemberOfSpace = $modelSpaceUser->exists($id_user, $space_id);

        if ($isMemberOfSpace) {
            // User is already member of space
            $modelSpaceUser = new CoreSpaceUser();
            $modelSpaceUser->delete($id_user, $space_id);
        } else {
            // User is not member of space
            $modelSpacePending = new CorePendingAccount();
            $isPending = $modelSpacePending->isActuallyPending($id_user, $space_id);

            if (!$isPending) {
                // User hasn't already an unanswered request to join
                $spaceModel = new CoreSpace();
                $spaceName = $spaceModel->getSpaceName($space_id);

                if ($modelSpacePending->exists($space_id, $id_user)) {
                    // This user is already associated to this space in database
                    $pendingId = $modelSpacePending->getBySpaceIdAndUserId($space_id, $id_user)["id"];
                    $modelSpacePending->invalidate($pendingId, NULL);
                } else {
                    // This user is not associated to this space in database
                    $modelSpacePending->add($id_user, $space_id);
                }

                $mailParams = [
                    "id_space" => $space_id,
                    "space_name" => $spaceName
                ];
                $email = new Email();
                $email->NotifyAdminsByEmail($mailParams, "new_join_request", $this->getLanguage());
            }
        } 
        $this->redirect("coretiles");
    }

}
