<?php

require_once 'tests/InvoicesBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';

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
            $req = $this->request([
                "path" => "clclients/".$space['id'],
                "id" => 0
             ]); 
            $c = new ClientslistController($req, $space);
            $data = $c->runAction('clients', 'index', ['id_space' => $space['id']]);
            $clients = $data['clients'];
            $this->doInvoice($space, $user, $clients[0]);

        }
    }

    public function testListEditInvoice() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];

        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $invoices = $this->listInvoices($space);
            $this->assertTrue(!empty($invoices));
            $invoice = $this->editInvoice($space, $invoices[0]);
            $this->assertTrue($invoice['id'] > 0);
            $canList = true;
            try {
                $user = $this->user($data['users'][0]);
                $this->asUser($user['login'], $space['id']);
                $list = $this->listInvoices($space);
                Configuration::getLogger()->error('should not list', ['invoices' => $list]);
            } catch(Exception) {
                $canList = false;
            }
            $this->assertFalse($canList);
            $canEdit = true;
            try {
                $user = $this->user($data['users'][0]);
                $this->asUser($user['login'], $space['id']);
                $i = $this->editInvoice($space, $invoices[0]);
                Configuration::getLogger()->error('should not edit', ['invoice' => $i]);
            } catch(Exception) {
                $canEdit = false;
            }
            $this->assertFalse($canEdit);

            $canDelete = true;
            try {
                $user = $this->user($data['users'][0]);
                $this->asUser($user['login'], $space['id']);
                $i = $this->deleteInvoice($space, $invoices[0]);
                Configuration::getLogger()->error('should not delete', ['invoice' => $i]);
            } catch(Exception) {
                $canDelete = false;
            }
            $this->assertFalse($canDelete);

            // Manager can delete invoice
            $user = $this->user($data['managers'][0]);
            $this->asUser($user['login'], $space['id']);
            $this->asUser($user['login'], $space['id']);
            $i = $this->deleteInvoice($space, $invoices[0]);
            $this->assertTrue($i['id'] > 0);
            // 1 space is enough, we tested booking on first space only
            break;
        }       
    }

}

?>