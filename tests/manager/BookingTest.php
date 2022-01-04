<?php

require_once 'tests/BookingBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';

class BookingTest extends BookingBaseTest {

    public function testConfigureModuleBooking() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateBooking($space, $user);
            $user = $this->user($data['managers'][0]);
            $this->setupBookings($space, $user);
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
            $this->setupBookings($space2, $user, $user['login']);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);

        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        // user from space1 try to access space2
        $success = true;
        try {
            $this->setupBookings($space2, $user, $user['login']);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);
    }

    public function testBookings() {
        $ctx = $this->Context();
        $spaces = array_keys($ctx['spaces']);
        $space = $this->space($spaces[0]);

        $this->asAdmin();
        $req = new Request([
            "path" => "resources/".$space['id'],
            "id" => 0
         ], false); 
        $c = new ResourcesinfoController($req);
        $data = $c->indexAction($space['id']);
        $resources = $data['resources'];

        $req = new Request([
            "path" => "clclients/".$space['id'],
            "id" => 0
         ], false); 
        $c = new ClientslistController($req);
        $data = $c->indexAction($space['id']);
        $clients = $data['clients'];

        $admin = $this->user($ctx['spaces'][$spaces[0]]['admins'][0]);
        $manager = $this->user($ctx['spaces'][$spaces[0]]['managers'][0]);
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);

         /**
          * bookingaccessibilities:
          * res0 = 1;  // user can book resource
          * res1 = 2;  // authorized user can book resource
          * res3 = 3;  // managers can book resource
          */

        // manager book all resources for user
        $this->asUser($manager['login'], $space['id']);
        foreach($resources as $resource) {
            $this->book($space, $user, $clients[0], $resource, 9);
        }

        // user book all resources
        // only res0 and res1 is allowed
        $this->asUser($user['login'], $space['id']);
        foreach($resources as $resource) {
            try {
                $this->book($space, $user, $clients[0], $resource, 10);
            } catch(PHPUnit\Framework\ExpectationFailedException) {
                if($resource['name'] == 'res0') {
                    $this->fail($user['login'].' should be able to book res0');
                }
            }
        }
        // now should give bkauth to user on resource res1
        $this->asUser($admin['login'], $space['id']);
        $this->addAuthorization($space, $user, $resources[1]);

        // now user should be able to book res1
        $this->asUser($user['login'], $space['id']);
        $this->book($space, $user, $clients[0], $resources[1], 10);
    }

}

?>