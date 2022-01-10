<?php

require_once 'tests/DocumentsBaseTest.php';


class DocumentsTest extends DocumentsBaseTest {

    public function testConfigureModuleDocuments() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateDocuments($space, $user);
        }
    }

}

?>