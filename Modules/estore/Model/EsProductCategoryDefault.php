<?php

require_once 'Framework/Model.php';

class EsProductCategoryDefault extends Model {

    public function __construct() {
        $this->tableName = "es_product_categories";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("description", "text", "");
        $this->primaryKey = "id";
    }
    
    public function getFirstId($id_sapce){
        $sql = "SELECT id FROM es_product_categories WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_sapce));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM es_product_categories WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function get($id) {
        $sql = "SELECT * FROM es_product_categories WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getName($id) {
        $sql = "SELECT * FROM es_product_categories WHERE id=?";
        $data = $this->runRequest($sql, array($id));
        if ($data->rowCount() > 0){
            $tmp = $data->fetch();
            return $tmp[0];
        }
        else{
            return "";
        }
    }

    public function set($id, $id_space, $name, $description) {
        if ($id == 0) {
            $sql = 'INSERT INTO es_product_categories (id_space, name, description) VALUES (?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $description));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_product_categories SET id_space=?, name=?, description=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $description, $id));
            return $id;
        }
    }
    
    public function getForList($id_space){
        $sql = "SELECT * FROM es_product_categories WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM es_product_categories WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
