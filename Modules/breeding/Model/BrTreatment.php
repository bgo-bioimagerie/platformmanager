<?php

require_once 'Framework/Model.php';

class BrTreatment extends Model {

    public function __construct() {
        $this->tableName = "br_treatment";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_batch", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->setColumnsInfo("antibiotic", "varchar(255)", "");
        $this->setColumnsInfo("suppressor", "varchar(255)", "");
        $this->setColumnsInfo("water", "varchar(255)", "");
        $this->setColumnsInfo("food", "varchar(255)", "");
        $this->setColumnsInfo("comment", "text", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_batch) {
        $sql = "SELECT * FROM br_treatment WHERE id_batch=? ORDER BY date DESC";
        return $this->runRequest($sql, array($id_batch))->fetchAll();
    }

    public function get($id) {
        if($id == 0){
            return array(
                "date" => date("Y-m-d", time()),
                "antibiotic" => "",
                "suppressor" => "",
                "water" => "",
                "food" => "",
                "comment" => "",
            );
        }
        $sql = "SELECT * FROM br_treatment WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_batch, $date, $antibiotic, $suppressor, $water, $food, $comment) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_treatment (id_batch, date, antibiotic, '
                    . 'suppressor, water, food, comment) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_batch, $date, $antibiotic, $suppressor, $water, $food, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_treatment SET id_batch=?, date=?, antibiotic=?, '
                    . 'suppressor=?, water=?, food=?, comment=? WHERE id=?';
            $this->runRequest($sql, array($id_batch, $date, $antibiotic, $suppressor, 
                $water, $food, $comment, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM br_treatment WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
