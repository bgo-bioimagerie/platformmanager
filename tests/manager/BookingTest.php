<?php

require_once 'tests/BookingBaseTest.php';


class BookingTest extends BookingBaseTest {

    public function testConfigureModuleBooking() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateBooking($space, $user);
        }
    }

}

?>