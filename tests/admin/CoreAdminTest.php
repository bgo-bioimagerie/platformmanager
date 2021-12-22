<?php

use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Confidence;
use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/core/Controller/CorespaceadminController.php';
require_once 'Modules/core/Controller/CorespaceaccessController.php';
require_once 'Modules/core/Controller/CoremainmenuController.php';
require_once 'Modules/resources/Controller/ResourcesconfigController.php';
require_once 'Modules/resources/Controller/ReareasController.php';
require_once 'Modules/resources/Controller/RecategoriesController.php';
require_once 'Modules/resources/Controller/RevisasController.php';
require_once 'Modules/resources/Controller/ResourcesinfoController.php';
require_once 'Modules/resources/Controller/ResourcesController.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CorePendingAccount.php';


class CoreTest extends TestCase
{
    private static $allSpaces = [];
    private static $allUsers = [];
    private static $allManagers = [];

    private function asAdmin($id_space=0) {
        $u = $this->asUser(Configuration::get('admin_user', 'pfmadmin'), $id_space);
        $_SESSION['user_status'] = CoreStatus::$ADMIN;
        return $u;
    }

    private function asUser($name, $id_space) {
        $m = new CoreUser();
        $users = $m->getAll();
        foreach($users as $user){
            if($user['login'] == $name) {
                $_SESSION['user_status'] = CoreStatus::$USER;
                $_SESSION['id_user'] = $user['id'];
                $_SESSION['id_space'] = $id_space;
                $_SESSION["user_settings"] = ["language" => "en"];                
                return $user;
            }
        }
        return null;
    }

    public function testInstallAndCoreAccess()
    {
        $req = new Request(["path" => ""], true);
        $c = new CoreconnectionController($req);
        $c->indexAction();
        $m = new CoreUser();
        $users = $m->getAll();
        $this->assertTrue($users && !empty($users));
        foreach($users as $user){
            self::$allUsers[] = ["name" => $user['login'], "id" => intval($user['id'])];
        }


    }

    protected function setUp(): void {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '';
    }

    public function testCreateSpace() {
        $spaceName = uniqid();
        $this->asAdmin();
        $req = new Request([
            "path" => "spaceadminedit/0",
            "formid" => "corespaceadminedit",
            "name" => $spaceName,
            "contact" => "admin",
            "status" => 1,
            "color" => "#000000",
            "txtcolor" => "#ffffff",
            "support" => "",
            "description" => "",
            "admins" => []
        ], true);
        $c = new CorespaceadminController($req);
        $c->editAction(0);
        $m = new CoreSpace();
        $spaces = $m->getSpaces('id');
        $spaceId = 0;
        foreach($spaces as $space){
            if ($space['name'] == $spaceName) {
                $spaceId = $space['id'];
            }
        }
        $this->assertTrue($spaceId > 0);
        self::$allSpaces[] = ["name" => $spaceName, "id" => $spaceId];
    }

    public function testCreateMenus() {
        $this->asAdmin();

        $menuName = uniqid();
        $req = new Request([
            "path" => "coremainmenuedit/0",
            "formid" => "editmainmenuform",
            "name" => $menuName,
            "display_order" => 0
        ], true);
        $c = new CoremainmenuController($req);
        $c->editAction(0);

        $menuModel = new CoreMainMenu();
        $menus = $menuModel->getAll();

        $subMenuName = uniqid();
        $req = new Request([
            "path" => "coresubmenuedit/0",
            "formid" => "editmainsubmenuform",
            "name" => $subMenuName,
            "id_main_menu" => $menus[0]['id'],
            "display_order" => 0
        ], true);
        $c = new CoremainmenuController($req);
        $c->submenueditAction(0);

        $subMenuModel = new CoreMainSubMenu();
        $subMenus = $subMenuModel->getAll();

        foreach (self::$allSpaces as $space) {
            $itemMenuName = uniqid();
            $req = new Request([
                "path" => "coremainmenuitemedit/0",
                "formid" => "editmenuitemform",
                "name" => $itemMenuName,
                "id_sub_menu" => $subMenus[0]['id'],
                "id_space" => $space['id'],
                "display_order" => 0
            ], true);
            $c = new CoremainmenuController($req);
            $c->itemeditAction(0);
        }
        $itemMenuModel = new CoreMainMenuItem();
        $itemMenus = $itemMenuModel->getAll();
        $this->assertFalse(empty($itemMenus));
    }

    public function testCreateUser() {
        $space = self::$allSpaces[0];
        for($i=0;$i<3;$i++){
            $userName = uniqid();
            $this->asAdmin();
            $req = new Request([
                "path" => "corespaceaccessuseradd/".$space['id'],
                "formid" => "createuseraccountform",
                "name" => $userName,
                "firstname" => $userName,
                "login" =>  $userName,
                "email" => $userName."@pfm.org",
                "phone" => ""
            ], true);
            $c = new CorespaceaccessController($req);
            $c->useraddAction($space['id']);
            $userId = 0;
            $m = new CoreUser();
            $users = $m->getAll();
            $userId = 0;
            foreach($users as $user){
                if ($user['login'] == $userName) {
                    $userId = $user['id'];
                }
            }
            $this->assertTrue($userId > 0);
            self::$allUsers[] = ["login" => $userName, "id" => intval($userId)];

            $sm = new CoreSpace();
            $role = $sm->getUserSpaceRole($space['id'], $userId);
            $this->assertEquals(-1, $role);
            $pm = new CorePendingAccount();
            $this->assertTrue($pm->isActuallyPending($space['id'], $userId));
        }
    }

