<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/mailer/Model/MailerSend.php';


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
            'userPendingSpaces' => $userSpaces['userPendingSpaceIds']
        ), "indexAction");
    }
    
    public function docAction(){
        
        return $this->render(array(
            "lang" => $this->getLanguage()
        ));
    }


    /**
     * Get spaces of which user is member or has a pending request to join
     * 
     * @return array of space ids
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
        array_push($result, ["userPendingSpaceIds" => $userPendingSpaceIds]);

        $modelSpaceUser = new CoreSpaceUser();
        $data = $modelSpaceUser->getUserSpaceInfo($_SESSION["id_user"]);
        $userSpaceIds = array();

        if ($data && count($data) > 0) { 
            foreach ($data as $space) {
                array_push($userSpaceIds, $space["id_space"]);
            }
        }
        $result = array("userSpaceIds" => $userSpaceIds, "userPendingSpaceIds" => $userPendingSpaceIds);

        return $result;
    }

    /**
     * 
     * Manage actions resulting from user request to join a space
     *
     * @param int $id_space 
     */
    public function joinSpaceAction($id_space) {
        $modelSpacePending = new CorePendingAccount();
        $modelSpacePending->add($_SESSION["id_user"], $id_space);
        $this->NotifyAdminForJoinRequest($id_space);
        $this->redirect("coretiles");
    }

    /**
     * 
     * Send an Email to space managers (status > 2) notifying that logged user requested to join space
     *
     * @param int $id_space 
     */
    public function NotifyAdminForJoinRequest($id_space) {
        $lang = $this->getLanguage();
        $spaceModel = new CoreSpace();
        $emailSpaceManagers = $spaceModel->getEmailsSpaceManagers($id_space);
        
        $mailer = new MailerSend();
        $mail_from = getenv('MAIL_FROM');
        $from = (!empty($mail_from)) ? $mail_from : "support@platform-manager.com";
        $fromName = "Platform-Manager";
        $spaceName = $spaceModel->getSpace($id_space)["name"];
        $subject = CoreTranslator::JoinRequestSubject($spaceName, $lang);
        $content = CoreTranslator::JoinRequestEmail($_SESSION['login'], $spaceName, $lang);
        foreach ($emailSpaceManagers as $emailSpaceManager) {
            $toAdress = $emailSpaceManager["email"];
            $mailer->sendEmail($from, $fromName, $toAdress, $subject, $content, false);
        }  
    }
}
