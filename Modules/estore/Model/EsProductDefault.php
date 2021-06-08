<?php

require_once 'Framework/Model.php';

class EsProductDefault extends Model {

    public function __construct() {
        $this->tableName = "es_products";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_category", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("description", "text", "");
        $this->setColumnsInfo("url_image", "varchar(255)", "");
        $this->setColumnsInfo("Quantity", 'int(11)', 0);
        $this->primaryKey = "id";
    }
    
    public function getAll($id_space) {
        $sql = " SELECT es_products.*, es_product_categories.name as category ";
        $sql .= "FROM es_products ";     
        $sql .= "INNER JOIN es_product_categories "; 
        $sql .= "ON es_products.id_category = es_product_categories.id "; 
        $sql .= "WHERE es_products.id_space=?";
        
        return $this->runRequest($sql, array($id_space))->fetchAll();
        
    }
    
    public function getByCategory($id_category){
                $sql = "SELECT * FROM es_products WHERE id_category=?";
        return $this->runRequest($sql, array($id_category))->fetchAll();
    }

    public function getForCategory($id_category) {
        $sql = "SELECT * FROM es_products WHERE id_category=?";
        return $this->runRequest($sql, array($id_category))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM es_products WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getName($id){
        $sql = "SELECT name FROM es_products WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount()){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $id_space, $id_category, $name, $description) {
        if ($id == 0) {
            $sql = 'INSERT INTO es_products (id_space, id_category, name, description) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_space, $id_category, $name, $description));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_products SET id_space=?, id_category=?, name=?, description=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $id_category, $name, $description, $id));
            return $id;
        }
    }

    public function setQuantity($id, $quantity) {
        $sql = 'UPDATE es_products SET quantity=? WHERE id=?';
        $this->runRequest($sql, array($quantity, $id));
    }

    public function setImage($id, $url) {
        $sql = 'UPDATE es_products SET url_image=? WHERE id=?';
        $this->runRequest($sql, array($url, $id));
    }

    public function delete($id) {
        $sql = "DELETE FROM es_products WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
