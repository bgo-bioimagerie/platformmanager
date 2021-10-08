<?php
use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/core/Controller/CorespaceadminController.php';
require_once 'Modules/core/Controller/CorespaceaccessController.php';
require_once 'Modules/core/Controller/CoremainmenuController.php';

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

    private function asAdmin($id_space=0) {
        return $this->asUser(Configuration::get('admin_user', 'pfmadmin'), $id_space);
    }

    private function asUser($name, $id_space) {
        for($i=0;$i<count(self::$allUsers);$i++) {
            if(self::$allUsers[$i]['name'] == $name) {
                $_SESSION['id_user'] = self::$allUsers[$i]['id'];
                $_SESSION['id_space'] = $id_space;
                $_SESSION["user_settings"] = ["language" => "en"];                
                return self::$allUsers[$i];
            }
        }
        return null;
    }

    public function testInstallAndCoreAccess()
    {
        $req = new Request(["path" => ""], true);
        $c = new CoreconnectionController($req);
        $res = $c->indexAction();
        $this->assertTrue(isset($res['metadesc']));

        $m = new CoreUser();
        $users = $m->getAll();
        foreach($users as $user){
            self::$allUsers[] = ["name" => $user['login'], "id" => intval($user['id'])];
        }


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
            "color" => "#154186",
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
        $userName = uniqid();
        $this->asAdmin();
        $req = new Request([
            "path" => "corespaceaccessuseradd/".$space['id'],
            "formid" => "createuseraccountform",
            "name" => $userName,
            "firstname" => $userName,
            "login" =>  $userName,
            "email" => "",
            "phone" => ""
        ], true);
        $c = new CorespaceaccessController($req);
        $c->useraddAction($space['id']);
        $userId = 0;
        $m = new CoreUser();
        $users = $m->getAll();
        $userId = 0;
        foreach($users as $user){
            if ($user['name'] == $userName) {
                $userId = $user['id'];
            }
        }
        $this->assertTrue($userId > 0);
        self::$allUsers[] = ["name" => "user1", "id" => intval($userId)];

        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($space['id'], $userId);
        $this->assertEquals(-1, $role);
        $pm = new CorePendingAccount();
        $this->assertTrue($pm->isActuallyPending($space['id'], $userId));
    }

    public function testActivatePendingUserAsUser() {
        $this->asAdmin();

        $space = self::$allSpaces[0];
        $user = self::$allUsers[0];
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
}

