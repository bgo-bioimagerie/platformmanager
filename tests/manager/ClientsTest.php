<?php

require_once 'tests/ClientsBaseTest.php';


class ClientsTest extends ClientsBaseTest {

    public function testConfigureModuleClients() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateClients($space, $user);
            $user = $this->user($data['managers'][0]);
            $client = $this->setupClients($space, $user);
            foreach($data["users"] as $u) {
                $cu = $this->user($u);
                $this->addToClient($space, $client, $cu, $user);
            }
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
            $client = $this->setupClients($space2, $user, $user['login']);
            $this->addToClient($space2, $client, $user, $user);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);

        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        // user from space1 try to access space2
        $success = true;
        try {
            $client = $this->setupClients($space2, $user, $user['login']);
            $this->addToClient($space2, $client, $user, $user);
        } catch(PfmAuthException) {
            $success=false;
        }
        $this->assertFalse($success);
    }

}

?>