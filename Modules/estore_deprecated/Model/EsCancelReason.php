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

    public function get($id_space, $id) {
        $sql = "SELECT * FROM es_cancel_reasons WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getName($id_space, $id){
        $sql = "SELECT name FROM es_cancel_reasons WHERE id=? AND id_space=? AND deleted=0";
        $d = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $d[0];

    }
    
    public function exists($id_space, $name){
        $sql = "SELECT id FROM es_cancel_reasons WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $id_space, $name) {
        if (!$id) {
            $sql = 'INSERT INTO es_cancel_reasons (id_space, name) VALUES (?,?)';
            $this->runRequest($sql, array($id_space, $name));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_cancel_reasons SET name=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $id, $id_space));
            return $id;
        }
    }

    public function getForList($id_space){
        $sql = "SELECT * FROM es_cancel_reasons WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE es_cancel SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM es_cancel_reasons WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
