<?php

require_once 'Framework/Model.php';

class BrProduct extends Model {

    public function __construct() {
        $this->tableName = "br_products";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("code", "varchar(255)", "");
        $this->setColumnsInfo("description", "text", "");
        $this->setColumnsInfo("id_category", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_products WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        if($id == 0){
            return array(
                "id" => 0,
                "name" => "",
                "code" => "",
                "description" => "",
                "id_category" => 0
            );
        }
        $sql = "SELECT * FROM br_products WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_space, $name, $code, $description, $id_category) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_products (id_space, name, code, description, id_category) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $code, $description, $id_category));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_products SET id_space=?, name=?, code=?, description=?, id_category=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $code, $description, $id_category, $id));
            return $id;
        }
    }
    
    public function getIdFromName($name){
        $sql = "SELECT id FROM br_products WHERE name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() > 0){
            $tmp =  $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getForList($id_space){
        $sql = "SELECT * FROM br_products WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM br_products WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
