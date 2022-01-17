<?php

require_once 'tests/HelpdeskBaseTest.php';


class HelpdeskTest extends HelpdeskBaseTest {

    public function testConfigureModuleHelpdesk() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateHelpdesk($space, $user);
        }
    }

}

?>