<?php

require_once 'tests/AntibodiesBaseTest.php';


class AntibodiesTest extends AntibodiesBaseTest {

    public function testConfigureModuleAntibodies() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateAntibodies($space, $user);
        }
    }

    public function testCreateAntibodies() {
        $ctx = $this->Context();
        $spaces = array_keys($ctx['spaces']);
        $space = $this->space($spaces[0]);

        $this->asAdmin();
        $this->createAntibodies($space);
    }

   

}

?>