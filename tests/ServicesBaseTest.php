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
        $req = $this->request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "servicesmenusactivationForm",
            "servicesMenustatus" => 3,
            "servicesDisplayMenu" => 0,
            "servicesDisplayColor" =>  "#000000",
            "servicesDisplayColorTxt" => "#ffffff"
        ]);
        $c = new ServicesconfigController($req, $space);
        $c->runAction('services', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $invoicesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'services') {
                $invoicesEnabled = true;
            }
        }
        $this->assertTrue($invoicesEnabled);


        $req = $this->request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "periodCommandForm",
            "servicesuseproject" => 1,
            "servicesusecommand" => 1,
        ]);
        $c = new ServicesconfigController($req, $space);
        $c->runAction('services', 'index', ['id_space' => $space['id']]);

        $req = $this->request([
            "path" => "servicesconfig/".$space['id'],
            "formid" => "stockForm",
            "servicesusestock" => 1,
        ]);
        $c = new ServicesconfigController($req, $space);
        $c->runAction('services', 'index', ['id_space' => $space['id']]);

    }

    protected function createServices($space, $user, string $service) {
        Configuration::getLogger()->debug('create services', ['user' => $user, 'space' => $space, 'service' => $service]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "servicesedit/".$space['id'],
            "formid" => "editserviceform",
            "name" => $service,
            "description" => "new service",
            "display_order" => "0",
            "type_id" => 1  // quantity

        ]);
        $c = new ServiceslistingController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => '']);
        $this->assertTrue($data['service']['id'] > 0);
        return $data['service'];
    }

    protected function getServices($space) {
        $req = $this->request([
            "path" => "services/".$space['id']
        ]);
        $c = new ServiceslistingController($req, $space);
        $data = $c->runAction('services', 'listing', ['id_space' => $space['id']]);
        return $data['services'];        
    }

    protected function createVisa($space, $user) {
        Configuration::getLogger()->debug('create visa', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "servicesvisaedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "id_user" => $user['id'],
        ]);
        $c = new ServicesvisaController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['visa']['id'] > 0);
        return $data['visa'];
    }

    protected function createCabinet($space, $cabinet) {
        Configuration::getLogger()->debug('create cabinet', ['cabinet' => $cabinet, 'space' => $space]);
        $req = $this->request([
            "path" => "stockcabineteditedit/".$space['id']."/0",
            "formid" => "stockcabineteditform",
            "id" => 0,
            "room_number" => $cabinet["room_number"],
            "name" => $cabinet['name'],

        ]);
        $c = new StockcabinetController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['cabinet']['id'] > 0);
        return $data['cabinet'];
    }

    protected function createShelf($space, $cabinet, $shelf) {
        Configuration::getLogger()->debug('create shelf', ['shelf' => $shelf, 'space' => $space]);
        $req = $this->request([
            "path" => "stockshelfeditedit/".$space['id']."/0",
            "formid" => "stockshelfeditform",
            "id" => 0,
            "id_cabinet" => $cabinet['id'],
            "name" => $shelf['name'],

        ]);
        $c = new StockshelfController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['shelf']['id'] > 0);
        return $data['shelf'];
    }


    protected function createOrigin($space, $user, $origin) {
        Configuration::getLogger()->debug('create origin', ['user' => $user, 'space' => $space, 'origin' => $origin]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "servicesoriginedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "name" => $origin,
            "display_order" => 1
        ]);
        $c = new ServicesoriginsController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['origin']['id'] > 0);
        return $data['origin'];
    }

    protected function addServiceToProject($space, $project, $service) {
        Configuration::getLogger()->debug('add service to project', ['project' => $project, 'space' => $space, 'service' => $service]);
        $date = new DateTime();
        $date->modify('next monday');
        $req = $this->request([
            "path" => "servicesprojecteditentryquery/".$space['id']."/".$project['id'],
            "formid" => "editNoteForm",
            "formprojectentryid" => 0,
            "formprojectentryprojectid" => $project['id'],
            "formprojectentrydate" => $date->format('Y-m-d'),
            "formserviceid" => $service['id'],
            "formservicequantity" => 2,
            "formservicecomment" => "add a service to project"
        ]);
        $c = new ServicesprojectsController($req, $space);
        $data = $c->runAction('services', 'editentryquery', ['id_space' => $space['id']]);
        $this->assertTrue($data['entry']['id'] > 0);
        return $data['entry'];
    }

    protected function closeProject($space, $project, $visa) {
        Configuration::getLogger()->debug('close project', ['project' => $project, 'space' => $space]);
        $date = new DateTime();
        $date->modify('next monday');
        $req = $this->request([
            "path" => "servicesprojectclosing/".$space['id']."/".$project['id'],
            "formid" => "projectclosingform",
            "date_close" => $date->format('Y-m-d'),
            "closed_by" => $visa['id'],
            "samplereturn" => '',
            "samplereturndate" => '',
        ]);
        $c = new ServicesprojectsController($req, $space);
        $data = $c->runAction('services', 'closing', ['id_space' => $space['id'], 'id' => $project['id']]);
        $this->assertTrue($data['project']['id'] > 0);
        return $data['project'];
    }

    
    protected function createProject($space, $user, $name, $visa, $client, $client_user, $origin) {
        Configuration::getLogger()->debug('create origin', ['user' => $user, 'space' => $space, 'name' => $name]);
        $this->asUser($user['login'], $space['id']);
        $date = new DateTime();
        $date->modify('next monday');
        $req = $this->request([
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
        ]);
        $c = new ServicesprojectsController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['project']['id'] > 0);
        return $data['project'];

    }


    protected function createPurchase($space, $service, $count) {
        Configuration::getLogger()->debug('create puchase', ['service' => $service, 'space' => $space]);
        $req = $this->request([
            "path" => "servicespurchaseedit/".$space['id']."/0",
            "formid" => "editserviceform",
            "comment" => "new purchase",
            "date" => date('Y-m-d'),
            "services" => [$service['id']],
            "quantities" => [$count]

        ]);
        $c = new ServicespurchaseController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['purchase']['id'] > 0);
        return $data['purchase'];
    }

    protected function createOrder($space, $service, $user, $client, $count) {
        Configuration::getLogger()->debug('create puchase', ['service' => $service, 'space' => $space]);
        $req = $this->request([
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

        ]);
        $c = new ServicesordersController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['order']['id'] > 0);
        return $data['order'];
    }

}


?>