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

        /*
        $this->setColumnsInfo("id_purchase", "int(11)", 0);
        $this->setColumnsInfo("id_service", "int(11)", 0);
        $this->setColumnsInfo("quantity", "varchar(100)", "0");
        $this->setColumnsInfo("comment", "varchar(255)", "");
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `se_purchase_item` (
            `id_purchase` int NOT NULL DEFAULT 0,
            `id_service` int NOT NULL DEFAULT 0,
            `quantity` varchar(100) NOT NULL DEFAULT 0,
            `comment` varchar(255) DEFAULT NULL,
            `id_space` int NOT NULL DEFAULT 0
          )';
        $this->runRequest($sql);
        $this->baseSchema();
    }

    public function getForPurchase($id_space, $id_purchase) {
        $sql = "SELECT * FROM se_purchase_item WHERE id_purchase=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_purchase, $id_space))->fetchAll();
        $services = array(); $quantities = array();
        foreach($req as $r){
            $services[] = $r["id_service"];
            $quantities[] = $r["quantity"];
        }
        return array("services" => $services, "quantities" => $quantities);
    }
    
    public function getItemQuantity($id_space, $id_service, $id_purchase){
        $sql = "SELECT quantity FROM se_purchase_item WHERE id_purchase=? AND id_service=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_purchase, $id_service, $id_space))->fetch();
    }
    
    public function set($id_space, $id_purchase, $id_service, $quantity, $comment){
        if ($this->ispurchaseItem($id_space, $id_purchase, $id_service)){
            $sql = "UPDATE se_purchase_item SET quantity=?, comment=? WHERE id_purchase=? AND id_service=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($quantity, $comment, $id_purchase, $id_service, $id_space));
        }
        else{
            $sql = "INSERT INTO se_purchase_item (id_purchase, id_service, quantity, comment, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_purchase, $id_service, $quantity, $comment, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function ispurchaseItem($id_space, $id_purchase, $id_service){
        $sql = "SELECT * FROM se_purchase_item WHERE id_purchase=? AND id_service=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_purchase, $id_service, $id_space));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }

    public function delete($id_space, $id_purchase, $id_service){
        $sql = "DELETE FROM se_purchase_item WHERE id_purchase=? AND id_service=? AND id_space=?";
        $this->runRequest($sql, array($id_purchase, $id_service, $id_space));
    }

}
