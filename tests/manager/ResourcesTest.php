<?php

require_once 'tests/ResourcesBaseTest.php';


class ResourcesTest extends ResourcesBaseTest {

    public function testConfigureModuleResources() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateResources($space, $user);
            $user = $this->user($data['managers'][0]);
            $this->setupResources($space, $user);
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
            $this->setupResources($space2, $user, $user['login']);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);

        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        // user from space1 try to access space2
        $success = true;
        try {
            $this->setupResources($space2, $user, $user['login']);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);
    }

    public function testResponsible() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $user = $this->user($data['managers'][0]);
            $space = $this->space($spaceName);
            $this->asUser($user['login'], $space['id']);
            $space = $this->space($spaceName);
            $this->addInstructorStatus($space, 'resp_status_1');
        }
    }

    public function testEventType() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $user = $this->user($data['managers'][0]);
            $space = $this->space($spaceName);
            $this->asUser($user['login'], $space['id']);
            $this->addEventType($space, 'resp_evt_type_1');
        }
    }

    public function testState() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $user = $this->user($data['managers'][0]);
            $space = $this->space($spaceName);
            $this->asUser($user['login'], $space['id']);
            $this->addState($space, 'resp_state_1');
        }
    }

    public function testReEvent() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $user = $this->user($data['managers'][0]);
            $space = $this->space($spaceName);
            $this->asUser($user['login'], $space['id']);
            $et = $this->addEventType($space, 're_eventtype_event');
            $s = $this->addState($space, 'resp_state_event');
            $resources = $this->getResources($space);
            $r = $resources[0];
            $this->addResourceEvent($space, $r, $user, $et, $s);
        }

    }

}

?>