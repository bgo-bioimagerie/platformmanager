<?php

require_once 'Framework/Model.php';

class BrChipping extends Model {

    public function __construct() {
        $this->tableName = "br_chipping";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_batch", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->setColumnsInfo("chip_number", "varchar(255)", "");
        $this->setColumnsInfo("comment", "text", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_batch) {
        $sql = "SELECT * FROM br_chipping WHERE id_batch=? ORDER BY date DESC";
        return $this->runRequest($sql, array($id_batch))->fetchAll();
    }

    public function get($id) {
        if($id == 0){
            return array(
                "date" => date("Y-m-d", time()),
                "chip_number" => "",
                "comment" => ""
            );
        }
        $sql = "SELECT * FROM br_chipping WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_batch, $date, $chip_number, $comment) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_chipping (id_batch, date, chip_number, '
                    . 'comment) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_batch, $date, $chip_number, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_chipping SET id_batch=?, date=?, chip_number=?, '
                    . 'comment=? WHERE id=?';
            $this->runRequest($sql, array($id_batch, $date, $chip_number, $comment, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM br_chipping WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
