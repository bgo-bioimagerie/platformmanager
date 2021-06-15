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
        $result = array(
            "userSpaceIds" => $userSpaceIds,
            "userPendingSpaceIds" => $userPendingSpaceIds,
            "spacesUserIsAdminOf" => $spacesUserIsAdminOf
        );

        return $result;
    }

    /**
     * 
     * Manage actions resulting from user request to join or leave a space
     *
     * @param int $id_space
     * @param bool $isMemberOfSpace
     */
    public function joinSpaceAction($id_space, $isMemberOfSpace) {
        if ($isMemberOfSpace) {
            $modelSpaceUser = new CoreSpaceUser();
            $modelSpaceUser->delete($_SESSION["id_user"], $id_space);
        } else {
            $modelSpacePending = new CorePendingAccount();
            $modelSpacePending->add($_SESSION["id_user"], $id_space);
            $params = array($id_space);
            $this->NotifyAdminsByEmail($params, "new_join_request");
        } 
        $this->redirect("coretiles");
    }

    /**
     * 
     * Send an Email to space managers (status > 2) notifying that logged user requested to join space
     *
     * @param array $params required to fill sendEmail() parameters. Depends on why we want to notify space admins
     * @param string $origin determines how to get sendEmail() paramters from $params
     */
    public function NotifyAdminsByEmail($params, $origin) {
        $lang = $this->getLanguage();
        if ($origin === "new_join_request") {
            $spaceModel = new CoreSpace();
            $emailSpaceManagers = $spaceModel->getEmailsSpaceManagers($params["id_space"]);
            $mailer = new MailerSend();
            $mail_from = Configuration::get('smtp_from');
            $from = (!empty($mail_from)) ? $mail_from : "support@platform-manager.com";
            $fromName = "Platform-Manager";
            $spaceName = $spaceModel->getSpace($params["id_space"])["name"];
            $subject = CoreTranslator::JoinRequestSubject($spaceName, $lang);
            $content = CoreTranslator::JoinRequestEmail($_SESSION['login'], $spaceName, $lang);
            foreach ($emailSpaceManagers as $emailSpaceManager) {
                $toAdress = $emailSpaceManager["email"];
                $mailer->sendEmail($from, $fromName, $toAdress, $subject, $content, false);
            }
        } else {
            Configuration::getLogger()->debug("notifyAdminsByEmail", ["message" => "origin parameter is not set properly", "origin" => $origin]);
        }
    }
}
