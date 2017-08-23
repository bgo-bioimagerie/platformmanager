<?php

require_once 'Framework/Model.php';

class Provider extends Model {

    public function __construct() {
        $this->tableName = "tuto_providers";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("phone", "varchar(20)", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM providers WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM providers WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_space, $name, $address, $phone) {
        if ($id == 0) {
            $sql = 'INSERT INTO providers (id_space, name, address, phone) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $address, $phone));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE providers SET id_space=?, name=?, address=?, phone=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $address, $phone, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM providers WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
