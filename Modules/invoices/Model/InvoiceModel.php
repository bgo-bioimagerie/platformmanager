<?php

require_once 'Framework/Model.php';


abstract class InvoiceModel extends Model
{
    abstract public function hasActivity($idSpace, $beginPeriod, $endPeriod, $id_resp);
    abstract public function invoice($idSpace, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang);
    abstract public function details($idSpace, $invoice_id, $lang);
    abstract public function delete($idSpace, $id_invoice);
}
