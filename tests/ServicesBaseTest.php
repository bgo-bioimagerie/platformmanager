<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/services/Controller/ServicesconfigController.php';
require_once 'Modules/services/Controller/ServiceslistingController.php';
require_once 'Modules/services/Controller/ServicesvisaController.php';
require_once 'Modules/services/Controller/ServicesoriginsController.php';
require_once 'Modules/services/Controller/ServicespurchaseController.php';
require_once 'Modules/services/Controller/ServicesordersController.php';

require_once 'Modules/services/Controller/ServicesprojectsController.php';
require_once 'Modules/services/Controller/StockcabinetController.php';
require_once 'Modules/services/Controller/StockshelfController.php';

require_once 'tests/BaseTest.php';

class ServicesBaseTest extends BaseTest {

    protected function activateServices($space, $user) {
        Configuration::getLogger()->debug('activate services', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "servicesmenusactivationForm",
            "servicesMenustatus" => 3,
            "servicesDisplayMenu" => 0,
            "servicesDisplayColor" =>  "#000000",
            "servicesDisplayColorTxt" => "#ffffff"
        ], false);
        $c = new ServicesconfigController($req, $space);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false), $space);
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
        $c = new ServicesconfigController($req, $space);
        $c->indexAction($space['id']);

        $req = new Request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "stockForm",
            "servicesusestock" => 1,
        ], false);
        $c = new ServicesconfigController($req, $space);
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
            "display_order" => "0",
            "type_id" => 1  // quantity

        ], false);
        $c = new ServiceslistingController($req, $space);
        $data = $c->editAction($space['id'], '');
        $this->assertTrue($data['service']['id'] > 0);
        return $data['service'];
    }

    protected function getServices($space) {
        $req = new Request([
            "path" => "services/".$space['id']
        ], false);
        $c = new ServiceslistingController($req, $space);
        $data = $c->listingAction($space['id']);
        return $data['services'];        
    }

    protected function createVisa($space, $user) {
        Configuration::getLogger()->debug('create visa', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "servicesvisaedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "id_user" => $user['id'],
        ], false);
        $c = new ServicesvisaController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['visa']['id'] > 0);
        return $data['visa'];
    }

    protected function createCabinet($space, $cabinet) {
        Configuration::getLogger()->debug('create cabinet', ['cabinet' => $cabinet, 'space' => $space]);
        $req = new Request([
            "path" => "stockcabineteditedit/".$space['id']."/0",
            "formid" => "stockcabineteditform",
            "id" => 0,
            "room_number" => $cabinet["room_number"],
            "name" => $cabinet['name'],

        ], false);
        $c = new StockcabinetController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['cabinet']['id'] > 0);
        return $data['cabinet'];
    }

    protected function createShelf($space, $cabinet, $shelf) {
        Configuration::getLogger()->debug('create shelf', ['shelf' => $shelf, 'space' => $space]);
        $req = new Request([
            "path" => "stockshelfeditedit/".$space['id']."/0",
            "formid" => "stockshelfeditform",
            "id" => 0,
            "id_cabinet" => $cabinet['id'],
            "name" => $shelf['name'],

        ], false);
        $c = new StockshelfController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['shelf']['id'] > 0);
        return $data['shelf'];
    }


    protected function createOrigin($space, $user, $origin) {
        Configuration::getLogger()->debug('create origin', ['user' => $user, 'space' => $space, 'origin' => $origin]);
        $this->asUser($user['login'], $space['id']);
        $req = new Request([
            "path" => "servicesoriginedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "name" => $origin,
            "display_order" => 1
        ], false);
        $c = new ServicesoriginsController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['origin']['id'] > 0);
        return $data['origin'];
    }

    
    protected function createProject($space, $user, $name, $visa, $client, $client_user, $origin) {
        Configuration::getLogger()->debug('create origin', ['user' => $user, 'space' => $space, 'name' => $name]);
        $this->asUser($user['login'], $space['id']);
        $date = new DateTime();
        $date->modify('next monday');
        $req = new Request([
            "path" => "servicesprojectsedit/".$space['id']."/0",
            "formid" => "projectEditForm",
            "in_charge" => $visa['id'],
            "id_resp" => $client['id'],
            "name" => $name,
            "id_user" => $client_user['id'],
            "new_team" => 1,
            "new_project" => 1,
            "id_origin" => $origin['id'],
            "time_limit" => $date->format('Y-m-d'),
            "date_open" => date('Y-m-d'),
            "date_close" => ""
        ], false);
        $c = new ServicesprojectsController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['project']['id'] > 0);
        return $data['project'];

    }


    protected function createPurchase($space, $service, $count) {
        Configuration::getLogger()->debug('create puchase', ['service' => $service, 'space' => $space]);
        $req = new Request([
            "path" => "servicespurchaseedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "comment" => "new purchase",
            "date" => date('Y-m-d'),
            "services" => [$service['id']],
            "quantities" => [$count]

        ], false);
        $c = new ServicespurchaseController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['purchase']['id'] > 0);
        return $data['purchase'];
    }

    protected function createOrder($space, $service, $user, $client, $count) {
        Configuration::getLogger()->debug('create puchase', ['service' => $service, 'space' => $space]);
        $req = new Request([
            "path" => "servicesorderedit/".$space['id']."/0",
            "formid" => "orderEditForm",
            "no_identification" => 'order'.time(),
            "id_user" => $user['id'],
            "id_client" => $client['id'],
            "id_status" => 1,
            "date_open" => date('Y-m-d'),
            "date_close" => '',
            "services" => [$service['id']],
            "quantities" => [$count]

        ], false);
        $c = new ServicesordersController($req, $space);
        $data = $c->editAction($space['id'], 0);
        $this->assertTrue($data['order']['id'] > 0);
        return $data['order'];
    }

}


?>