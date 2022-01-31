<?php

require_once 'tests/ServicesBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';

class ServicesTest extends ServicesBaseTest {

    public function testConfigureModuleInvoices() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateServices($space, $user);

        }
    }

    public function testCreateServices() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $services = [];
            foreach(['service1', 'service2'] as $service) {
                $services[] = $this->createServices($space, $user, $service);
            }
            $visa = $this->createVisa($space, $user);
            $origin = $this->createOrigin($space, $user, 'origin1');


            $this->asUser($user['login'], $space['id']);
            $req = $this->request([
                "path" => "clclients/".$space['id'],
                "id" => 0
             ]); 
            $c = new ClientslistController($req, $space);
            $clients_data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
            $clients = $clients_data['clients'];
            $client_user = $this->user($data['users'][0]);
            $project = $this->createProject($space, $user, 'project1', $visa, $clients[0], $client_user, $origin);
            $this->addServiceToProject($space, $project, $services[0]);
            //$this->closeProject($space, $project, $visa);
        } 
    }

    public function testStock(){
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $cabinet = $this->createCabinet($space, ['name' => 'cab1', 'room_number' => '1']);
            $shelf = $this->createShelf($space, $cabinet, ['name' => 'shelf1']);
        }
    }

    // stock
    public function testPurchase(){
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $services = $this->getServices($space);
            $this->createPurchase($space, $services[0], 10);
            $services = $this->getServices($space);
            $this->assertEquals($services[0]['quantity'], 10);
        }
    }
    // orders

    public function testOrder(){
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $manager = $this->user($data['managers'][0]);
            $this->asUser($manager['login'], $space['id']);
            $services = $this->getServices($space);
            $user = $this->user($data['users'][0]);

            $req = $this->request([
                "path" => "clclients/".$space['id'],
                "id" => 0
             ]); 
            $c = new ClientslistController($req, $space);
            $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
            $clients = $data['clients'];
            
            $this->createOrder($space, $services[0], $user, $clients[0], 2);
            $services = $this->getServices($space);
            $this->assertEquals($services[0]['quantity'], 8);
        }
    }

    public function testNotAllowed() {
        $ctx = $this->Context();
        $spaces = array_keys($ctx['spaces']);
        $space2 = $this->space($spaces[1]);
        $user = $this->user($ctx['spaces'][$spaces[0]]['managers'][0]);
        // manager from space1 try to access space2
        $success = true;
        try {
            $this->createServices($space2, $user, 'serviceToFail');
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);

        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        // user from space1 try to access space2
        $success = true;
        try {
            $this->createServices($space2, $user, 'serviceToFail');
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);
    }


}

?>