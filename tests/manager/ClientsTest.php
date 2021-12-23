<?php

use PHPUnit\Framework\TestCase;
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/clients/Controller/ClientsconfigController.php';
require_once 'Modules/clients/Controller/ClientspricingsController.php';
require_once 'Modules/clients/Controller/ClientslistController.php';
require_once 'Modules/clients/Controller/ClientsusersController.php';


require_once 'tests/BaseTest.php';


class ClientsTest extends BaseTest {

    private function setupClients($space, $user) {
        Configuration::getLogger()->debug('setup clients', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate resources module
        $req = new Request([
            "path" => "clientsconfig/".$space['id'],
            "formid" => "menusactivationForm",
            "clientsmenustatus" => 3,
            "displayMenu" => '',
            "colorMenu" =>  "#000000",
            "colorTxtMenu" => "#ffffff"
        ], false);
        $c = new ClientsconfigController($req);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false));
        $spaceView = $c->viewAction($space['id']);
        $clientsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'clients') {
                $clientsEnabled = true;
            }
        }
        $this->assertTrue($clientsEnabled);

        // Create pricing
        $req = new Request([
            "path" => "clpricingedit/".$space['id']."/0",
            "formid" => "pricing/edit",
            "id" => 0,
            "name" => "pricing1",
            "color" => "#000000",
            "txtcolor" => "#ffffff",
            "display_order" => 0,
            "type" => 1
        ], false);
        $c = new ClientspricingsController($req);
        $data = $c->editAction($space['id'], 0);
        $pricing = $data['pricing'];
        $this->assertTrue($pricing['id'] > 0);
        $data = $c->indexAction($space['id']);
        $pricings = $data['pricings'];
        $this->assertFalse(empty($pricings));

        
        // Create client
        $req = new Request([
            "path" => "clclientedit/".$space['id']."/0",
            "formid" => "client/edit",
            "id" => 0,
            "name" => "client1",
            "contact_name" => "",
            "phone" => "",
            "email" => "client1@pfm.org",
            "pricing" => $pricing['id'],
            "invoice_send_preference" => 1

        ], false);
        $c = new ClientslistController($req);
        $data = $c->editAction($space['id'], 0);
        $client = $data['client'];
        $this->assertTrue($client['id'] > 0);
        $data = $c->indexAction($space['id']);
        $clients = $data['clients'];
        $this->assertFalse(empty($clients));
        return $client;
    }

    private function addToClient($space, $client, $clientUser, $user) {
        Configuration::getLogger()->debug('add to clients', ['client' => $client, 'user' => $clientUser, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate resources module
        $req = new Request([
            "path" => "clclientusers/".$space['id']."/".$client['id'],
            "formid" => "clientsusersform",
            "id_user" => $clientUser['id'],
        ], false);
        $c = new ClientsusersController($req);
        $c->indexAction($space['id'], $client['id']);
        $req = new Request([
            "path" => "clclientusers/".$space['id']."/".$client['id'],
        ], false);
        $c = new ClientsusersController($req);
        $data = $c->indexAction($space['id'], $client['id']);
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

    public function testConfigureModuleClients() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $client = $this->setupClients($space, $user);
            foreach($data["users"] as $u) {
                $cu = $this->user($u);
                $this->addToClient($space, $client, $cu, $user);
            }
        }
    }

}

?>