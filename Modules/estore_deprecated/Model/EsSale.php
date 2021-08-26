<?php

require_once 'Framework/Model.php';

class EsSale extends Model {

    public function __construct() {
        $this->tableName = "es_sales";

        // entered (status is in EsSaleHistory)
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("date_expected", "date", "0000-00-00");
        $this->setColumnsInfo("id_contact_type", "int(11)", 0);
        $this->setColumnsInfo("further_information", "text", "");

        // validation info
        $this->setColumnsInfo("date_validated", "date", "0000-00-00");
        $this->setColumnsInfo("feasible", "int(1)", 0);
        $this->setColumnsInfo("not_feasible_reason", "int(1)", 0);
        $this->setColumnsInfo("feasible_comment", "text", "");
        

        // quote
        $this->setColumnsInfo("quote_delivery_date", "date", "0000-00-00");
        $this->setColumnsInfo("quote_delivery_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("quote_packing_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("quote_sent_date", "date", "0000-00-00");
        
        // quotesent
        $this->setColumnsInfo("purchase_order_num", "varchar(255)", "");
        $this->setColumnsInfo("purchase_order_file", "varchar(255)", "");

        // delivery
        $this->setColumnsInfo("delivery_type", "int(11)", 0);
        $this->setColumnsInfo("delivery_date_expected", "date", "0000-00-00");

        // Pricing
        $this->setColumnsInfo("invoice_delivery_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("invoice_packing_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("invoice_sent_date", "date", "0000-00-00");
        
        // paid
        $this->setColumnsInfo("paid_date", "DATE", "0000-00-00");
        $this->setColumnsInfo("paid_amount", "DECIMAL(9,2)", 0);
        
        // cancel
        $this->setColumnsInfo("cancel_reason", "int(11)", 0);
        $this->setColumnsInfo("cancel_description", "text", "");

        // status
        $this->setColumnsInfo("id_status", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM es_sales WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function updateStatus($id_space, $id) {

        $sql = "SELECT MAX(id_status) AS id_status FROM es_sale_history WHERE id_sale=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();

        $sql2 = "UPDATE es_sales SET id_status=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql2, array($tmp[0], $id, $id_space));
    }

    public function setEntered($id, $id_space, $id_client, $date_expected, $id_contact_type, $further_information) {
        if (!$id) {
            $sql = "INSERT INTO es_sales (id_space, id_client, date_expected, id_contact_type, further_information) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $id_client, $date_expected, $id_contact_type, $further_information));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE es_sales SET id_client=?, date_expected=?, id_contact_type=?, further_information=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_client, $date_expected, $id_contact_type, $further_information, $id, $id_space));
            return $id;
        }
    }

    public function setInProgress($id_space, $id, $date_validated) {
        $sql = "UPDATE es_sales SET date_validated=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($date_validated, $id, $id_space));
    }

    public function setTodoQuote($id_space, $id, $quote_delivery_date, $quote_delivery_price, $quote_packing_price, $quote_sent_date) {
        $sql = "UPDATE es_sales SET quote_delivery_date=?, quote_delivery_price=?, quote_packing_price=?, quote_sent_date=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($quote_delivery_date, $quote_delivery_price, $quote_packing_price, $quote_sent_date, $id, $id_space));
    }

    public function setQuoteSent($id_space, $id, $purchase_order_num) {
        $sql = "UPDATE es_sales SET purchase_order_num=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($purchase_order_num, $id, $id_space));
    }

    public function setQuoteSentFile($id_space, $id, $url) {
        $sql = "UPDATE es_sales SET purchase_order_file=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($url, $id, $id_space));
    }

    public function setDelivery($id_space, $id, $delivery_type, $delivery_date_expected) {
        $sql = "UPDATE es_sales SET delivery_type=?, delivery_date_expected=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($delivery_type, $delivery_date_expected, $id, $id_space));
    }

    public function setInvoice($id_space, $id, $invoice_packing_price, $invoice_delivery_price, $invoice_sent_date) {
        $sql = "UPDATE es_sales SET invoice_packing_price=?, invoice_delivery_price=?, invoice_sent_date=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($invoice_packing_price, $invoice_delivery_price, $invoice_sent_date, $id, $id_space));
    }
    
    public function setPaid($id_space, $id, $paid_amount, $paid_date){
        $sql = "UPDATE es_sales SET paid_amount=?, paid_date=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($paid_amount, $paid_date, $id, $id_space));
    }
    
    public function setCanceled($id_space, $id, $cancel_reason, $cancel_description){
        $sql = "UPDATE es_sales SET cancel_reason=?, cancel_description=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($cancel_reason, $cancel_description, $id, $id_space));
    }
    
    public function delete($id_space, $id) {
        $sql = "UPDATE br_sales SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM br_sales WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    public function count($id_space, $id_status) {
        $sql = "SELECT id FROM es_sales WHERE id_space=? AND id_status=? AND deleted=0";
        return $this->runRequest($sql, array($id_space, $id_status))->rowCount();
    }

    public function getForSpace($id_space, $id_status) {
        $sql = "SELECT * FROM es_sales WHERE id_space=? AND id_status=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space, $id_status))->fetchAll();

        $modelClient = new ClClient();
        $modelContactType = new EsContactType();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["client"] = $modelClient->getName($id_space, $data[$i]["id_client"]);
            $data[$i]["contact_type"] = $modelContactType->getName($id_space, $data[$i]["id_contact_type"]);
            $data[$i]["number"] = '#' . $data[$i]["id"];
        }
        return $data;
    }

}
