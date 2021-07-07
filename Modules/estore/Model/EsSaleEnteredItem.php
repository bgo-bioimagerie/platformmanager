<?php

require_once 'Framework/Model.php';
require_once 'Modules/estore/Model/EsProduct.php';

class EsSaleEnteredItem extends Model {

    public function __construct() {
        $this->tableName = "es_sale_entered_items";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_sale", "int(11)", 0);
        $this->setColumnsInfo("id_product", "int(11)", "");
        $this->setColumnsInfo("quantity", "int(11)", "");
        $this->primaryKey = "id";
    }

    public function getitems($id_space, $id_sale) {
        $sql = "SELECT * FROM es_sale_entered_items WHERE id_sale=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_sale, $id_space))->fetchAll();
    }
    
    public function getitemsDesc($id_space, $id_sale) {
        $sql = "SELECT * FROM es_sale_entered_items WHERE id_sale=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_sale, $id_space))->fetchAll();
        $modelProduct = new EsProduct($id_space);
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["product"] = $modelProduct->getName($id_space, $data[$i]["id_product"]);
        }
        return $data;
    }
    
    public function set($id_space, $id, $id_sale, $id_product, $quantity) {
        if (!$id) {
            $sql = 'INSERT INTO es_sale_entered_items (id_sale, id_product, quantity, id_space) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_sale, $id_product, $quantity, $id_space));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_sale_entered_items SET id_sale=?, id_product=?, quantity=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($id_sale, $id_product, $quantity, $id, $id_space));
        }
    }
    
    public function delete($id_space, $id) {
        $sql = "UPDATE es_sale_entered_items SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM es_sale_entered_items WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
