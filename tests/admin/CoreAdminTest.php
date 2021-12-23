<?php

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

require_once 'tests/BaseTest.php';


class CoreTest extends BaseTest {

    public function testInstallAndCoreAccess()
    {
        $req = new Request(["path" => ""], true);
        $c = new CoreconnectionController($req);
        $c->indexAction();
        $m = new CoreUser();
        $users = $m->getAll();
        Configuration::getLogger()->debug('[core] users', ['users' => $users]);
        $this->assertTrue($users && !empty($users));
    }

    public function testCreateSpace() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
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
            $data = $c->editAction(0);
            $newSpace = $data['space'];
            $this->assertTrue($newSpace['id'] > 0);
            $this->assertNotFalse($this->space($spaceName));
        }
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

        $spaces = $this->spaces();
        foreach ($spaces as $space) {
            //$itemMenuName = uniqid();
            $itemMenuName = $space['name'];
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
        $this->asAdmin();
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];

        foreach($spaces as $spaceName => $spaceMembers) {
            $usersToAdd = [];
            $space = $this->space($spaceName);
            foreach($spaceMembers['admins'] as $u) {
                $usersToAdd[] = $u;
            }
            foreach($spaceMembers['managers'] as $u) {
                $usersToAdd[] = $u;
            }
            foreach($spaceMembers['users'] as $u) {
                $usersToAdd[] = $u;
            }
            foreach($usersToAdd as $userName) {
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
                $data = $c->useraddAction($space['id']);
                $newUser = $data['user'];
                $this->assertTrue($newUser && $newUser['id'] > 0);
    
                $sm = new CoreSpace();
                $role = $sm->getUserSpaceRole($space['id'], $newUser['id']);
                $this->assertEquals(-1, $role);
                $pm = new CorePendingAccount();
                $this->assertTrue($pm->isActuallyPending($space['id'], $newUser['id']));

            }
        }
    }


    public function activatePendingAs($space_id, $user_id, $role) {
        $pm = new CorePendingAccount();
        $pendings = $pm->getBySpaceIdAndUserId($space_id, $user_id);

        $req = new Request([
            "path" => "corespacependinguseredit/".$space_id."/".$pendings['id'],
            "formid" => "pendingusereditactionform",
            "role" => $role
        ], true);

        $c = new CorespaceaccessController($req);
        $c->pendingusereditAction($space_id, $pendings['id']);
        $sm = new CoreSpace();
        $urole = $sm->getUserSpaceRole($space_id, $user_id);
        $this->assertEquals($role, $urole);
        $pm = new CorePendingAccount();
        $this->assertFalse($pm->isActuallyPending($space_id, $user_id));
    }

    public function testActivatePendingUserAsUser() {
        $this->asAdmin();
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];

        foreach($spaces as $spaceName => $spaceMembers) {
            $space = $this->space($spaceName);
            foreach($spaceMembers['users'] as $u) {
               $user = $this->user($u);
               $this->activatePendingAs($space['id'], $user['id'], CoreSpace::$USER);
            }
        }
    }

    public function testActivatePendingUserAsSpaceManager() {
        $this->asAdmin();
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];

        foreach($spaces as $spaceName => $spaceMembers) {
            $space = $this->space($spaceName);
            foreach($spaceMembers['managers'] as $u) {
               $user = $this->user($u);
               $this->activatePendingAs($space['id'], $user['id'], CoreSpace::$MANAGER);
            }
        }
    }

    public function testActivatePendingUserAsSpaceAdmin() {
        $this->asAdmin();
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];

        foreach($spaces as $spaceName => $spaceMembers) {
            $space = $this->space($spaceName);
            foreach($spaceMembers['admins'] as $u) {
               $user = $this->user($u);
               $this->activatePendingAs($space['id'], $user['id'], CoreSpace::$ADMIN);
            }
        }
    }

}

