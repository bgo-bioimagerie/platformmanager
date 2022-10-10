<?php

require_once 'tests/RatingBaseTest.php';


class RatingTest extends RatingBaseTest {

    public function testConfigureModuleRating() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            if($space['plan'] == 0) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateRating($space, $user);
        }
    }

    public function testCampaign() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            if($space['plan'] == 0) {
                continue;
            }
            $user = $this->user($data['managers'][0]);
            $campaign = $this->createCampaign($space, $user);
            $user = $this->user($data['users'][0]);
            $this->asUser($user['login'], $space['id']);
            $this->rate($space, $campaign);
        }
    }

}

?>