<?php

require_once 'Framework/Model.php';

class ClPricing extends Model {

    public function __construct() {
        //$this->tableName = "cl_pricings";
        //$this->setColumnsInfo("id", "int(11)", "");
        //$this->setColumnsInfo("id_space", "int(11)", 0);
        //$this->setColumnsInfo("name", "varchar(255)", "");
        //$this->setColumnsInfo("color", "varchar(7)", "");
        //$this->setColumnsInfo("type", "int(1)", 0);
        //$this->setColumnsInfo("display_order", "int(11)", 0);
        
        $this->primaryKey = "id";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `cl_pricings` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL DEFAULT '0',
            `name` varchar(255) DEFAULT NULL,
            `color` varchar(7) DEFAULT NULL,
            `txtcolor` varchar(7) DEFAULT NULL,
            `type` int NOT NULL DEFAULT '0',
            `display_order` int NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }    

    public function getAll($id_space) {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id_space, $id) {
        if(!$id) {
            return [
                "id" => 0,
                "name" => "",
                "color" => "#ffffff",
                "txtcolor" => "#000000",
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

    
    
    public function set($id, $id_space, $name, $color, $type, $display_order, $txtcolor="#000000") {
        if (!$id) {
            $sql = 'INSERT INTO cl_pricings (id_space, name, color, type, display_order, txtcolor) VALUES (?,?,?,?,?, ?)';
            $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order, $txtcolor));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_pricings SET name=?, color=?, type=?, display_order=?, txtcolor=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $color, $type, $display_order, $txtcolor, $id, $id_space));
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

    /**
     * Get a client's pricing
     * 
     * @param int|string $id_space
     * @param int|string $id_client
     * 
     * @return array(string) pricings
     */
    public function getPricingByClient($id_space, $id_client) {
        $sql =
            "SELECT cl_pricings.* FROM cl_pricings
            INNER JOIN cl_clients
            ON cl_clients.pricing = cl_pricings.id
            WHERE cl_clients.id_space = ?
            AND cl_clients.id = ?
            AND cl_pricings.deleted = 0";
            return $this->runRequest($sql, array($id_space, $id_client))->fetchAll();
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE cl_pricings SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
