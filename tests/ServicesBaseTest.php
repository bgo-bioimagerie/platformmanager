<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/services/Controller/ServicesconfigController.php';
require_once 'Modules/services/Controller/ServiceslistingController.php';

require_once 'tests/BaseTest.php';

class ServicesBaseTest extends BaseTest {

    protected function activateServices($space, $user) {
        Configuration::getLogger()->debug('activate services', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "menusactivationForm",
            "servicesmenustatus" => 3,
            "displayMenu" => 0,
            "displayColor" =>  "#000000",
            "displayColorTxt" => "#ffffff"
        ], false);
        $c = new ServicesconfigController($req);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false));
        $spaceView = $c->viewAction($space['id']);
        $invoicesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'services') {
                $invoicesEnabled = true;
            }
        }
        $this->assertTrue($invoicesEnabled);


        $req = new Request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "periodCommandForm",
            "servicesuseproject" => 1,
            "servicesusecommand" => 1,
        ], false);
        $c = new ServicesconfigController($req);
        $c->indexAction($space['id']);

        $req = new Request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "stockForm",
            "servicesusestock" => 1,
        ], false);
        $c = new ServicesconfigController($req);
        $c->indexAction($space['id']);

    }

    protected function createServices($space, $user, string $service) {
        Configuration::getLogger()->debug('create services', ['user' => $user, 'space' => $space, 'service' => $service]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "servicesedit/".$space['id'],
            "formid" => "editserviceform",
            "name" => $service,
            "description" => "new service",
            "display_order" => "",
            "type_id" => 1  // quantity

        ], false);
        $c = new ServiceslistingController($req);
        $data = $c->editAction($space['id'], '');
        $this->assertTrue($data['service']['id'] > 0);
    }

    



}


?>