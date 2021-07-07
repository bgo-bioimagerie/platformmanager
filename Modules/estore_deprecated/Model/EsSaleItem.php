<?php

require_once 'Framework/Model.php';

class EsSaleItem extends Model {

    public function __construct() {
        $this->tableName = "es_sale_items";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_sale", "int(11)", 0);
        $this->setColumnsInfo("id_batch", "int(11)", 0);
        $this->setColumnsInfo("quantity", "int(11)", 0);
        $this->setColumnsInfo("price", "float", -1);
        $this->primaryKey = "id";
    }

    public function getitems($id_space, $id_sale) {
        $sql = "SELECT * FROM es_sale_items WHERE id_sale=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_sale, $id_space))->fetchAll();
    }
    
    public function set($id_space, $id, $id_sale, $id_batch, $quantity, $price = -1) {
        if (!$id) {
            $sql = 'INSERT INTO es_sale_items (id_sale, id_batch, quantity, price, id_space) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_sale, $id_batch, $quantity, $price, $id_space));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_sale_items SET id_sale=?, id_batch=?, quantity=?, price=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($id_sale, $id_batch, $quantity, $price, $id, $id_space));
        }
    }
    
    public function delete($id_space, $id) {
        $sql = "UPDATE es_sale_items SET deleted=0,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM es_sale_items WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
