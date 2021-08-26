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
        $sql .= "WHERE es_products.id_space=? AND es_products.deleted=0";
        
        return $this->runRequest($sql, array($id_space))->fetchAll();
        
    }
    
    public function getByCategory($id_space, $id_category){
            $sql = "SELECT * FROM es_products WHERE id_category=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_category, $id_space))->fetchAll();
    }

    public function getForCategory($id_space, $id_category) {
        return $this->getByCategory($id_space, $id_category);
        // $sql = "SELECT * FROM es_products WHERE id_category=? AND id_space=? AND deleted=0";
        // return $this->runRequest($sql, array($id_category, $id_space))->fetchAll();
    }

    public function get($id_space ,$id) {
        $sql = "SELECT * FROM es_products WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getName($id_space, $id){
        $sql = "SELECT name FROM es_products WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount()){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $id_space, $id_category, $name, $description) {
        if (!$id) {
            $sql = 'INSERT INTO es_products (id_space, id_category, name, description) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_space, $id_category, $name, $description));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_products SET id_category=?, name=?, description=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($id_category, $name, $description, $id, $id_space));
            return $id;
        }
    }

    public function setQuantity($id_space, $id, $quantity) {
        $sql = 'UPDATE es_products SET quantity=? WHERE id=? AND id_space=? AND deleted=0';
        $this->runRequest($sql, array($quantity, $id, $id_space));
    }

    public function setImage($id_space, $id, $url) {
        $sql = 'UPDATE es_products SET url_image=? WHERE id=? AND id_space=? AND deleted=0';
        $this->runRequest($sql, array($url, $id, $id_space));
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE es_products SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM es_products WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
