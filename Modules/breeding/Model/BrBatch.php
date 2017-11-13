<?php

require_once 'Framework/Model.php';

class BrBatch extends Model {

    public function __construct() {
        $this->tableName = "br_batchs";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("reference", "varchar(255)", "");
        $this->setColumnsInfo("created", "date", "");
        $this->setColumnsInfo("id_male_spawner", "int(11)", ""); // ref to a batch
        $this->setColumnsInfo("id_female_spawner", "int(11)", ""); // ref to a batch
        $this->setColumnsInfo("id_destination", "int(11)", ""); // 1 sale, 2 Lab
        $this->setColumnsInfo("id_product", "int(11)", "");
        $this->setColumnsInfo("quantity", "int(11)", 0);
        $this->setColumnsInfo("quantity_start", "int(11)", 0);
        $this->setColumnsInfo("quantity_losse", "int(11)", 0);
        $this->setColumnsInfo("quantity_sale", "int(11)", 0);
        $this->setColumnsInfo("chipped", "int(1)", ""); // 0 vs 1
        $this->setColumnsInfo("comment", "text", ""); 

        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_batchs WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getInProgress($id_space){
        $sql = "SELECT * FROM br_batchs WHERE id_space=? AND quantity>0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
        public function getArchives($id_space){
        $sql = "SELECT * FROM br_batchs WHERE id_space=? AND quantity=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function get($id) {
        $sql = "SELECT * FROM br_batchs WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getName($id){
        $sql = "SELECT reference FROM br_batchs WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();
        return $data[0];
    }

    public function set($id, $id_space, $reference, $created, $id_male_spawner, $id_female_spawner, 
                        $id_destination, $id_product,  $chipped, $comment ) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_batchs (id_space, reference, created, id_male_spawner, id_female_spawner, 
            id_destination, id_product,  chipped, comment) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array( $id_space, $reference, $created, $id_male_spawner, $id_female_spawner, 
            $id_destination, $id_product,  $chipped, $comment ));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_batchs SET id_space=?, reference=?, created=?, id_male_spawner=?, id_female_spawner=?, 
            id_destination=?, id_product=?, chipped=?, comment=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $reference, $created, $id_male_spawner, $id_female_spawner, 
            $id_destination, $id_product,  $chipped, $comment, $id));
            return $id;
        }
    }

    public function setQuantity($id, $quantity){
        $sql = "UPDATE br_batchs SET quantity=? WHERE id=?";
        $this->runRequest($sql, array($quantity, $id));
    }

    public function getForList($id_space){
        $sql = "SELECT * FROM br_batchs WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        $names[] = "";
        $ids[] = 0;
        foreach($data as $d){
            $names[] = $d["reference"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM br_batchs WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
