<?php

require_once 'Framework/Model.php';

class BrProductUnitQ extends Model {

    public function __construct() {
        $this->tableName = "br_product_unit_q";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_product_stage", "int(11)", 0);
        $this->setColumnsInfo("unit_quantity", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM br_product_unit_q WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id_space, $id_product_stage, $unit_quantity) {
        $id = $this->exists($id_product_stage);
        if ($id == 0) {
            $sql = "INSERT INTO br_product_unit_q (id_space, id_product_stage, unit_quantity) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_space, $id_product_stage, $unit_quantity));
        } else {
            $sql = "UPDATE br_product_unit_q SET id_space=?, id_product_stage=?, unit_quantity=? WHERE id=?";
            $this->runRequest($sql, array($id_space, $id_product_stage, $unit_quantity, $id));
        }
    }
    
    public function exists($id_product_stage){
        $sql = "SELECT id FROM br_product_unit_q WHERE id_product_stage=?";
        $req = $this->runRequest($sql, array($id_product_stage));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getquantity($id_product_stage) {
        $sql = "SELECT unit_quantity FROM br_product_unit_q WHERE id_product_stage=?";
        $req = $this->runRequest($sql, array($id_product_stage));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM br_product_unit_q WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
