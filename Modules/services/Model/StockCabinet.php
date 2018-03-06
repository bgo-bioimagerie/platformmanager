<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class StockCabinet extends Model {

    public function __construct() {
        $this->tableName = "stock_cabinets";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("room_number", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getForList($id_space){
        $sql = "SELECT id, name, room_number FROM stock_cabinets WHERE id_space=? ORDER BY name ASC;";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        
        $names = array();
        $ids = array();
        foreach($data as $d){
            $names[] = $d["room_number"] . " - " . $d["name"];
            $ids[] = $d["id"];
        }
        return array( "names" => $names, "ids" => $ids );
    }
    
    public function getAll($id_space){
        $sql = "SELECT * FROM stock_cabinets WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getOne($id){
        $sql = "SELECT * FROM stock_cabinets WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_space, $name, $room_number){
        
        if ($id > 0){
            $sql = "UPDATE stock_cabinets SET id_space=?, name=?, room_number=? WHERE id=?";
            $this->runRequest($sql, array(
                $id_space, $name, $room_number, $id
            ));
            return $id;
        }
        else{
            $sql = "INSERT INTO stock_cabinets (id_space, name, room_number) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_space, $name, $room_number));
            return $this->getDatabase()->lastInsertId();
        }
        
    }
    
    public function delete($id) {

        $sql = "DELETE FROM stock_cabinets WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
