<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/helpdesk/Controller/HelpdeskconfigController.php';
require_once 'Modules/helpdesk/Controller/HelpdeskController.php';


require_once 'tests/BaseTest.php';

class HelpdeskBaseTest extends BaseTest {

    protected function activateHelpdesk($space, $user) {
        Configuration::getLogger()->debug('activate helpdesk', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "helpdeskconfig/".$space['id'],
            "formid" => "helpdeskmenusactivationForm",
            "helpdeskMenustatus" => 3,
            "helpdeskDisplayMenu" => 0,
            "helpdeskDisplayColor" =>  "#000000",
            "helpdeskDisplayColorTxt" => "#ffffff"
        ]);
        /*
        $req = new Request([
            "path" => "helpdeskconfig/".$space['id'],
            "formid" => "helpdeskmenusactivationForm",
            "helpdeskMenustatus" => 3,
            "helpdeskDisplayMenu" => 0,
            "helpdeskDisplayColor" =>  "#000000",
            "helpdeskDisplayColorTxt" => "#ffffff"
        ], false);
        */
        $c = new HelpdeskconfigController($req, $space);
        //$c->indexAction($space['id']);
        $c->runAction('helpdesk', 'index', [$space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        // $req = new Request(["path" => "corespace/".$space['id']], false);
        $c = new CorespaceController($req, $space);
        //$spaceView = $c->viewAction($space['id']);
        $spaceView = $c->runAction('core', 'view', [$space['id']]);
        $enabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'helpdesk') {
                $enabled = true;
            }
        }
        $this->assertTrue($enabled);

    }

}


?>