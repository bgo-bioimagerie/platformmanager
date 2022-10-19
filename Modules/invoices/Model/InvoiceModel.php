<?php

require_once 'Framework/Model.php';


abstract class InvoiceModel extends Model
{
    abstract public function hasActivity($id_space, $beginPeriod, $endPeriod, $id_resp);
    abstract public function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang);
    abstract public function details($id_space, $invoice_id, $lang);
    abstract public function delete($id_space, $id_invoice);
}
