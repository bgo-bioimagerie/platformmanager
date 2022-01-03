<?php

require_once 'tests/InvoicesBaseTest.php';


class InvoicesTest extends InvoicesBaseTest {

    public function testConfigureModuleInvoices() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateInvoices($space, $user);

        }
    }

    public function testGenerateInvoice() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $req = new Request([
                "path" => "clclients/".$space['id'],
                "id" => 0
             ], false); 
            $c = new ClientslistController($req);
            $data = $c->indexAction($space['id']);
            $clients = $data['clients'];
            $this->doInvoice($space, $user, $clients[0]);

        }
    }

}

?>