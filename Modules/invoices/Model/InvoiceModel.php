<?php

require_once 'Framework/Model.php';


abstract class InvoiceModel extends Model {
 
    public abstract function hasActivity($id_space, $beginPeriod, $endPeriod, $id_resp);
    public abstract function invoice($id_space, $beginPeriod, $endPeriod, $id_resp, $invoice_id, $lang);
    public abstract function details($id_space, $invoice_id, $lang);
    public abstract function delete($id_invoice);
    
}