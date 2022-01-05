<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Controller/BookinginvoiceController.php';
require_once 'Modules/invoices/Controller/InvoicesconfigController.php';

require_once 'tests/BaseTest.php';

class InvoicesBaseTest extends BaseTest {

    protected function activateInvoices($space, $user) {
        Configuration::getLogger()->debug('activate invoices', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate booking module
        $req = new Request([
            "path" => "invoicesconfig/".$space['id'],
            "formid" => "menusactivationForm",
            "invoicesmenustatus" => 3,
            "invoicesmenudisplay" => 0,
            "invoicesmenucolor" =>  "#000000",
            "invoicesmenucolorTxt" => "#ffffff"
        ], false);
        $c = new InvoicesconfigController($req, $space);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false), $space);
        $spaceView = $c->viewAction($space['id']);
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
        $req = new Request([
            "path" => "invoicepdftemplate/".$space['id'],
            "formid" => "formUploadTemplate"
        ], false);
        $c = new InvoicesconfigController($req, $space);
        try {
        $c->pdftemplateAction($space['id']);
        } catch(Throwable) {
            copy($template, __DIR__."/../data/invoices/".$space["id"]."/template.twig");
        }
        unset($_FILES);
        // move fails in testing...
        //$this->assertTrue(file_exists(__DIR__."/../data/invoices/".$space["id"]."/template.twig"));
    }

    protected function doInvoice($space, $user, $client){
        Configuration::getLogger()->debug('generate invoices', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $dateStart = new DateTime('first day of this month');
        $dateEnd = new DateTime('last day of this month');
        $req = new Request([
            "path" => "bookinginvoice/".$space['id'],
            "formid" => "ByPeriodForm",
            "period_begin" => $dateStart->format('Y-m-d'),
            "period_end" => $dateEnd->format('Y-m-d'),
            "id_resp" => $client['id']
        ], false);
        $c = new BookinginvoiceController($req, $space);
        $data = $c->indexAction($space['id']);
        $invoice_id = $data['invoice']["id"];
        $this->assertTrue($invoice_id > 0);
        $req = new Request([
            "path" => "bookinginvoiceedit/".$space['id'].'/'.$invoice_id.'/1'
        ], false);
        // try generate pdf
        $c = new BookinginvoiceController($req, $space);
        $c->editAction($space['id'], $invoice_id, 1);
        // with details
        $c->editAction($space['id'], $invoice_id, 2);
    }



}


?>