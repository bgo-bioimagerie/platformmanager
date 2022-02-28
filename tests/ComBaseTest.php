<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/com/Controller/ComconfigController.php';
require_once 'Modules/com/Controller/ComnewsController.php';


require_once 'tests/BaseTest.php';

class ComBaseTest extends BaseTest {

    protected function activateCom($space, $user) {
        Configuration::getLogger()->debug('activate com', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "comconfig/".$space['id'],
            "formid" => "commenusactivationForm",
            "comMenustatus" => 2,
            "comDisplayMenu" => 0,
            "comDisplayColor" =>  "#000000",
            "comDisplayColorTxt" => "#ffffff"
        ]);
        $c = new ComconfigController($req, $space);
        $c->runAction('com', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $comEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'com') {
                $comEnabled = true;
            }
        }
        $this->assertTrue($comEnabled);

    }

}


?>