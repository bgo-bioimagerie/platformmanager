<?php

require_once 'tests/InvoicesBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'tests/BookingBaseTest.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';

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
            break;

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

    public function testBookingInvoicingUnits() {
        // test invoicable quantities
        $bkEntryModel = new BkCalendarEntry();
        $resourcesModel = new ResourceInfo();
        $clientUserModel = new ClClientUser();
        $bkBaseTestModel = new BookingBaseTest();

        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        $spaceName = array_key_first($spaces);

        $nbScenarios = 4;
        for ($scenario=1; $scenario <= $nbScenarios; $scenario++) {
            $space = $this->space($spaceName);
            $user = $this->user($spaces[$spaceName]['users'][0]);
            $admin = $spaces[$spaceName]['admins'][0];
            $this->asUser($admin, $space['id']);

            // delete all calentries
            $calEntries = $bkEntryModel->getAllEntries($space['id']);
            if (!empty($calEntries)) {
                foreach ($calEntries as $entry) {
                    $bkEntryModel->setDeleted($space['id'], $entry['id']);
                }
            }
            // delete all previous invoices
            $invoicesList = $this->listInvoices($space);
            foreach ($invoicesList as $invoice) {
                $this->deleteInvoice($space, $invoice);
            }
            $resources = $resourcesModel->getForSpace($space['id']);
            $client = $clientUserModel->getUserClientAccounts($user['id'], $space['id'])[0];

            // set booking
            $reservationResult = $bkBaseTestModel->setReservationWithInvoicingUnits($space, $user, $client, $resources[0], $scenario); 
            //invoice booking
            $user = $this->asUser($admin, $space['id']);
            $this->doInvoice($space, $user, $client);
            
            // get resulting invoice
            $resultingInvoice = null;
            $invoicesList = $this->listInvoices($space);
            foreach ($invoicesList as $invoice) {
                if ($invoice["module"] === "booking") {
                    //get result
                    $resultingInvoice = $invoice;
                }
            }
            // check if resulting invoice data matches with what's expected
            $invoiceTotalHt = $resultingInvoice['total_ht'];
            $this->assertTrue($invoiceTotalHt == $reservationResult["expectedCost"]);
        }
    }

}

?>