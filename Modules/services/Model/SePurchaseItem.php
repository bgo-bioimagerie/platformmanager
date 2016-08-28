<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SePurchaseItem extends Model {

        public function __construct() {

        $this->tableName = "se_purchase_item";
        $this->setColumnsInfo("id_purchase", "int(11)", 0);
        $this->setColumnsInfo("id_service", "int(11)", 0);
        $this->setColumnsInfo("quantity", "varchar(100)", "0");
        $this->setColumnsInfo("comment", "varchar(255)", "");
    }

    public function getForPurchase($id_purchase) {
        $sql = "SELECT * FROM se_purchase_item WHERE id_purchase=?";
        $req = $this->runRequest($sql, array($id_purchase))->fetchAll();
        $services = array(); $quantities = array();
        foreach($req as $r){
            $services[] = $r["id_service"];
            $quantities[] = $r["quantity"];
        }
        return array("services" => $services, "quantities" => $quantities);
    }
    
    public function getItemQuantity($id_service, $id_purchase){
        $sql = "SELECT quantity FROM se_purchase_item WHERE id_purchase=? AND id_service=?";
        return $this->runRequest($sql, array($id_purchase, $id_service))->fetch();
    }
    
    public function set($id_purchase, $id_service, $quantity, $comment){
        if ($this->ispurchaseItem($id_purchase, $id_service)){
            $sql = "UPDATE se_purchase_item SET quantity=?, comment=? WHERE id_purchase=? AND id_service=?";
            $this->runRequest($sql, array($quantity, $comment, $id_purchase, $id_service));
        }
        else{
            $sql = "INSERT INTO se_purchase_item (id_purchase, id_service, quantity, comment) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_purchase, $id_service, $quantity, $comment));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function ispurchaseItem($id_purchase, $id_service){
        $sql = "SELECT * FROM se_purchase_item WHERE id_purchase=? AND id_service=?";
        $req = $this->runRequest($sql, array($id_purchase, $id_service));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }

    public function delete($id_purchase, $id_service){
        $sql = "DELETE FROM se_purchase_item WHERE id_purchase=?, id_service=?";
        $this->runRequest($sql, array($id_purchase, $id_service));
    }

}
