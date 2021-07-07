<?php

require_once 'Framework/Model.php';

class EsProductUnitQ extends Model {

    public function __construct() {
        $this->tableName = "es_product_unit_q";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_product", "int(11)", 0);
        $this->setColumnsInfo("unit_quantity", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM es_product_unit_q WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function set($id_space, $id_product, $unit_quantity) {
        $id = $this->exists($id_space, $id_product);
        if (!$id) {
            $sql = "INSERT INTO es_product_unit_q (id_space, id_product, unit_quantity) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_space, $id_product, $unit_quantity));
        } else {
            $sql = "UPDATE es_product_unit_q SET id_product=?, unit_quantity=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_product, $unit_quantity, $id, $id_space));
        }
    }
    
    public function exists($id_space, $id_product){
        $sql = "SELECT id FROM es_product_unit_q WHERE id_product=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_product, $id_space));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getquantity($id_space ,$id_product) {
        $sql = "SELECT unit_quantity FROM es_product_unit_q WHERE id_product=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_product, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE es_product_unit_q SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM es_product_unit_q WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
