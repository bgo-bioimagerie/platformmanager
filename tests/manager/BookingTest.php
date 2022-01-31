<?php

require_once 'tests/BookingBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/booking/Controller/BookingschedulingController.php';

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
        $req = $this->request([
            "path" => "resources/".$space['id'],
            "id" => 0
         ]); 
        $c = new ResourcesinfoController($req, $space);
        $data = $c->runAction('resources', 'index', ['id_space' =>$space['id']]);
        $resources = $data['resources'];

        $req = $this->request([
            "path" => "clclients/".$space['id'],
            "id" => 0
         ]); 
        $c = new ClientslistController($req, $space);
        $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
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
        $userBookings = [];
        $this->asUser($user['login'], $space['id']);
        foreach($resources as $resource) {
            try {
                $bookingId = $this->book($space, $user, $clients[0], $resource, 10);
                $userBookings[$resource['id']] = $bookingId;
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
        //$this->asUser($user['login'], $space['id']);
        $this->asUser($user['login'], $space['id']);
        $bookingId = $this->book($space, $user, $clients[0], $resources[1], 10);
        $userBookings[$resources[1]['id']] = $bookingId;

        // Should conflict, for all users
        for($i=0;$i<2;$i++){
            $user = $this->user($ctx['spaces'][$spaces[0]]['users'][$i]);
            $this->asUser($user['login'], $space['id']);
            $resource = $resources[0];
            $canBook = true;
            try {
                $this->book($space, $user, $clients[0], $resource, 10);
            } catch(PHPUnit\Framework\ExpectationFailedException) {
                    $canBook = false;
            }
            if($canBook) {
                $this->fail('BAD EXPECTING CONFLICT');
            }
        }

        // user1 can edit resa
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        $this->asUser($user['login'], $space['id']);
        // Get user1 booking on resource 0
        $bookingId = $userBookings[$resources[0]['id']];
        try {
        $this->viewBooking($space, $user, 'r_'.$bookingId);
        } catch(Exception) {
            $this->fail('user1 should have access to booking');
        }
        // user2 cannot edit resa
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][1]);
        $this->asUser($user['login'], $space['id']);
        $canView = true;
        try {
            $this->viewBooking($space, $user, 'r_'.$bookingId);
        } catch(Exception) {
            $canView = false;
        }
        $this->assertFalse($canView);
        // user2 cannot cancel user1 booking1
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][1]);
        $this->asUser($user['login'], $space['id']);
        $canCancel = true;
        try {
        $this->cancelBooking($space, $user, $bookingId);
        } catch(Exception) {
            $canCancel = false;
        }
        $this->assertFalse($canCancel, 'user should be able to cancel booking');

        // user1 can cancel his booking
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        $this->asUser($user['login'], $space['id']);
        $this->cancelBooking($space, $user, $bookingId);
        $isCancelled = false;
        try {
            $this->viewBooking($space, $user, 'r_'.$bookingId);
        } catch(PfmParamException) {
            $isCancelled = true;
        }
        $this->assertTrue($isCancelled);
        // user2 can book without conflict
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        $this->asUser($user['login'], $space['id']);
        $bookingId = $this->book($space, $user, $clients[0], $resources[0], 10);
    }

    public function testBookinSCheduling() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        $space = null;
        $manager = null;
        $user = null;
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $manager = $this->user($data['managers'][0]);
            $user = $this->user($data['users'][0]);

            break;
        }
        $this->asUser($manager['login'], $space['id']);
        $req = $this->request([
            "path" => "resources/".$space['id'],
            "id" => 0
         ]); 
        $c = new ResourcesinfoController($req, $space);
        $data = $c->runAction('resources', 'index', ['id_space' =>$space['id']]);
        $resources = $data['resources'];
        $resource = $resources[0];
        $req = $this->request([
            "path" => "clclients/".$space['id'],
            "id" => 0
         ]); 
        $c = new ClientslistController($req, $space);
        $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
        $clients = $data['clients'];

        $r = new ReArea();
        $areas = $r->getForSpace($space['id']);
        $area = $areas[0];
        $req = $this->request([
            'path' => 'bookingschedulingedit/'.$space['id'].'/'.$area['id']
        ]);
        $c = new BookingschedulingController($req, $space);
        $data = $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id' => $area['id']]);
        $bkScheduling = $data['bkScheduling'];
        $this->assertTrue($bkScheduling['id']  == 0);

        // cannot book at 19h
        $this->asUser($user['login'], $space['id']);
        $canBook = true;
        try {
            $this->book($space, $user, $clients[0], $resource, 19);
        } catch(Exception) {
            $canBook = false;
        }
        $this->assertFalse($canBook, 'should not be able to book at 19h');

        $this->asUser($manager['login'], $space['id']);
        $bkScheduling['day_end'] = '20';
        $form = array_merge([
            'path' => 'bookingschedulingedit/'.$space['id'].'/'.$area['id'],
            'formid' => 'bookingschedulingedit'
        ], $bkScheduling);
        $req = $this->request($form);

        $c = new BookingschedulingController($req, $space);
        $data = $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id' => $area['id']]);
        $bkScheduling = $data['bkScheduling'];

        $this->assertTrue($bkScheduling['id']  > 0);

        $this->asUser($user['login'], $space['id']);
        // cannot book at 19h
        $canBook = true;
        try {
        $this->book($space, $user, $clients[0], $resource, 19);
        } catch(Exception $e) {
            $canBook = false;
            Configuration::getLogger()->error('should be able to book at 19h', ['error' => $e]);
        }
        $this->assertTrue($canBook, 'should be able to book at 19h');

        $this->asUser($manager['login'], $space['id']);
        $nextMonday = new DateTime();
        $nextMonday->modify('next monday');
        // navigate
        $req = $this->request([
            'path' => 'bookingday/'.$space['id'],
            'bk_curentDate' => $nextMonday->format('Y-m-d'),
            'bk_id_resource' => $resource['id'],
            'bk_id_area' => $area['id']
        ]);
        $c = new BookingController($req, $space);
        $data = $c->runAction('booking', 'day', ['id_space' => $space['id'], 'action' => '', 'message' => '']);
        $this->assertTrue(!empty($data['bookings']));

        $req = $this->request([
            'path' => 'bookingdayarea/'.$space['id'],
            'bk_curentDate' => $nextMonday->format('Y-m-d'),
            'bk_id_resource' => $resource['id'],
            'bk_id_area' => $area['id']
        ]);
        $c = new BookingController($req, $space);
        $data = $c->runAction('booking', 'dayarea', ['id_space' => $space['id'], 'action' => '', 'message' => '']);
        $this->assertTrue(!empty($data['bookings']));

        $req = $this->request([
            'path' => 'bookingweek/'.$space['id'],
            'bk_curentDate' => $nextMonday->format('Y-m-d'),
            'bk_id_resource' => $resource['id'],
            'bk_id_area' => $area['id']
        ]);
        $c = new BookingController($req, $space);
        $data = $c->runAction('booking', 'week', ['id_space' => $space['id'], 'action' => '', 'message' => '']);
        $this->assertTrue(!empty($data['bookings']));

        $req = $this->request([
            'path' => 'bookingweekarea/'.$space['id'],
            'bk_curentDate' => $nextMonday->format('Y-m-d'),
            'bk_id_resource' => $resource['id'],
            'bk_id_area' => $area['id']
        ]);
        $c = new BookingController($req, $space);
        $data = $c->runAction('booking', 'weekarea', ['id_space' => $space['id'], 'action' => '', 'message' => '']);
        $this->assertTrue(!empty($data['bookings']));

        $req = $this->request([
            'path' => 'bookingmonth/'.$space['id'],
            'bk_curentDate' => $nextMonday->format('Y-m-d'),
            'bk_id_resource' => $resource['id'],
            'bk_id_area' => $area['id']
        ]);
        $c = new BookingController($req, $space);
        $data = $c->runAction('booking', 'month', ['id_space' => $space['id'], 'action' => '', 'message' => '']);
        $this->assertTrue(!empty($data['bookings']));
    }

}

?>