<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CorespaceController extends CoresecureController {

     private $spaceModel;
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
        }
        $this->spaceModel = new CoreSpace ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        //$lang = $this->getLanguage();
        //$this->render(array("lang" => $lang, "id_space" => $id_space));
    }

    /**
     * 
     * @param type $id_space
     */
    public function viewAction($id_space) {
        
        $space = $this->spaceModel->getSpace($id_space);
        
        $lang = $this->getLanguage();
        $showAdmMenu = false;
        if ($_SESSION['user_status'] > CoreStatus::$USER){
            $spaceMenuItems = $this->spaceModel->getSpaceMenus($space["id"], CoreSpace::$ADMIN);
            $showAdmMenu = true;
        }
        else{
            $role = $this->spaceModel->getUserSpaceRole($space["id"], $_SESSION['id_user']);
            if ($role > CoreSpace::$MANAGER){
                $showAdmMenu = true;
            }
            $spaceMenuItems = $this->spaceModel->getSpaceMenus($space["id"], $role);
        }

        $this->render(array("lang" => $lang, "id_space" => $id_space, "space" => $space, "spaceMenuItems" => $spaceMenuItems, "showAdmMenu" => $showAdmMenu));
    }

    /**
     * 
     * @param type $id_space
     */
    public function configAction($id_space){
        
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $space = $this->spaceModel->getSpace($id_space);
        $modulesTable = $this->configModulesTable($lang, $id_space);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "space" => $space, "modulesTable" => $modulesTable));
    
    }
    
    /**
     * 
     * @param type $id_space
     */
    public function configusersAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // space info
        $space = $this->spaceModel->getSpace($id_space);

        // user form
        $userForm = $this->configUsersForm($lang, $id_space);
        if ($userForm->check()) {
            $id_space = $this->request->getParameter("id_space");
            $id_user = $this->request->getParameter("id_user");
            $id_role = $this->request->getParameter("id_role");
            $this->spaceModel->setUser($id_user, $id_space, $id_role);
            $this->redirect("spaceconfiguser/" . $id_space);
        }

        
        $userTable = $this->configUsersTable($lang, $id_space);
        $this->render(array("lang" => $lang, "id_space" => $id_space, "space" => $space, "userForm" => $userForm->getHtml($lang), "userTable" => $userTable));
    }

    /**
     * 
     * @param type $lang
     * @param type $id_space
     * @return type
     */
    protected function configModulesTable($lang, $id_space){
        
        $modelInstalledMod = new CoreInstalledModules();
        $modules = $modelInstalledMod->getModules();
        
        $mods = array();
        $count = -1;
        for ($i = 0; $i < count($modules); ++$i) {

            $moduleName = ucfirst(strtolower($modules[$i]["name"]));
            $abstractMethod = $moduleName . "ConfigAbstract";
            $configFile = "Modules/" . $modules[$i]["name"] . "/Controller/" . $moduleName . "configController.php";
            if (file_exists($configFile)) {
                $count++;
                // name
                $mods[$count]['name'] = $modules[$i]["name"];
                if ($modules[$i]["name"] != "core"){
                    require_once "Modules/" . $modules[$i]["name"] . "/Model/" . $moduleName . "Translator.php" ;
                }
                // get abstract html text
                $mods[$count]['abstract'] = forward_static_call(array($moduleName . "Translator", $abstractMethod), $lang);
                // construct action
                $action = $modules[$i]["name"] . "config";
                $mods[$count]['action'] = $action;
                $mods[$count]['id'] = $i;
            }
        }

        $headers = array("name" => CoreTranslator::Name($lang),
            "abstract" => CoreTranslator::Description($lang));

        $tableView = new TableView("tableModules");
        $tableView->setTitle(CoreTranslator::Modules_configuration($lang), 3);
        $tableView->addLineEditButton("spaceconfigmodule/".$id_space, "name");
        return $tableView->view($mods, $headers);

    }

    /**
     * 
     * @param type $lang
     * @param type $id_space
     * @return type
     */
    protected function configUsersTable($lang, $id_space) {
        
        $data = $this->spaceModel->getUsers($id_space);
        //print_r($data);
        for( $i = 0 ; $i < count($data) ; $i++){
            if ($data[$i]["role"] == 1){
                $data[$i]["role"] = CoreTranslator::Visitor($lang);
            }
            if ($data[$i]["role"] == 2){
                $data[$i]["role"] = CoreTranslator::User($lang);
            }
            if ($data[$i]["role"] == 3){
                $data[$i]["role"] = CoreTranslator::Manager($lang);
            }
            if ($data[$i]["role"] == 4){
                $data[$i]["role"] = CoreTranslator::Admin($lang);
            }
        }
        
        $tableUsers = new TableView();
        $tableUsers->addDeleteButton("spaceconfigdeleteuser/".$id_space);
        return $tableUsers->view($data, array("name" => CoreTranslator::Name($lang), 
            "firstname" => CoreTranslator::Firstname($lang), 
            "role" => CoreTranslator::Role($lang)));
    }
    
    /**
     * 
     * @param type $id_space
     * @param type $id_user
     */
    public function configdeleteuserAction($id_space, $id_user){
        $this->spaceModel->deleteUser($id_space, $id_user);
        $this->redirect("spaceconfiguser/" . $id_space);
    }

    /**
     * 
     * @param type $lang
     * @param type $id_space
     * @return \Form
     */
    protected function configUsersForm($lang, $id_space) {

        $modeluser = new CoreUser();
        $users = $modeluser->getActiveUsers("name");
        $usersNames = array();
        $usersId = array();
        foreach ($users as $user) {
            $usersNames[] = $user["name"] . " " . $user["firstname"];
            $usersId[] = $user["id"];
        }

        $roles = CoreSpace::roles($lang);

        $formUser = new Form($this->request, "adduser");
        $formUser->setColumnsWidth(3, 6);
        $formUser->setButtonsWidth(2, 8);
        $formUser->addHidden("id_space", $id_space);
        $formUser->addSelect("id_user", CoreTranslator::User($lang), $usersNames, $usersId);
        $formUser->addSelect("id_role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"]);
        $formUser->setValidationButton(CoreTranslator::Ok($lang), "spaceconfiguser/" . $id_space);
        return $formUser;
    }

    /**
     * 
     * @param type $id_space
     * @return type
     */
    public function spaceName($id_space){
        $space = $this->spaceModel->getSpace($id_space);
        return $space["name"];
    }
    
    /**
     * 
     * @param type $id_space
     * @return string
     */
    public function menu($id_space) {

        $space = $this->spaceModel->getSpace($id_space);
        
        $lang = $this->getLanguage();
        $showAdmMenu = false;
        if ($_SESSION['user_status'] > CoreStatus::$USER){
            $spaceMenuItems = $this->spaceModel->getSpaceMenus($space["id"], CoreSpace::$ADMIN);
            $showAdmMenu = true;
        }
        else{
            $role = $this->spaceModel->getUserSpaceRole($space["id"], $_SESSION['id_user']);
            if ($role > CoreSpace::$MANAGER){
                $showAdmMenu = true;
            }
            $spaceMenuItems = $this->spaceModel->getSpaceMenus($space["id"], $role);
        }
        
        //$spaceMenuItems = array("spaceconfig/" . $space["id"] => CoreTranslator::Configuration($lang),
        //    "spaceconfiguser/" . $space["id"] => CoreTranslator::Access($lang));

        //$html = "<h3 style=\"text-align:center;\">" . $space["name"] . "</h3>";
        
        $html = "<div class=\"pm-space-menu\">";
        $html .= "<div class=\"bs-glyphicons\">";
        $html .= "<ul class=\"bs-glyphicons-list\">";
        
        if ($showAdmMenu){
                        $html .= "<li>";
            $html .= "<a href=\"" . "spaceconfig/" . $space["id"] . "\">";
            $html .= "<span class=\"glyphicon-class glyphicon glyphicon-cog\" aria-hidden=\"true\"> " . CoreTranslator::Configuration($lang) . "</span>";
            $html .= "</a>";
            $html .= "</li>";
                        $html .= "<li>";
            $html .= "<a href=\"" . "spaceconfiguser/" . $space["id"] . "\">";
            $html .= "<span class=\"glyphicon-class glyphicon glyphicon-cog\" aria-hidden=\"true\"> " . CoreTranslator::Access($lang) . "</span>";
            $html .= "</a>";
            $html .= "</li>";
        }
        
        foreach ($spaceMenuItems as $item) {
            
            $classTranslator = ucfirst($item["module"])."Translator";
            $TranslatorFile = "Modules/" . $item["module"] . "/Model/" . $classTranslator . ".php";
            require_once $TranslatorFile;
            $translator = new $classTranslator();
            $url = $item["url"];
            $name = $translator->$url($lang);
            
            $html .= "<li>";
            $html .= "<a href=\"" . $item["url"] . "/" . $space["id"] . "\">";
            $html .= "<span class=\"glyphicon-class glyphicon ".$item["icon"]."\" aria-hidden=\"true\"> " . $name . "</span>";
            $html .= "</a>";
            $html .= "</li>";
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    /**
     * 
     * @param type $id_space
     * @param type $name_module
     */
    public function configmoduleAction($id_space, $name_module){
        
        $path = $name_module . "config/";
        $this->redirect($path.$id_space);
    }
}
