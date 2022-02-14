<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';


class GlobalInvoice extends Model {

    public static string $INVOICES_GLOBAL_ALL = 'invoices_global_all';
    public static string $INVOICES_GLOBAL_CLIENT = 'invoices_global_client';

    public function invoiceAll($id_space, $beginPeriod, $endPeriod, $id_user, $lang='en') {

        $clm = new ClClient();
        $resps = $clm->getAll($id_space);

        foreach( $resps as $resp ){
            $respFound  =  $this->invoice($id_space, $beginPeriod, $endPeriod, $resp["id"], $id_user, $lang);
            if($respFound) {
                $found = true;
            }
            
        }
        return $found;
    }

    public function invoice($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang='en') {
        $modules = Configuration::get("modules");
        $found = false;
        foreach ($modules as $module) {
            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                if ($model->hasActivity($id_space, $beginPeriod, $endPeriod, $id_client)) {
                    $found = true;
                    break;
                }
            }
        }
        if(!$found) {
            return false;
        }


        // create invoice in the database
        $modelInvoice = new InInvoice();
        $invoiceNumber = $modelInvoice->getNextNumber($id_space);
        $id_invoice = $modelInvoice->addInvoice("invoices", "invoiceglobal", $id_space, 'in progress', date("Y-m-d", time()), $id_client, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $id_user);
        $modelInvoice->setTitle($id_space, $id_invoice, InvoicesTranslator::Invoice($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));

        // get invoice content
        $invoiceDataArray = array();
        $total_ht = 0;


        try {
            foreach ($modules as $module) {

                $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
                if (file_exists($invoiceModelFile)) {

                    require_once $invoiceModelFile;
                    $modelName = ucfirst(strtolower($module)) . "Invoice";
                    $model = new $modelName();

                    $moduleArray = array();
                    $moduleArray["module"] = $module;
                    $moduleArray["data"] = $model->invoice($id_space, $beginPeriod, $endPeriod, $id_client, $id_invoice, $lang);
                    $invoiceDataArray[] = $moduleArray;

                    $total_ht += floatval($moduleArray["data"]["total_ht"]);
                }
            }

        } catch(Exception $e) {
            $modelInvoice->setNumber($id_space, $id_invoice, 'error');
            throw $e;
        }

        // set invoice content to the database
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoice->setNumber($id_space, $id_invoice, $invoiceNumber);
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->setItem($id_space, 0, $id_invoice, "invoices", "invoiceglobal", json_encode($invoiceDataArray), "", $total_ht);

        return true;

        }
        
}
?>