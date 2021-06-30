<?php

require_once 'Framework/Model.php';

class BrLosseType extends Model {

    public function __construct() {
        $this->tableName = "br_losse_types";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_losse_types WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_losse_types WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM br_losse_types WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }

    public function getIdFromName($name) {
        $sql = "SELECT id FROM br_losse_types WHERE name=?";
        $d = $this->runRequest($sql, array($name))->fetch();
        return $d[0];
    }

    public function set($id, $id_space, $name) {
        if (!$id) {
            $sql = 'INSERT INTO br_losse_types (id_space, name) VALUES (?,?)';
            $this->runRequest($sql, array($id_space, $name));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_losse_types SET id_space=?, name=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $id));
            return $id;
        }
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM br_losse_types WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM br_losse_types WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
