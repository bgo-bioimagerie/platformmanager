<?php

require_once 'Framework/Model.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InVisa.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class InvoicesInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {

        $modelInvoice = new InInvoice();
        $modelInvoice->createTable();

        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoiceItem->createTable();

        $modelVisa = new InVisa();
        $modelVisa->createTable();

        if (!file_exists('data/invoices/')) {
            mkdir('data/invoices/', 0777, true);
        }
    }

}
