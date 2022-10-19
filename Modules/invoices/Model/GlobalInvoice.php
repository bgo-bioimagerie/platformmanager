<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';


class GlobalInvoice extends Model
{
    public static string $INVOICES_GLOBAL_ALL = 'invoices_global_all';
    public static string $INVOICES_GLOBAL_CLIENT = 'invoices_global_client';

    public function invoiceAll($idSpace, $beginPeriod, $endPeriod, $idUser, $lang='en')
    {
        $clm = new ClClient();
        $resps = $clm->getAll($idSpace);
        $found = false;
        foreach ($resps as $resp) {
            $respFound  =  $this->invoice($idSpace, $beginPeriod, $endPeriod, $resp["id"], $idUser, $lang);
            if ($respFound) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    public function invoice($idSpace, $beginPeriod, $endPeriod, $id_client, $idUser, $lang='en')
    {
        $modules = Configuration::get("modules");
        $found = false;
        foreach ($modules as $module) {
            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                if ($model->hasActivity($idSpace, $beginPeriod, $endPeriod, $id_client)) {
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            return false;
        }


        // create invoice in the database
        $modelInvoice = new InInvoice();
        $invoiceNumber = $modelInvoice->getNextNumber($idSpace);
        $id_invoice = $modelInvoice->addInvoice("invoices", "invoiceglobal", $idSpace, 'in progress', date("Y-m-d", time()), $id_client, 0, $beginPeriod, $endPeriod);
        $modelInvoice->setEditedBy($idSpace, $id_invoice, $idUser);
        $modelInvoice->setTitle($idSpace, $id_invoice, InvoicesTranslator::Invoice($lang).": " . CoreTranslator::dateFromEn($beginPeriod, $lang) . " => " . CoreTranslator::dateFromEn($endPeriod, $lang));

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
                    $moduleArray["data"] = $model->invoice($idSpace, $beginPeriod, $endPeriod, $id_client, $id_invoice, $lang);
                    $invoiceDataArray[] = $moduleArray;

                    $total_ht += floatval($moduleArray["data"]["total_ht"]);
                }
            }
        } catch(Exception $e) {
            $modelInvoice->setNumber($idSpace, $id_invoice, 'error');
            throw $e;
        }

        // set invoice content to the database
        $modelInvoice->setTotal($idSpace, $id_invoice, $total_ht);
        $modelInvoice->setNumber($idSpace, $id_invoice, $invoiceNumber);
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->setItem($idSpace, 0, $id_invoice, "invoices", "invoiceglobal", json_encode($invoiceDataArray), "", $total_ht);

        return true;
    }
}
