<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Controller/BookingconfigController.php';
require_once 'Modules/booking/Controller/BookingcolorcodesController.php';
require_once 'Modules/booking/Controller/BookingaccessibilitiesController.php';
require_once 'Modules/booking/Controller/BookingdefaultController.php';
require_once 'Modules/booking/Controller/BookingController.php';
require_once 'Modules/booking/Controller/BookingnightweController.php';

require_once 'Modules/booking/Controller/BookingauthorisationsController.php';
require_once 'Modules/booking/Controller/BookingquantitiesController.php';
require_once 'Modules/core/Controller/CorespaceuserController.php';

require_once 'Modules/resources/Controller/ResourcesinfoController.php';
require_once 'Modules/resources/Model/ReVisa.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';

require_once 'Modules/clients/Model/ClPricing.php';

require_once 'tests/BaseTest.php';

class BookingBaseTest extends BaseTest {

    protected function activateBooking($space, $user) {
        Configuration::getLogger()->debug('activate booking', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate booking module
        $req = $this->request([
            "path" => "bookingconfig/".$space['id'],
            "formid" => "bookingmenusactivationForm",
            "bookingMenustatus" => 2,
            "bookingDisplayMenu" => 0,
            "bookingDisplayColor" =>  "#000000",
            "bookingDisplayColorTxt" => "#ffffff"
        ]);
        $c = new BookingconfigController($req, $space);
        $c->runAction('booking', 'index', ['id_space' => $space['id']]);

        $req = $this->request([
            "path" => "bookingconfig/".$space['id'],
            "formid" => "bookingsettingsmenusactivationForm",
            "bookingsettingsMenustatus" => 3,
            "bookingsettingsDisplayMenu" => 0,
            "bookingsettingsDisplayColor" =>  "#000000",
            "bookingsettingsDisplayColorTxt" => "#ffffff"

        ]);
        $c = new BookingconfigController($req, $space);
        $c->runAction('booking', 'index', ['id_space' => $space['id']]);

        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
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
        $req = $this->request([
            "path" => "bookingcolorcodeedit/".$space['id']."/0",
            "formid" => "editActionForm",
            "id" => 0,
            "name" => "colorcode1".$suffix,
            "color" => "#000000",
            "text" => "#ffffff",
            "display_order" => 0,
            "who_can_use" => 2
        ]);
        $c = new BookingcolorcodesController($req, $space);
        $data = $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $bkcode = $data['bkcode'];
        $this->assertTrue($bkcode['id'] > 0);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        $bkcodes = $data['bkcodes'];
        $this->assertFalse(empty($bkcodes));

        // Create color code
        $req = $this->request([
            "path" => "bookingcolorcodeedit/".$space['id']."/0",
            "formid" => "editActionForm",
            "id" => 0,
            "name" => "colorcode2".$suffix,
            "color" => "#ffffff",
            "text" => "#000000",
            "display_order" => 0,
            "who_can_use" => 3
        ]);
        $c = new BookingcolorcodesController($req, $space);
        $data = $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $bkcode = $data['bkcode'];
        $this->assertTrue($bkcode['id'] > 0);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        $bkcodes = $data['bkcodes'];
        $this->assertFalse(empty($bkcodes));
        
        // Update resources accessibilities
        $req = $this->request([
            "path" => "resources/".$space['id'],
            "id" => 0
        ]);
        $c = new ResourcesinfoController($req, $space);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $resources = $data['resources'];
        $form = [
            "path" => "bookingaccessibilities/".$space['id'],
            "formid" => "bookingaccessibilities",
        ];
        $form["r_" . $resources[0]['id']] = 1;  // user can book resource 1
        $form["r_" . $resources[1]['id']] = 2;  // authorized user can book resource 2
        $form["r_" . $resources[2]['id']] = 3;  // managers can book resource 3
        $expects = [
            $resources[0]['id'] => 1,
            $resources[1]['id'] => 2,
            $resources[2]['id'] => 3
        ];
        $req = $this->request($form);
        $c = new BookingaccessibilitiesController($req, $space);
        $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        $form = [
            "path" => "bookingaccessibilities/".$space['id'],
        ];
        $req = $this->request($form);
        $c = new BookingaccessibilitiesController($req, $space);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        Configuration::getLogger()->debug('bkaccess', ['expects' => $expects, 'data' => $data]);
        foreach($data['bkaccess'] as $bkaccess) {
            $this->assertEquals($expects[$bkaccess['resource']], $bkaccess['bkaccess']);
        }



        //  Call index to create them if not exists
        $form = [
            "path" => "bookingnightwe/".$space['id'],
        ];
        $req = $this->request($form);
        $c = new BookingnightweController($req, $space);
        $c->runAction('bookingnightwe', 'index', ['id_space' => $space['id']]);


        $clm = new ClPricing();
        $pricings = $clm->getForList($space['id']);
        foreach($pricings['ids'] as $pid) {
            $form = [
                "path" => "bookingnightweeditq/".$pid,
                "id" => $pid,
                "tarif_unique" => 0,
                "tarif_night" => 1,
                "night_start" => 19,
                "night_end" => 8,
                "tarif_we" => 1,
                "lundi" => "",
                "mardi" => "",
                "mercredi" => "",
                "jeudi" => "",
                "vendredi" => "",
                "samedi" => 1,
                "dimanche" => 1
            ];
            $req = $this->request($form);
            $c = new BookingnightweController($req, $space);
            $c->runAction('bookingnightwe', 'editquery', ['id_space' => $space['id']]);
        }
    }

    protected function viewBooking($space, $user, $id) {
        Configuration::getLogger()->debug('view booking', ['user' => $user, 'space' => $space, 'booking' => $id]);
        $req = $this->request([
            "path" => "bookingeditreservation/".$space['id'].'/r_'.$id,
        ]);
        $c = new BookingController($req, $space);
        return $c->runAction('booking', 'editreservation' , ['id_space' => $space['id'], 'param' => $id]);
    }

    protected function cancelBooking($space, $user, $id) {
        $req = $this->request([
            "path" => "bookingeditreservationdefaultdelete/".$space['id']."/".$id,
            "formid" => "bookingeditreservationdefaultdeleteform",
            "id_reservation" => $id,
            "sendmail" => 0
        ]);
        $c = new BookingdefaultController($req, $space);
        $c->runAction('booking', 'delete', ['id_space' => $space['id'], 'id' => $id]);
    }

    /**
     * Book resource on next monday between $time and $time+1 for user
     * 
     * option duration in hours
     */
    protected function book($space, $user, $client, $resource, $time=9, $day='monday', $duration=0):mixed {
        Configuration::getLogger()->debug('book', ['for' => $user, 'space' => $space, 'resource' => $resource]);
        
        $date = new DateTime();
        $date->modify('next '.$day);
        $date->setTime($time, 0, 0);
        $bookDate = $date->format('Y-m-d');

        $bookEnd = $bookDate;
        $hour_endH = $time+1;
        if($duration) {
            $resa_end = $date;
            $resa_end->add(DateInterval::createFromDateString($duration.' hour'));
            $bookEnd = $resa_end->format('Y-m-d');
            $hour_endH = $resa_end->format('h');
        }

        Configuration::getLogger()->debug('[book] info', ['resa_start' => $bookDate, 'hour_startH' => $time, 'resa_end' => $bookEnd, 'hour_endH' => $hour_endH]);

        $req = $this->request([
            "path" => "bookingeditreservationquery/".$space['id'],
            "formid" => "editReservationDefault",
            "id" => 0,
            "id_resource" => $resource['id'],
            "recipient_id" => $user['id'],
            "responsible_id" => $client['id'],
            "color_type_id" => 0,
            "all_day_long" => 0,
            "resa_start" => $bookDate,
            "hour_startH" => $time,
            "hour_startm" => 0,
            "resa_end" => $bookEnd,
            "hour_endH" => $hour_endH,
            "hour_endm" => 0,
            "reason" => 0
        ]);        
        $c = new BookingdefaultController($req, $space);
        $data = $c->runAction('booking', 'editreservationquery', ['id_space' => $space['id']]);
        $this->assertTrue($data !== null);
        $this->assertTrue(array_key_exists('bkcalentry', $data));
        $bkcalentry = $data['bkcalentry'];
        $this->assertTrue($bkcalentry['id'] > 0);
        return $bkcalentry['id'];
    }

    protected function addAuthorization($space, $user, $resource) {
        // bookingauthorisationsadd/*id_space*/*id_cat*_*id_user*
        Configuration::getLogger()->debug('add bk auth', ['for' => $user, 'space' => $space, 'resource' => $resource]);
        // need to get resource category
        $id_resource_category = $resource['id_category'];
        // Get a visa
        $modelVisa = new ReVisa();
        $visas = $modelVisa->getForListByCategory($space['id'], $id_resource_category);

        $req = $this->request([
            "path" => "corespaceuseredit/".$space['id']."/".$user['id'],
            "formid" => "authorisationAddForm",
            "user" => $user['id'],
            "resource" => $id_resource_category,
            "visa_id" => $visas['ids'][0],
            "date" => date('Y-m-d')
        ]);
        $c = new CorespaceuserController($req, $space);
        $c->runAction('corespaceuser', 'edit', ['id_space' => $space['id'], 'id_user' => $user['id']]);

        $req = $this->request([
            "path" => "bookingauthorizations/".$space['id']."/".$user['id'],
        ]);
        $c = new BookingauthorisationsController($req, $space);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id'], 'id_user' => $user['id']]);
        $this->assertTrue(!empty($data['bkauthorizations']));
    }

    protected function addBkQuantity($space, $user, $resource, $isInvoicingUnit=false) {
        Configuration::getLogger()->debug('add bk cal quantity', ['for' => $user, 'space' => $space, 'resource' => $resource]);
        $qteName = "quantity1";
        $req = $this->request([
            "path" => "bookingquantities/".$space['id'],
            "formid" => "supsForm",
            "id_sups" => [0],
            "id_resources" => [$resource['id']],
            "names" => [$qteName],
            "mandatory" => [true],
            "is_invoicing_unit" => [$isInvoicingUnit]
        ]);
        $c = new BookingquantitiesController($req, $space);
        $data = $c->runAction('bookingsettings', 'index', ['id_space' => $space['id']]);
        $this->assertTrue($data !== null);
        $this->assertTrue(array_key_exists('bksupids', $data));
        $id = $data['bksupids'][count($data['bksupids']) - 1];
        $modelCalQte = new BkCalQuantities();
        $bkCalQuantity = $modelCalQte->getById($space['id'], $id);
        $this->assertTrue($bkCalQuantity['name'] === $qteName);
        $this->assertTrue($id > 0);
        return $id;
    }

    public function setReservationWithInvoicingUnit($space, $user, $client, $resource) {
        $bkQteId = $this->addBkQuantity($space, $user, $resource, true);
        $bkCalEntryId = $this->book($space, $user, $client, $resource);
        $this->assertTrue($bkCalEntryId > 0);
        // get bookDate
        $bkCalEntryModel = new BkCalendarEntry();
        $bkCalEntry = $bkCalEntryModel->getEntry($space['id'], $bkCalEntryId);
        return $bkCalEntry;
    }

}


?>