<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Email.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

use League\CommonMark\CommonMarkConverter;


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoretilesController extends CorecookiesecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->user = new CoreUser();
        //$this->checkAuthorization(CoreStatus::$USER);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($level = 1, $id = -1) {
        if ( $id < 0 ){
            $this->redirect("coretilesdoc");
        }
        if ( $level == 0) {
            $this->showMainSubMenu(0);
        }
        else if ( $level == 1 ){
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

        if ($id == 0) {
            $lang = $this->getLanguage();
            $content_files = ['data/welcome_'.$lang.'.md', 'data/welcome_'.$lang.'.html', 'data/welcome.md', 'data/welcome.html'];
            $content = '';
            foreach($content_files as $content_file) {
                if (file_exists($content_file)) {
                    $content = file_get_contents($content_file);
                    if (str_ends_with($content_file, '.md')) {
                        $converter = new CommonMarkConverter([
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]);
                        $content = $converter->convertToHtml($content);
                    }
                    break;
                }
            }
            
            return $this->render(array(
                'lang' => $lang,
                'content' => $content,
                'mainSubMenus' => [],
                'showSubBar' => false
                ), "welcomeAction");
        }
        
        $mainSubMenus = [];
        $showSubBar = false;

        $modelMainMenuItem = new CoreMainMenuItem();
        $mainSubMenus = $modelSubMenu->getForMenu($modelSubMenu->getMainMenu($id));

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
        if(!isset($_SESSION["id_user"])) {
            return array(
                "userSpaceIds" => [],
                "userPendingSpaceIds" => [],
                "spacesUserIsAdminOf" => []
            );
        }
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
    public function selfJoinSpaceAction($id_space) {
        $modelSpaceUser = new CoreSpaceUser();
        $id_user = $_SESSION["id_user"];
        $isMemberOfSpace = $modelSpaceUser->exists($id_user, $id_space);

        if ($isMemberOfSpace) {
            // User is already member of space
            $modelSpaceUser = new CoreSpaceUser();
            // remove user from space members
            $modelSpaceUser->delete($id_space, $id_user);
        } else {
            // User is not member of space
            $modelSpacePending = new CorePendingAccount();
            $isPending = $modelSpacePending->isActuallyPending($id_user, $id_space);

            if (!$isPending) {
                // User hasn't already an unanswered request to join
                $spaceModel = new CoreSpace();
                $spaceName = $spaceModel->getSpaceName($id_space);

                if ($modelSpacePending->exists($id_space, $id_user)) {
                    // This user is already associated to this space in core_pending_account 
                    $pendingId = $modelSpacePending->getBySpaceIdAndUserId($id_space, $id_user)["id"];
                    $pendingObject = $modelSpacePending->get($pendingId);

                    if (intval($pendingObject["validated"]) === 1 && intval($pendingObject["validated_by"]) === 0) {
                        // user has unjoin or has been rejected by space admin
                        $modelSpacePending->updateWhenRejoin($id_user, $id_space);
                    } else {
                        $modelSpacePending->invalidate($pendingId, NULL);
                    }
                } else {
                    // This user is not associated to this space in database
                    $modelSpacePending->add($id_user, $id_space);
                }

                $modelUser = new CoreUser();
                $userEmail = $modelUser->getEmail($id_user);

                $mailParams = [
                    "id_space" => $id_space,
                    "space_name" => $spaceName,
                    "user_email" => $userEmail
                ];
                $email = new Email();
                $email->notifyAdminsByEmail($mailParams, "new_join_request", $this->getLanguage());
            }
        } 
        $this->redirect("coretiles");
    }

}
