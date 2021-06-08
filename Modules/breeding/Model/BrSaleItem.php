<?php

require_once 'Framework/Model.php';

class BrSaleItem extends Model {

    public function __construct() {
        $this->tableName = "br_sale_items";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_sale", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->setColumnsInfo("id_batch", "int(11)", 0);
        $this->setColumnsInfo("requested_product", "varchar(255)", "");
        $this->setColumnsInfo("requested_quantity", "int(11)", "");
        $this->setColumnsInfo("quantity", "int(11)", "");
        $this->setColumnsInfo("comment", "text", "");
        
        $this->primaryKey = "id";
    }

    public function getAll($id_sale) {
        $sql = "SELECT * FROM br_sale_items WHERE id_sale=?";
        return $this->runRequest($sql, array($id_sale))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_sale_items WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_sale, $date, $id_batch, $requested_product, 
            $requested_quantity, $quantity, $comment) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_sale_items (id_sale, date, id_batch, '
                    . 'requested_product, requested_quantity, quantity, comment ) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_sale, $date, $id_batch, $requested_product, 
            $requested_quantity, $quantity, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_sale_items SET id_sale=?, date=?, id_batch=?, '
                    . 'requested_product=?, requested_quantity=?, quantity=?,'
                    . ' comment=?  WHERE id=?';
            $this->runRequest($sql, array($id_sale, $date, $id_batch, $requested_product, 
            $requested_quantity, $quantity, $comment, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM br_sale_items WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
