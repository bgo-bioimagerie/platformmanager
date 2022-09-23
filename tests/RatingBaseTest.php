<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/rating/Controller/RatingconfigController.php';
require_once 'Modules/rating/Controller/RatingController.php';


require_once 'tests/BaseTest.php';

class RatingBaseTest extends BaseTest {

    protected function activateRating($space, $user) {

        Configuration::getLogger()->debug('setup rating', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate clients module
        $req = $this->request([
            "path" => "ratingconfig/".$space['id'],
            "formid" => "ratingmenusactivationForm",
            "ratingMenustatus" => 3,
            "ratingDisplayMenu" => 0,
            "ratingDisplayColor" =>  "#000000",
            "ratingDisplayColorTxt" => "#ffffff"
        ]);
        $c = new RatingconfigController($req, $space);
        $c->runAction('rating', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $ratingEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'rating') {
                $ratingEnabled = true;
            }
        }
        $this->assertTrue($ratingEnabled);
    }

    protected function createCampaign($space, $user) {
        Configuration::getLogger()->debug('setup clients', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);

        $now = new DateTime();
        // Create pricing
        $req = $this->request([
            "path" => "rating/".$space['id']."campaign/0",
            "formid" => "campaignedit",
            "id" => 0,
            "from_date" => $now->format('Y-m-d'),
            "to_date" => $now->add(date_interval_create_from_date_string('30 days'))->format('Y-m-d'),
            "limit_date" => $now->add(date_interval_create_from_date_string('30 days'))->format('Y-m-d'),
            "message" => 'sample campaign'
        ]);
        $c = new RatingController($req, $space);
        $data = $c->runAction('rating', 'campaign', ['id_space' => $space['id'], 'id_campaign' => 0]);
        $campaign = $data['campaign'];
        $this->assertTrue($campaign['id'] > 0);
        return $campaign;
    }

    protected function rate($space, $campaign) {
        $req = $this->request([
            "path" => "rating/".$space['id']."campaign/".$campaign['id']
        ]);
        $c = new RatingController($req, $space);
        $data = $c->runAction('rating', 'survey', ['id_space' => $space['id'], 'id_campaign' => $campaign['id']]);
        $resource = null;
        foreach ($data['resources'] as $key => $value) {
            $resource = $value;
            break;
        }

        $req = $this->request([
            "path" => "rating/".$space['id']."campaign/".$campaign['id']."/rate",
            "vid" => $resource['vid'],
            "id" => $resource['id'],
            "module" => $resource['module'],
            "name" => $resource['name'],
            "comment" => 'quite good',
            "rate" => 4,
            "anon" => 1
            
        ]);
        $c = new RatingController($req, $space);
        $data = $c->runAction('rating', 'rate', ['id_space' => $space['id'], 'id_campaign' => $campaign['id']]);
        $this->assertTrue($data['rate']['id'] > 0);
    }




}


?>