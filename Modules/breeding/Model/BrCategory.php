<?php

require_once 'Framework/Model.php';

class BrCategory extends Model {

    public function __construct() {
        $this->tableName = "br_categories";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("description", "text", "");
        $this->setColumnsInfo("vat", "float", 0);
        $this->primaryKey = "id";
    }

    public function getFirstId($id_sapce) {
        $sql = "SELECT id FROM br_categories WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_sapce));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_categories WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_categories WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getVat($id){
        $sql = "SELECT vat FROM br_categories WHERE id=?";
        $tmp =  $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function getName($id) {
        $sql = "SELECT * FROM br_categories WHERE id=?";
        $data = $this->runRequest($sql, array($id));
        if ($data->rowCount() > 0) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function set($id, $id_space, $name, $description, $vat) {
        if (!$id) {
            $sql = 'INSERT INTO br_categories (id_space, name, description, vat) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $description, $vat));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_categories SET id_space=?, name=?, description=?, vat=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $description, $vat, $id));
            return $id;
        }
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM br_categories WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function getIdFromName($name) {
        $sql = "SELECT id FROM br_categories WHERE name=?";
        $d = $this->runRequest($sql, array($name))->fetch();
        return $d[0];
    }

    public function delete($id) {
        $sql = "DELETE FROM br_categories WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
