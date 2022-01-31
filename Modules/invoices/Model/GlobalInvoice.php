<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

class GlobalInvoice extends Model {

    public function invoiceAll($id_space, $beginPeriod, $endPeriod, $id_user, $lang='en') {

        $modules = Configuration::get("modules");

        $modelUser = new CoreUser();
        $resps = $modelUser->getResponsibles(); 


        foreach( $resps as $resp ){

            $found = false;
            foreach ($modules as $module) {
                $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
                if (file_exists($invoiceModelFile)) {
                    require_once $invoiceModelFile;
                    $modelName = ucfirst(strtolower($module)) . "Invoice";
                    $model = new $modelName();

                    if ($model->hasActivity($id_space, $beginPeriod, $endPeriod, $resp["id"])) {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found){
                $this->invoice($id_space, $beginPeriod, $endPeriod, $resp["id"], $id_user, $lang);
            }
        }
    }

    public function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $id_user, $lang='en') {

        // create invoice in the database
        $modelInvoice = new InInvoice();
        $invoiceNumber = $modelInvoice->getNextNumber($id_space);
        $id_invoice = $modelInvoice->addInvoice("invoices", "invoiceglobal", $id_space, $invoiceNumber, date("Y-m-d", time()), $id_resp, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $id_user);
        $modelInvoice->setTitle($id_space, $id_invoice, InvoicesTranslator::Invoice($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));

        // get invoice content
        $modules = Configuration::get("modules");
        $invoiceDataArray = array();
        $total_ht = 0;
        foreach ($modules as $module) {

            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {

                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                $moduleArray = array();
                $moduleArray["module"] = $module;
                $moduleArray["data"] = $model->invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $id_invoice, $lang);
                $invoiceDataArray[] = $moduleArray;

                $total_ht += floatval($moduleArray["data"]["total_ht"]);
            }
        }

        // set invoice content to the database
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, "invoices", "invoiceglobal", json_encode($invoiceDataArray), "", $total_ht);


        }

}
?>