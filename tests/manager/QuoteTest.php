<?php

require_once 'tests/QuoteBaseTest.php';


class QuoteTest extends QuoteBaseTest {

    public function testConfigureModuleQuote() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateQuote($space, $user);
        }
    }

}

?>