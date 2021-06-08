<?php

require_once 'Framework/Model.php';

class EsCancelReason extends Model {

    public function __construct() {
        $this->tableName = "es_cancel_reasons";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM es_cancel_reasons WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM es_cancel_reasons WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id){
        $sql = "SELECT name FROM es_cancel_reasons WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];

    }
    
    public function exists($name){
        $sql = "SELECT id FROM es_cancel_reasons WHERE name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $id_space, $name) {
        if ($id == 0) {
            $sql = 'INSERT INTO es_cancel_reasons (id_space, name) VALUES (?,?)';
            $this->runRequest($sql, array($id_space, $name));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_cancel_reasons SET id_space=?, name=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $id));
            return $id;
        }
    }

    public function getForList($id_space){
        $sql = "SELECT * FROM es_cancel_reasons WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM es_cancel_reasons WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
