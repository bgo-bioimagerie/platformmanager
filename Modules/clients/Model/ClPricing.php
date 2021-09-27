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
        $sql = "SELECT * FROM cl_pricings WHERE id_space=? AND deleted=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id_space, $id) {
        if(!$id) {
            return [
                "id" => 0,
                "name" => "",
                "color" => "",
                "type" => 0,
                "display_order" => 0
            ];
        }
        $sql = "SELECT * FROM cl_pricings WHERE id=?  AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getIdFromName($name, $id_space) {
        $sql = "SELECT id FROM cl_pricings WHERE name=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($name, $id_space));
        if ( $tmp->rowCount() > 0 ){
            $tm = $tmp->fetch();
            return $tm[0];
        }
        return 0;
    }

    public function getName($id_space, $id) {
        $sql = "SELECT name FROM cl_pricings WHERE id=? AND id_space=? AND deleted=0";
        $d = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $d ? $d[0]: null;
    }

    
    
    public function set($id, $id_space, $name, $color, $type, $display_order) {
        if (!$id) {
            $sql = 'INSERT INTO cl_pricings (id_space, name, color, type, display_order) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_pricings SET name=?, color=?, type=?, display_order=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $color, $type, $display_order, $id, $id_space));
            return $id;
        }
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE cl_pricings SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
