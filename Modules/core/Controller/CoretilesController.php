<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Email.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreMainMenu.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreStar.php';

require_once 'Modules/catalog/Model/CaEntry.php';
require_once 'Modules/resources/Model/ResourceInfo.php';


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
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->user = new CoreUser();
        //$this->checkAuthorization(CoreStatus::$USER);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($level = 1, $id = -1) {
        if($level === "") {
            $level = 1;
        }
        if($id === "") { // welcome page
            $id = 0;
        }
        if ( $id < 0 ){
            $_SESSION['flash'] = 'Page not found, sorry...';
            //$this->showMainSubMenu(0);
            $this->redirect('');
            return;
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
        $id_sub = 0;
        if($id > 0) {
            $id_sub = $modelMenu->getFirstSubMenu($id);
        }
        
        $this->showMainSubMenu($id_sub);
    }
    
    public function showMainSubMenu($id){
        
        $modelSubMenu = new CoreMainSubMenu();
        
        if ($id < 0){
            $id = $modelSubMenu->getFirstIdx();
        }

        $modelCoreConfig = new CoreConfig();

        $lang = $this->getLanguage();
        $userSpaces = $this->getUserSpaces();


        if ($id == 0) {
            $starModel = new CoreStar();
            $spaceModel = new CoreSpace();
            
            $spaces = [];
            $logged = false;
            if(isset($_SESSION["id_user"])) {
                if($_SESSION["id_user"] > 0) {
                    $logged = true;
                }
                $starSpaces = $starModel->stars($_SESSION["id_user"]);
                foreach($starSpaces as $starSpace) {
                    $spaces[] = $spaceModel->getSpace($starSpace["id_space"]);
                }
            }
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

            $allSpaces = $spaceModel->getSpaces('id');
            $spaceMap = [];
            $userSpacesOnly = $this->request->getParameterNoException('mine') === '1' ? true: false;
            if($userSpacesOnly) {
                $spaces= [];
            }
            $userSpacesList = [];

            foreach($allSpaces as $space) {
                if($logged && isset($_SESSION['login']) && $userSpacesOnly) {
                    $isMemberOfSpace = (in_array($space["id"], $userSpaces['userSpaceIds'])) ? true : false;
                    if($isMemberOfSpace) {
                        $userSpacesList[] = $space;
                    }
                }
                if ($logged && !in_array($space["id"], $userSpaces['spacesUserIsAdminOf']) && isset($_SESSION["login"])) {
                    if (!in_array($space["id"], $userSpaces['userPendingSpaceIds'])) {
                        $isMemberOfSpace = (in_array($space["id"], $userSpaces['userSpaceIds'])) ? true : false;
                        if(!$isMemberOfSpace) {
                            $space['join'] = CoreTranslator::RequestJoin($isMemberOfSpace, $lang);
                        }
                    } else {
                        $space['join_requested'] = CoreTranslator::JoinRequested($lang);
                    }
                }
                $spaceMap[$space["id"]] = $space; // name, description
            }
            $catalogModel = new CaEntry();
            $catalog = $catalogModel->list();  // title, short_desc, full_desc
            $resourceModel = new ResourceInfo();
            $resources = $resourceModel->getAll(); // name, description, long_description
            
            $modelMainMenus = new CoreMainMenu();
            $mainMenus = $modelMainMenus->getAll();
            usort($mainMenus, function($item1, $item2){
                return $item1['name'] <=> $item2['name'];
            });

            $menuItemsModel = new CoreMainMenuItem();
            $itemsMenusList = $menuItemsModel->getMainMenus();
            $itemsMenus = [];
            foreach($itemsMenusList as $item) {
                $itemsMenus[$item['id']] = $item['name']; 
            }

            return $this->render(array(
                'lang' => $lang,
                'content' => $content,
                'spaces' => $spaces,
                'spaceMap' => $spaceMap,
                'userSpaces' => $userSpacesList,
                'catalog' => $catalog,
                'resources' => $resources,
                'mainSubMenus' => [],
                'mainMenus' => $mainMenus,
                'itemsMenus' => $itemsMenus,
                'iconType' => $modelCoreConfig->getParam("space_icon_type"),
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

        // filter out items with no space
        $refLength = count($items);
        for ($i=0; $i<$refLength; $i++) {
            if (!$items[$i]) {
                unset($items[$i]);
            }
        }

        $starModel = new CoreStar();
        $starList = [];
        if(isset($_SESSION["id_user"]) && $_SESSION["id_user"] > 0) {
            $starList = $starModel->stars($_SESSION["id_user"]);
        }
        $stars = [];
        foreach($starList as $star) {
            $stars[$star["id_space"]] = true;
        }

        return $this->render(array(
            'lang' => $lang,
            'star' => $stars,
            'submenu' => $id,
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

    public function corestarAction($level, $id, $id_space) {
        $starModel = new CoreStar();
        $starModel->star($_SESSION["id_user"], $id_space);
        $this->redirect("coretiles/$level/$id");
    }

    public function coreunstarAction($level, $id, $id_space) {
        $starModel = new CoreStar();
        $starModel->delete($_SESSION["id_user"], $id_space);
        $this->redirect("coretiles/$level/$id");
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
        if(!isset($_SESSION["id_user"]) || $_SESSION["id_user"] <= 0) {
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
                $userFullName = $modelUser->getUserFUllName($id_user);

                $mailParams = [
                    "id_space" => $id_space,
                    "space_name" => $spaceName,
                    "email" => $userEmail,
                    "fullName" => $userFullName
                ];
                $email = new Email();
                $email->notifyAdminsByEmail($mailParams, "new_join_request", $this->getLanguage());
            }
        } 
        $this->redirect("coretiles");
    }

}
