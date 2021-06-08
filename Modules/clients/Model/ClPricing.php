<?php

require_once 'Framework/Model.php';

class ClPricing extends Model {

    public function __construct() {
        $this->tableName = "cl_pricings";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("color", "varchar(7)", "");
        $this->setColumnsInfo("type", "int(1)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM cl_pricings WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getIdFromName($name, $id_space) {
        $sql = "SELECT id FROM cl_pricings WHERE name=? AND id_space=?";
        $tmp = $this->runRequest($sql, array($name, $id_space));
        if ( $tmp->rowCount() > 0 ){
            $tm = $tmp->fetch();
            return $tm[0];
        }
        return 0;
    }

    public function getName($id) {
        $sql = "SELECT name FROM cl_pricings WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }

    
    
    public function set($id, $id_space, $name, $color, $type, $display_order) {
        if ($id == 0) {
            $sql = 'INSERT INTO cl_pricings (id_space, name, color, type, display_order) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_pricings SET id_space=?, name=?, color=?, type=?, display_order=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order, $id));
            return $id;
        }
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=?";
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
        $sql = "DELETE FROM cl_pricings WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
