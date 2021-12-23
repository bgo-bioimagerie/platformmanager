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

}

?>