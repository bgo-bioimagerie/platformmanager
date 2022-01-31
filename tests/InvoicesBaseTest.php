<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Controller/BookinginvoiceController.php';
require_once 'Modules/booking/Controller/BookingpricesController.php';

require_once 'Modules/services/Controller/ServicesinvoiceprojectController.php';
require_once 'Modules/services/Controller/ServicesinvoiceorderController.php';

require_once 'Modules/invoices/Controller/InvoicesconfigController.php';
require_once 'Modules/invoices/Controller/InvoiceslistController.php';

require_once 'Modules/resources/Controller/ResourcesinfoController.php';

require_once 'tests/BaseTest.php';

class InvoicesBaseTest extends BaseTest {

    protected function activateInvoices($space, $user) {
        Configuration::getLogger()->debug('activate invoices', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate booking module
        $req = $this->request([
            "path" => "invoicesconfig/".$space['id'],
            "formid" => "invoicesmenusactivationForm",
            "invoicesMenustatus" => 3,
            "invoicesDisplayMenu" => 0,
            "invoicesDisplayColor" =>  "#000000",
            "invoicesDisplayColorTxt" => "#ffffff"
        ]);
        $c = new InvoicesconfigController($req, $space);
        $c->runAction('invoices', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $invoicesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'invoices') {
                $invoicesEnabled = true;
            }
        }
        $this->assertTrue($invoicesEnabled);

        $template = __DIR__."/../externals/pfm/templates/invoice_template.twig";
        copy($template, "/tmp/test.twig");
        $_FILES = ["template" => ["error" => 0, "name" => "test.twig", "tmp_name" => "/tmp/test.twig", "size" => filesize($template)]];
        $req = $this->request([
            "path" => "invoicepdftemplate/".$space['id'],
            "formid" => "formUploadTemplate"
        ]);
        $c = new InvoicesconfigController($req, $space);
        try {
            $c->runAction('invoices', 'pdftemplate', ['id_space' => $space['id']]);
        } catch(Throwable) {
            if(!file_exists(__DIR__."/../data/invoices/".$space["id"])){
                mkdir(__DIR__."/../data/invoices/".$space["id"]);
            }
            copy($template, __DIR__."/../data/invoices/".$space["id"]."/template.twig");
        }
        unset($_FILES);
        // move fails in testing...
        //$this->assertTrue(file_exists(__DIR__."/../data/invoices/".$space["id"]."/template.twig"));

        // Set resource / pricing
        $req = $this->request([
            "path" => "resources/".$space['id'],
            "id" => 0
        ]);
        $c = new ResourcesinfoController($req, $space);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $resources = $data['resources'];
        $form = [
            "path" => "bookingpriceseditquery/".$space['id'],
            "formid" => "bookingPricesForm",
        ];
        foreach ($resources as $resource) {
            $form['resource_id'] = $resource['id'].'-day';
            $form['resource'] = $resource['name'];
            $cpm = new ClPricing();
            $pricings = $cpm->getAll($space['id']);
            $price = 10;
            foreach($pricings as $p) {
                $form["bel_" . $p['id']] = $price;
                $price += 10;
            }
            $req = $this->request($form);
            $c = new BookingpricesController($req, $space);
            $c->runAction('booking', 'editquery', ['id_space' => $space['id']]);
        }

        $req = $this->request([
            "path" => "bookingprices/".$space['id'],

        ]);
        $c = new BookingpricesController($req, $space);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        $this->assertTrue(!empty($data['prices']));

    }

