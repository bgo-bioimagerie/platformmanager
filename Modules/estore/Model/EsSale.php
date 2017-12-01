<?php

require_once 'Framework/Model.php';

class EsSale extends Model {

    public function __construct() {
        $this->tableName = "es_sales";
        
        // entered (status is in EsSaleHistory)
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("date_expected", "date", "0000-00-00");
        $this->setColumnsInfo("id_contact_type", "int(11)", 0);
        $this->setColumnsInfo("further_information", "text", "");
        
        // validation info
        $this->setColumnsInfo("date_validated", "date", "0000-00-00");
        
        // quote
        $this->setColumnsInfo("quote_packing_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("quote_delivery_price", "DECIMAL(9,2)", 0);
        
        // delivery
        $this->setColumnsInfo("purchase_order_num", "varchar(255)", "");
        $this->setColumnsInfo("id_delivery_method", "int(11)", 0);
        $this->setColumnsInfo("date_delivery", "date", "0000-00-00");
        
        // Pricing
        $this->setColumnsInfo("packing_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("delivery_price", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("discount", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("total_ht", "DECIMAL(9,2)", 0);
        $this->setColumnsInfo("total_ttc", "DECIMAL(9,2)", 0);
        
        // cancel
        $this->setColumnsInfo("cancel_reason", "varchar(255)", "");
        $this->setColumnsInfo("cancel_date", "date", "0000-00-00");
        
        // status
        $this->setColumnsInfo("id_status", "int(11)", 0);

        $this->primaryKey = "id";
    }
    
    public function get($id){
        $sql = "SELECT * FROM es_sales WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function updateStatus($id){
        
        $sql = "SELECT MAX(id_status) AS id_status FROM es_sale_history WHERE id_sale=?";
        $tmp = $req = $this->runRequest($sql, array($id))->fetch();
        
        $sql2 = "UPDATE es_sales SET id_status=? WHERE id=?";
        $this->runRequest($sql2, array($tmp[0], $id));
        
    }
    
    public function setEntered($id, $id_space, $id_client, $date_expected, $id_contact_type, $further_information){
        if ($id == 0){
            $sql = "INSERT INTO es_sales (id_space, id_client, date_expected, id_contact_type, further_information) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $id_client, $date_expected, $id_contact_type, $further_information));
            return $this->getDatabase()->lastInsertId();
        }
        else{
            $sql = "UPDATE es_sales SET id_space=?, id_client=?, date_expected=?, id_contact_type=?, further_information=? WHERE id=?";
            $this->runRequest($sql, array($id_space, $id_client, $date_expected, $id_contact_type, $further_information, $id));
            return $id;
        }
    }
    
    public function setInProgress($id, $date_validated){
        $sql = "UPDATE es_sales SET date_validated=? WHERE id=?";
        $this->runRequest($sql, array($date_validated, $id));
    } 
    
    public function setQuote($id, $quote_packing_price, $quote_delivery_price){
        $sql = "UPDATE es_sale SET quote_packing_price=?, quote_delivery_price=? WHERE id=?";
        $this->runRequest($sql, array($quote_packing_price, $quote_delivery_price, $id));
    }
  
    public function setSent($id, $purchase_order_num, $id_delivery_method, $date_delivery){
        $sql = "UPDATE es_sales SET purchase_order_num=?, id_delivery_method=?, date_delivery=? WHERE id=?";
        $this->runRequest($sql, array($purchase_order_num, $id_delivery_method, $date_delivery, $id));
    }
    
    public function setSold($id, $packing_price, $delivery_price, $discount, $total_ht, $total_ttc){
        $sql = "UPDATE es_sales SET packing_price=?, delivery_price=?, discount=?, total_ht=?, total_ttc=? WHERE id=?";
        $this->runRequest($sql, array($packing_price, $delivery_price, $discount, $total_ht, $total_ttc, $id));
    }
    
    public function setCancel($id, $cancel_reason, $cancel_date){
        $sql = "UPDATE es_sales SET cancel_reason=?, cancel_date=? WHERE id=?";
        $this->runRequest($sql, array($cancel_reason, $cancel_date, $id));
    }
    
    public function delete($id) {
        $sql = "DELETE FROM br_sales WHERE id=?";
        $this->runRequest($sql, array($id));
        
    }
    
    public function count($id_space, $id_status){
        $sql = "SELECT id FROM es_sales WHERE id_space=? AND id_status=?";
        return $this->runRequest($sql, array($id_space, $id_status))->rowCount();
    }
    
    public function getForSpace($id_space, $id_status){
        $sql = "SELECT * FROM es_sales WHERE id_space=? AND id_status=?";
        $data = $this->runRequest($sql, array($id_space, $id_status))->fetchAll();
        
        $modelClient = new ClClient();
        $modelContactType = new EsContactType();
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["client"] = $modelClient->getName($data[$i]["id_client"]);
            $data[$i]["contact_type"] = $modelContactType->getName($data[$i]["id_contact_type"]);
            $data[$i]["number"] = '#' . $data[$i]["id"];
        }
        return $data;
    }
    

}
