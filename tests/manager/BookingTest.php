<?php

require_once 'tests/BookingBaseTest.php';


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

        $manager = $this->user($ctx['spaces'][$spaces[0]]['managers'][0]);
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);


        //$user = $this->user($spaces['users'][0]);
        // manage book all resources
        $this->asUser($manager['login'], $space['id']);
        foreach($resources as $resource) {
            $this->book($space, $user, $clients[0], $resource, 9);
        }

    }

}

?>