<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/documents/Controller/DocumentsconfigController.php';
require_once 'Modules/documents/Controller/DocumentslistController.php';


require_once 'tests/BaseTest.php';

class DocumentsBaseTest extends BaseTest {

    protected function activateDocuments($space, $user) {
        Configuration::getLogger()->debug('activate documentss', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "documentsconfig/".$space['id'],
            "formid" => "documentsmenusactivationForm",
            "documentsMenustatus" => 3,
            "documentsDisplayMenu" => 0,
            "documentsDisplayColor" =>  "#000000",
            "documentsDisplayColorTxt" => "#ffffff"
        ], false);
        $c = new DocumentsconfigController($req, $space);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false), $space);
        $spaceView = $c->viewAction($space['id']);
        $documentsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'documents') {
                $documentsEnabled = true;
            }
        }
        $this->assertTrue($documentsEnabled);

    }

}


?>