    protected function doInvoice($space, $user, $client){
        Configuration::getLogger()->debug('generate invoices', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $dateStart = new DateTime('first day of this month');
        $dateEnd = new DateTime('last day of this month');
        $req = $this->request([
            "path" => "bookinginvoice/".$space['id'],
            "formid" => "ByPeriodForm",
            "period_begin" => $dateStart->format('Y-m-d'),
            "period_end" => $dateEnd->format('Y-m-d'),
            "id_resp" => $client['id']
        ]);
        $c = new BookinginvoiceController($req, $space);
        $data = $c->runAction('booking', 'index', ['id_space' => $space['id']]);
        $invoice_id = $data['invoice']["id"];
        $this->assertTrue($invoice_id > 0);


        // invoice order
        /* FAILING in parseOrdersToDetails, a PR is in progress to fix orders
        $req = $this->request([
            "path" => "servicesinvoiceorder/".$space['id'],
            "formid" => "invoicebyunitform",
            "date_begin" => $dateStart->format('Y-m-d'),
            "date_end" => $dateEnd->format('Y-m-d'),
            "id_client" => $client['id']
        ]);
        $c = new ServicesinvoiceorderController($req, $space);
        $data = $c->runAction('services', 'index', ['id_space' => $space['id']]);
        $service_invoice_id = $data['invoice']["id"];
        $this->assertTrue($service_invoice_id > 0);
        */


        // invoice service
        $req = $this->request([
            "path" => "servicesinvoiceproject/".$space['id'],
            "formid" => "ByPeriodForm",
            "period_begin" => $dateStart->format('Y-m-d'),
            "period_end" => $dateEnd->format('Y-m-d'),
            "id_resp" => $client['id']
        ]);
        $c = new ServicesinvoiceprojectController($req, $space);
        $data = $c->runAction('services', 'index', ['id_space' => $space['id']]);
        $service_invoice_id = $data['invoice']["id"];
        $this->assertTrue($service_invoice_id > 0);
        $req = $this->request([
            "path" => "invoiceedit/".$space['id'].'/'.$service_invoice_id
        ]);
        $c = new ServicesinvoiceprojectController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id_invoice' => $service_invoice_id, 'pdf' => 0]);
        $itemDatas = explode(';', $data['item']['content']);
        $itemData = explode('=', $itemDatas[0]);
        $serviceId = $itemData[0];
        $quantity = $itemData[1];
        $unitPrice = $itemData[2];
        $comment = $itemData[3];
        $formid = 'editinvoiceprojectform';
        $req = $this->request([
            "path" => "invoiceedit/".$space['id'].'/'.$service_invoice_id,
            "formid" => $formid,
            "id_service" => [$serviceId],
            "quantity" => [$quantity],
            "unit_price" => [$unitPrice],
            "comment" => [$comment],
            "discount" => 0
        ]);
        $c = new ServicesinvoiceprojectController($req, $space);
        $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id_invoice' => $service_invoice_id, 'pdf' => 0]);


        $req = $this->request([
            "path" => "invoiceedit/".$space['id'].'/'.$service_invoice_id
        ]);
        $c = new ServicesinvoiceprojectController($req, $space);
        $data = $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id_invoice' => $service_invoice_id, 'pdf' => 0]);
        $itemDatas = explode(';', $data['item']['content']);
        $itemData = explode('=', $itemDatas[0]);
        $this->assertTrue($itemData[1]==$quantity);


        $req = $this->request([
            "path" => "bookinginvoiceedit/".$space['id'].'/'.$invoice_id.'/1'
        ]);
        // try generate pdf
        $c = new BookinginvoiceController($req, $space);
        $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id_invoice' => $invoice_id, 'pdf' => 1]);

        // with details
        $c->runAction('booking', 'edit', ['id_space' => $space['id'], 'id_invoice' => $invoice_id, 'pdf' => 2]);

        $req = $this->request([
            "path" => "invoiceedit/".$space['id'].'/'.$service_invoice_id
        ]);
        $c = new ServicesinvoiceprojectController($req, $space);
        $c->runAction('services', 'edit', ['id_space' => $space['id'], 'id_invoice' => $service_invoice_id, 'pdf' => 1]);
        
    }

    protected function listInvoices($space) {
        $req = $this->request([
            "path" => "invoices/".$space['id'],
        ]);
        $i = new InvoiceslistController($req, $space);
        $data = $i->runAction('invoices', 'index', ['id_space' => $space['id'], 'sent' => 0]);
        return $data['invoices'];
    }

    protected function editInvoice($space, $invoice) {
        $req = $this->request([
            "path" => "invoiceedit/".$space['id'].'/'.$invoice['id'],
        ]);

        $i = new InvoiceslistController($req, $space);
        $data = $i->runAction('invoices', 'edit', ['id_space' => $space['id'], 'id' => $invoice['id']]);
        return $data['invoice'];
    }

    protected function deleteInvoice($space, $invoice) {
        $req = $this->request([
            "path" => "invoicedelete/".$space['id'].'/'.$invoice['id'],
        ]);
        $i = new InvoiceslistController($req, $space);
        $data = $i->runAction('invoices', 'delete', ['id_space' => $space['id'], 'id' => $invoice['id']]);
        return $data['invoice'];
    }



}


?>