<?php

require_once 'tests/StatisticsBaseTest.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/statistics/Controller/StatisticsglobalController.php';
require_once 'Modules/booking/Controller/BookingstatisticauthorizationsController.php';
require_once 'Modules/booking/Controller/BookingstatisticsController.php';
require_once 'Modules/services/Controller/ServicesstatisticsprojectController.php';
require_once 'Modules/services/Controller/ServicesstatisticsorderController.php';


class StatisticsTest extends StatisticsBaseTest {

    public function testConfigureModuleStatistics() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateStatistics($space, $user);
        }
    }

    public function testGenerateStatistics() {
        
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        $cf = new CoreFiles();
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $dateStart = new DateTime('first day of this month');
            $dateEnd = new DateTime('last day of this month');
            $req = $this->request([
                "path" => "statisticsglobal/".$space['id'],
                "formid" => "generateGlobalStatForm",
                "date_begin" => $dateStart->format('Y-m-d'),
                "date_end" => $dateEnd->format('Y-m-d'),
                "generateclientstats" => 1,
                "exclude_color" => []
            ]);
            $c = new StatisticsglobalController($req, $space);
            $data = $c->runAction('statistics', 'index', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);

            $req = $this->request([
                "path" => "bookingstatisticauthorizations/".$space['id'],
                "formid" => "bookingstatisticauthorizations",
                "period_begin" => $dateStart->format('Y-m-d'),
                "period_end" => $dateEnd->format('Y-m-d'),
            ]);
            $c = new BookingstatisticauthorizationsController($req, $space);
            $data = $c->runAction('statistics', 'index', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);


            $re = new ReCategory();
            $rcats = $re->getBySpace($space['id']);
            $req = $this->request([
                "path" => "bookingauthorizedusersquery/".$space['id'],
                "resource_id" => $rcats[0]['id'],
            ]);

            $c = new BookingstatisticauthorizationsController($req, $space);
            $data = $c->runAction('statistics', 'authorizedusersquery', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);


            $req = $this->request([
                "path" => "bookingusersstats/".$space['id'],
                "formid" => 'sygrrifstats/statbookingusers',
                "startdate" => $dateStart->format('Y-m-d'),
                "enddate" => $dateEnd->format('Y-m-d') 
            ]);

            $c = new BookingstatisticsController($req, $space);
            $data = $c->runAction('statistics', 'statbookingusers', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);

            $req = $this->request([
                "path" => "bookingreservationstats/".$space['id'],
                "formid" => "statreservationsForm",
                "date_begin" => $dateStart->format('Y-m-d'),
                "date_end" => $dateEnd->format('Y-m-d'),
                "generateclientstats" => 1,
                "exclude_color" => []
            ]);
            $c = new BookingstatisticsController($req, $space);
            $data = $c->runAction('bookingstatistics', 'statreservations', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);

            $req = $this->request([
                "path" => "statquantities/".$space['id'],
                "formid" => "bookingStatQuantities",
                "datebegin" => $dateStart->format('Y-m-d'),
                "dateend" => $dateEnd->format('Y-m-d')
            ]);
            $c = new BookingstatisticsController($req, $space);
            $data = $c->runAction('bookingstatistics', 'statquantities', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            // if no quantities will be in error, so do not test
            //$f = $cf->get($data['stats']['id']);
            //$this->assertTrue($f['status'] == CoreFiles::$READY);

            $req = $this->request([
                "path" => "bookingstatreservationresp/".$space['id'],
                "formid" => "bookingStatTimeResp",
                "datebegin" => $dateStart->format('Y-m-d'),
                "dateend" => $dateEnd->format('Y-m-d')
            ]);
            $c = new BookingstatisticsController($req, $space);
            $data = $c->runAction('bookingstatistics', 'statreservationresp', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);


            $req = $this->request([
                "path" => "servicesstatisticsproject/".$space['id'],
                "formid" => 'formbalancesheet',
                "begining_period" => $dateStart->format('Y-m-d'),
                "end_period" => $dateEnd->format('Y-m-d') 
            ]);

            $c = new ServicesstatisticsprojectController($req, $space);
            $data = $c->runAction('statistics', 'index', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);


            $req = $this->request([
                "path" => "servicesstatisticsprojectsamplesreturn/".$space['id'],
            ]);

            $c = new ServicesstatisticsprojectController($req, $space);
            $data = $c->runAction('statistics', 'samplesreturn', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);

            $req = $this->request([
                "path" => "servicesstatisticsmailresps/".$space['id'],
                "formid" => "formmailresps",
                "begining_period" => $dateStart->format('Y-m-d'),
                "end_period" => $dateEnd->format('Y-m-d')
            ]);
            $c = new ServicesstatisticsprojectController($req, $space);
            $data = $c->runAction('services', 'mailresps', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);


            $req = $this->request([
                "path" => "servicesstatisticsorder/".$space['id'],
                "formid" => "formbalancesheet",
                "begining_period" => $dateStart->format('Y-m-d'),
                "end_period" => $dateEnd->format('Y-m-d')
            ]);
            $c = new ServicesstatisticsorderController($req, $space);
            $data = $c->runAction('services', 'index', ['id_space' => $space['id']]);
            $this->assertTrue($data['stats']['id'] > 0);
            $f = $cf->get($data['stats']['id']);
            $this->assertTrue($f['status'] == CoreFiles::$READY);

        break;
        }
    }

}

?>