<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Controller/BookingconfigController.php';


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


}


?>