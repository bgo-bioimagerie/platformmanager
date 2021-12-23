<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Controller/BookingconfigController.php';
require_once 'Modules/booking/Controller/BookingcolorcodesController.php';
require_once 'Modules/booking/Controller/BookingaccessibilitiesController.php';

require_once 'Modules/resources/Controller/ResourcesinfoController.php';

require_once 'tests/BaseTest.php';

class BookingBaseTest extends BaseTest {

    protected function activateBooking($space, $user) {
        Configuration::getLogger()->debug('activate booking', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate booking module
        $req = new Request([
            "path" => "bookingconfig/".$space['id'],
            "formid" => "menusactivationForm",
            "bookingmenustatus" => 2,
            "displayBookingMenu" => 0,
            "colorBookingMenu" =>  "#000000",
            "colorTxtBookingMenu" => "#ffffff",
            "bookingsettingsmenustatus" => 3,
            "displaySettingsMenu" => 0,
            "colorSettingsMenu" =>  "#000000",
            "colorTxtSettingsMenu" => "#ffffff"

        ], false);
        $c = new BookingconfigController($req);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false));
        $spaceView = $c->viewAction($space['id']);
        $clientsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'booking') {
                $clientsEnabled = true;
            }
        }
        $this->assertTrue($clientsEnabled);
        $clientsEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'bookingsettings') {
                $clientsEnabled = true;
            }
        }
        $this->assertTrue($clientsEnabled);
    }

    protected function setupBookings($space, $user, $suffix='') {
        Configuration::getLogger()->debug('setup bookings', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);

        // Create color code
        $req = new Request([
            "path" => "bookingcolorcodeedit/".$space['id']."/0",
            "formid" => "editActionForm",
            "id" => 0,
            "name" => "colorcode1".$suffix,
            "color" => "#000000",
            "text" => "#ffffff",
            "display_order" => 0,
            "who_can_use" => 2
        ], false);
        $c = new BookingcolorcodesController($req);
        $data = $c->editAction($space['id'], 0);
        $bkcode = $data['bkcode'];
        $this->assertTrue($bkcode['id'] > 0);
        $data = $c->indexAction($space['id']);
        $bkcodes = $data['bkcodes'];
        $this->assertFalse(empty($bkcodes));

        // Create color code
        $req = new Request([
            "path" => "bookingcolorcodeedit/".$space['id']."/0",
            "formid" => "editActionForm",
            "id" => 0,
            "name" => "colorcode2".$suffix,
            "color" => "#ffffff",
            "text" => "#000000",
            "display_order" => 0,
            "who_can_use" => 3
        ], false);
        $c = new BookingcolorcodesController($req);
        $data = $c->editAction($space['id'], 0);
        $bkcode = $data['bkcode'];
        $this->assertTrue($bkcode['id'] > 0);
        $data = $c->indexAction($space['id']);
        $bkcodes = $data['bkcodes'];
        $this->assertFalse(empty($bkcodes));
        
        // Update resources accessibilities
        $req = new Request([
            "path" => "resources/".$space['id'],
            "id" => 0
        ], false);
        $c = new ResourcesinfoController($req);
        $data = $c->indexAction($space['id']);
        $resources = $data['resources'];
        $form = [
            "path" => "bookingaccessibilities/".$space['id'],
            "formid" => "bookingaccessibilities",
        ];
        $form["r_" . $resources[0]['id']] = 1;  // user can book resource 1
        $form["r_" . $resources[1]['id']] = 2;  // authorized user can book resource 2
        $form["r_" . $resources[2]['id']] = 3;  // managers can book resource 2
        $expects = [
            $resources[0]['id'] => 1,
            $resources[1]['id'] => 2,
            $resources[2]['id'] => 3
        ];
        $req = new Request($form, false);
        $c = new BookingaccessibilitiesController($req);
        $c->indexAction($space['id']);
        $form = [
            "path" => "bookingaccessibilities/".$space['id'],
        ];
        $req = new Request($form, false);
        $c = new BookingaccessibilitiesController($req);
        $data = $c->indexAction($space['id']);
        Configuration::getLogger()->debug('bkaccess', ['expects' => $expects, 'data' => $data]);
        foreach($data['bkaccess'] as $bkaccess) {
            $this->assertEquals($expects[$bkaccess['resource']], $bkaccess['bkaccess']);
        }
    }


}


?>