<?php
use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Controller/CorespaceadminController.php';

class CoreTest extends TestCase
{
    private static $allSpaces = [];

    private function asAdmin($id_space=0) {
        $_SESSION['id_user'] = 1;
        $_SESSION['id_space'] = $id_space;
        $_SESSION["user_settings"] = ["language" => "en"];

    }

    public function testInstallAndCoreAccess()
    {
        $req = new Request(["path" => ""], true);
        $c = new CoreconnectionController($req);
        $res = $c->indexAction();
        $this->assertTrue(isset($res['metadesc']));
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
        self::$allSpaces['test1'] = $spaceId;

    }
}

