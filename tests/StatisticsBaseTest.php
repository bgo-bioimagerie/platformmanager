<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/statistics/Controller/StatisticsconfigController.php';


require_once 'tests/BaseTest.php';

class StatisticsBaseTest extends BaseTest {

    protected function activateStatistics($space, $user) {
        Configuration::getLogger()->debug('activate stats', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "statisticsconfig/".$space['id'],
            "formid" => "statisticsmenusactivationForm",
            "statisticsMenustatus" => 3,
            "statisticsDisplayMenu" => 0,
            "statisticsDisplayColor" =>  "#000000",
            "statisticsDisplayColorTxt" => "#ffffff"
        ]);
        $c = new statisticsconfigController($req, $space);
        $c->runAction('statistics', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $statisticsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'statistics') {
                $statisticsEnabled = true;
            }
        }
        $this->assertTrue($statisticsEnabled);

    }

}


?>