    public function testActivatePendingUserAsUser() {
        $this->asAdmin();

        $space = self::$allSpaces[0];
        $user = self::$allUsers[1];
        $pm = new CorePendingAccount();
        $pendings = $pm->getBySpaceIdAndUserId($space['id'], $user['id']);

        $req = new Request([
            "path" => "corespacependinguseredit/".$space['id']."/".$pendings['id'],
            "formid" => "pendingusereditactionform",
            "role" => CoreSpace::$USER
        ], true);


        $c = new CorespaceaccessController($req);
        $c->pendingusereditAction($space['id'], $pendings['id']);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($space['id'], $user['id']);
        $this->assertEquals(CoreSpace::$USER, $role);
        $pm = new CorePendingAccount();
        $this->assertFalse($pm->isActuallyPending($space['id'], $user['id']));
    }

    public function testActivatePendingUserAsSpaceManager() {
        $this->asAdmin();

        $space = self::$allSpaces[0];
        $user = self::$allUsers[2];
        $pm = new CorePendingAccount();
        $pendings = $pm->getBySpaceIdAndUserId($space['id'], $user['id']);

        $req = new Request([
            "path" => "corespacependinguseredit/".$space['id']."/".$pendings['id'],
            "formid" => "pendingusereditactionform",
            "role" => CoreSpace::$MANAGER
        ], true);


        $c = new CorespaceaccessController($req);
        $c->pendingusereditAction($space['id'], $pendings['id']);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($space['id'], $user['id']);
        $this->assertEquals(CoreSpace::$MANAGER, $role);
        $pm = new CorePendingAccount();
        $this->assertFalse($pm->isActuallyPending($space['id'], $user['id']));
        self::$allManagers[] = ['login' => $user['login'], 'id' => $user['id'], 'space'=> $space['id']];
    }

    public function testActivatePendingUserAsSpaceAdmin() {
        $this->asAdmin();

        $space = self::$allSpaces[0];
        $user = self::$allUsers[3];
        $pm = new CorePendingAccount();
        $pendings = $pm->getBySpaceIdAndUserId($space['id'], $user['id']);

        $req = new Request([
            "path" => "corespacependinguseredit/".$space['id']."/".$pendings['id'],
            "formid" => "pendingusereditactionform",
            "role" => CoreSpace::$ADMIN
        ], true);


        $c = new CorespaceaccessController($req);
        $c->pendingusereditAction($space['id'], $pendings['id']);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($space['id'], $user['id']);
        $this->assertEquals(CoreSpace::$ADMIN, $role);
        $pm = new CorePendingAccount();
        $this->assertFalse($pm->isActuallyPending($space['id'], $user['id']));
        self::$allManagers[] = ['login' => $user['login'], 'id' => $user['id'], 'space'=> $space['id']];
    }
    
    public function testConfigureModuleResources() {
        $user = self::$allManagers[1];
        $this->asUser($user['login'], $user['space']);
        // activate resources module
        $req = new Request([
            "path" => "resourcesconfig/".$user['space'],
            "formid" => "menusactivationForm",
            "resourcesmenustatus" => 3,
            "displayMenu" => '',
            "displayColor" =>  "#000000",
            "displayTxtColor" => "#ffffff"
        ], true);
        $c = new ResourcesconfigController($req);
        $c->indexAction($user['space']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$user["space"]], false));
        $spaceView = $c->viewAction($user["space"]);
        $resourcesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'resources') {
                $resourcesEnabled = true;
            }
        }
        $this->assertTrue($resourcesEnabled);
        // Create area
        $req = new Request([
            "path" => "reareasedit/".$user['space']."/0",
            "formid" => "reareasedit/".$user['space'],
            "id" => 0,
            "name" => "rearea1",
            "is_restricted" => 0
        ], false);
        $c = new ReareasController($req);
        $c->editAction($user['space'], 0);
        $data = $c->indexAction($user['space']);
        $reareas = $data['reareas'];
        $this->assertFalse(empty($reareas));
        // Create category
        $req = new Request([
            "path" => "recategoriesedit/".$user['space']."/0",
            "formid" => "recategoriesedit/".$user['space'],
            "id" => 0,
            "name" => "cat1",
        ], false);
        $c = new RecategoriesController($req);
        $c->editAction($user['space'], 0);
        $data = $c->indexAction($user['space']);
        $categories = $data['recategories'];
        $this->assertFalse(empty($categories));

        // Create visa
        $req = new Request([
            "path" => "resourceseditvisa/".$user['space']."/0",
            "formid" => "formeditVisa",
            "id" => 0,
            "id_resource_category" => $categories[0]['id'],
            "id_instructor" => $user['id'],
            "is_active" => 1,
            "instructor_status" => 1
        ], false);
        $c = new RevisasController($req);
        $visa = $c->editAction($user['space'], 0);
        $this->assertTrue($visa['revisa']['id'] > 0);
        $data = $c->indexAction($user['space']);
        $visas = $data['revisas'];
        $this->assertFalse(empty($visas));    
        // Create 2 resource
        for($i=0;$i<2;$i++) {
            $req = new Request([
                "path" => "resourcesedit/".$user['space']."/0",
                "formid" => "resourcesedit",
                "id" => 0,
                "name" => "res".$i,
                "brand" => "",
                "type" => "",
                "description" => "testing",
                "long_description" => "long testing",
                "id_category" => $categories[0]['id'],
                "id_area" => $reareas[0]['id'],
                "display_order" => 0
            ], false);
            $c = new ResourcesinfoController($req);
            $resource = $c->editAction($user['space'], 0);
            $this->assertTrue($resource['resource']['id'] > 0);
        }
        $data = $c->indexAction($user['space']);
        $resources = $data['resources'];
        $this->assertFalse(empty($resources));
        $this->assertEquals(2, count($resources));
    }



}

