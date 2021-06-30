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

    public function getitems($id_sale) {
        $sql = "SELECT * FROM es_sale_entered_items WHERE id_sale=?";
        return $this->runRequest($sql, array($id_sale))->fetchAll();
    }
    
    public function getitemsDesc($id_space, $id_sale) {
        $sql = "SELECT * FROM es_sale_entered_items WHERE id_sale=?";
        $data = $this->runRequest($sql, array($id_sale))->fetchAll();
        $modelProduct = new EsProduct($id_space);
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["product"] = $modelProduct->getName($data[$i]["id_product"]);
        }
        return $data;
    }
    
    public function set($id, $id_sale, $id_product, $quantity) {
        if (!$id) {
            $sql = 'INSERT INTO es_sale_entered_items (id_sale, id_product, quantity) VALUES (?,?,?)';
            $this->runRequest($sql, array($id_sale, $id_product, $quantity));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_sale_entered_items SET id_sale=?, id_product=?, quantity=? WHERE id=?';
            $this->runRequest($sql, array($id_sale, $id_product, $quantity, $id));
        }
    }
    
    public function delete($id) {
        $sql = "DELETE FROM es_sale_entered_items WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
