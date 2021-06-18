<?php
use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Modules/core/Model/CoreInstall.php';


class CoreTest extends TestCase
{
    public function testInstallAndCoreAccess()
    {
        new CoreDB();
        $req = new Request(["path" => "install"], true);
        $c = new CoreconnectionController($req);
        $res = $c->indexAction();
        $this->assertTrue(isset($res['metadesc']));
    }
}

