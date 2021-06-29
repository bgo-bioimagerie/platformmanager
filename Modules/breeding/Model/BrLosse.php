<?php

require_once 'Framework/Model.php';

class BrLosse extends Model {

    public function __construct() {
        $this->tableName = "br_losses";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_batch", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("quantity", "int(4)", "");
        $this->setColumnsInfo("comment", "text", "");
        $this->setColumnsInfo("id_type", "text", "");

        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_losses WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getForBatch($id_batch){
        $sql = "SELECT * FROM br_losses WHERE id_batch=?";
        return $this->runRequest($sql, array($id_batch))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_losses WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_space, $id_batch, $date, $id_user, $quantity, $comment, $id_type ) {
        if (!$id) {
            $sql = 'INSERT INTO br_losses (id_space, id_batch, date, id_user, quantity, comment, id_type) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array( $id_space, $id_batch, $date, $id_user, $quantity, $comment, $id_type ));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_losses SET id_space=?, id_batch=?, date=?, id_user=?, quantity=?, comment=?, id_type=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $id_batch, $date, $id_user, $quantity, $comment, $id_type, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM br_losses WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
