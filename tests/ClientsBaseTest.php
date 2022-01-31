<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/clients/Controller/ClientsconfigController.php';
require_once 'Modules/clients/Controller/ClientspricingsController.php';
require_once 'Modules/clients/Controller/ClientslistController.php';
require_once 'Modules/clients/Controller/ClientsusersController.php';

require_once 'tests/BaseTest.php';

class ClientsBaseTest extends BaseTest {

    protected function activateClients($space, $user) {
        Configuration::getLogger()->debug('setup clients', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate clients module
        $req = $this->request([
            "path" => "clientsconfig/".$space['id'],
            "formid" => "clientsmenusactivationForm",
            "clientsMenustatus" => 3,
            "clientsDisplayMenu" => 0,
            "clientsDisplayColor" =>  "#000000",
            "clientsDisplayColorTxt" => "#ffffff"
        ]);
        $c = new ClientsconfigController($req, $space);
        $c->runAction('clients', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $clientsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'clients') {
                $clientsEnabled = true;
            }
        }
        $this->assertTrue($clientsEnabled);
    }

    protected function setupClients($space, $user, $suffix='') {
        Configuration::getLogger()->debug('setup clients', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);

        // Create pricing
        $req = $this->request([
            "path" => "clpricingedit/".$space['id']."/0",
            "formid" => "pricing/edit",
            "id" => 0,
            "name" => "pricing1".$suffix,
            "color" => "#000000",
            "txtcolor" => "#ffffff",
            "display_order" => 0,
            "type" => 1
        ]);
        $c = new ClientspricingsController($req, $space);
        $data = $c->runAction('clients', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $pricing = $data['pricing'];
        $this->assertTrue($pricing['id'] > 0);
        $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
        $pricings = $data['pricings'];
        $this->assertFalse(empty($pricings));

        // Check edit on pricing
        $req = $this->request([
            "path" => "clpricingedit/".$space['id']."/".$pricing['id'],
        ]);
        $c = new ClientspricingsController($req, $space);
        $data = $c->runAction('clients', 'edit', ['id_space' => $space['id'], 'id' => $pricing['id']]);
        $this->assertTrue($data['pricing']['id'] == $pricing['id']);
        $this->assertTrue($data['pricing']['name'] == "pricing1".$suffix);

        
        // Create client
        $req = $this->request([
            "path" => "clclientedit/".$space['id']."/0",
            "formid" => "client/edit",
            "id" => 0,
            "name" => "client1".$suffix,
            "contact_name" => "",
            "phone" => "",
            "email" => "client1@pfm.org",
            "pricing" => $pricing['id'],
            "invoice_send_preference" => 1

        ]);
        $c = new ClientslistController($req, $space);
        $data = $c->runAction('clients', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $client = $data['client'];
        $this->assertTrue($client['id'] > 0);
        $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
        $clients = $data['clients'];
        $this->assertFalse(empty($clients));
        return $client;
    }

    protected function addToClient($space, $client, $clientUser, $user) {
        Configuration::getLogger()->debug('add to clients', ['client' => $client, 'user' => $clientUser, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "clclientusers/".$space['id']."/".$client['id'],
            "formid" => "clientsusersform",
            "id_user" => $clientUser['id'],
        ]);
        $c = new ClientsusersController($req, $space);
        $c->runAction('clients', 'index', ['id_space' => $space['id'], 'id_client' => $client['id']]);
        $req = $this->request([
            "path" => "clclientusers/".$space['id']."/".$client['id'],
        ]);
        $c = new ClientsusersController($req, $space);
        $data = $c->runAction('clients', 'index', ['id_space' => $space['id'], 'id_client' => $client['id']]);
        $clientsusers = $data['clientsusers'];
        $this->assertTrue(!empty($clientsusers));
        $userInClient = false;
        foreach($clientsusers as $cu) {
            if($cu['id'] == $clientUser['id']) {
                $userInClient = true;
                break;
            }
        }
        $this->assertTrue($userInClient);

    }


}


?>