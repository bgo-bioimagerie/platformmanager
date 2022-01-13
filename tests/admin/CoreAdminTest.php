<?php

use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/core/Controller/CorespaceadminController.php';
require_once 'Modules/core/Controller/CorespaceaccessController.php';
require_once 'Modules/core/Controller/CoremainmenuController.php';
require_once 'Modules/core/Controller/CoreusersController.php';


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
        $this->asAdmin();
        $req = $this->request(["path" => ""]);
        $c = new CoreconnectionController($req);
        $c->runAction('core', 'index');
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
            $req = $this->request([
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
            ]);
            $c = new CorespaceadminController($req);
            $data = $c->runAction('core', 'edit', ['id_space' => 0]);
            $newSpace = $data['space'];
            $this->assertTrue($newSpace['id'] > 0);
            $this->assertNotFalse($this->space($spaceName));
            $req = $this->request([
                "path" => "spaceadminedit/".$newSpace['id'],
            ]);
            $c = new CorespaceadminController($req);
            $data = $c->runAction('core', 'edit', ['id_space' => $newSpace['id']]);
            $this->assertTrue($data['space']['name'] == $spaceName);
        }
        // list spaces
        $req = $this->request([
            "path" => "spaceadmin",
        ]);
        $c = new CorespaceadminController($req);
        $data = $c->runAction('core', 'index');
        $this->assertTrue(!empty($data['spaces']));
    }

    public function testCreateMenus() {
        $this->asAdmin();

        $menuName = 'menu1';
        $req = $this->request([
            "path" => "coremainmenuedit/0",
            "formid" => "editmainmenuform",
            "name" => $menuName,
            "display_order" => 0
        ]);
        $c = new CoremainmenuController($req);
        $c->runAction('core', 'edit', ['id' => 0]);

        $menuModel = new CoreMainMenu();
        $menus = $menuModel->getAll();

        $subMenuName = 'submenu1';
        $req = $this->request([
            "path" => "coresubmenuedit/0",
            "formid" => "editmainsubmenuform",
            "name" => $subMenuName,
            "id_main_menu" => $menus[0]['id'],
            "display_order" => 0
        ]);
        $c = new CoremainmenuController($req);
        $c->runAction('core', 'submenuedit', ['id' => 0]);

        $subMenuModel = new CoreMainSubMenu();
        $subMenus = $subMenuModel->getAll();

        $spaces = $this->spaces();
        foreach ($spaces as $space) {
            $itemMenuName = $space['name'];
            $req = $this->request([
                "path" => "coremainmenuitemedit/0",
                "formid" => "editmenuitemform",
                "name" => $itemMenuName,
                "id_sub_menu" => $subMenus[0]['id'],
                "id_space" => $space['id'],
                "display_order" => 0
            ]);
            $c = new CoremainmenuController($req);
            $c->runAction('core', 'itemedit', ['id' => 0]);
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
                $req = $this->request([
                    "path" => "corespaceaccessuseradd/".$space['id'],
                    "formid" => "createuseraccountform",
                    "name" => $userName,
                    "firstname" => $userName,
                    "login" =>  $userName,
                    "email" => $userName."@pfm.org",
                    "phone" => ""
                ]);
                $c = new CorespaceaccessController($req);
                $data = $c->runAction('core', 'useradd', ['id_space' => $space['id']]);
                $newUser = $data['user'];
                $this->assertTrue($newUser && $newUser['id'] > 0);
    
                $sm = new CoreSpace();
                $role = $sm->getUserSpaceRole($space['id'], $newUser['id']);
                $this->assertEquals(-1, $role);
                $pm = new CorePendingAccount();
                $this->assertTrue($pm->isActuallyPending($space['id'], $newUser['id']));

            }
        }

        // list users
        $req = $this->request([
            "path" => "coreusers",
        ]);
        $c = new CoreusersController($req);
        $data = $c->runAction('core', 'index');
        $this->assertTrue(!empty($data['users']));
        foreach($data['users'] as $user){
            if($user['name'] == 'user1') {
                $req = $this->request([
                    "path" => "coreusersedit/".$user['id'],
                ]);
                $c = new CoreusersController($req);
                $data = $c->runAction('core', 'edit', ['id' => $user['id']]);
                $this->assertTrue($data['user']['name'] == 'user1');
                break;
            }
        }
    }


    public function activatePendingAs($space_id, $user_id, $role) {
        $pm = new CorePendingAccount();
        $pendings = $pm->getBySpaceIdAndUserId($space_id, $user_id);

        $req = $this->request([
            "path" => "corespacependinguseredit/".$space_id."/".$pendings['id'],
            "formid" => "pendingusereditactionform",
            "role" => $role
        ]);

        $c = new CorespaceaccessController($req);
        $c->runAction('core', 'pendinguseredit', ['id_space' => $space_id, 'id' => $pendings['id']]);
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

