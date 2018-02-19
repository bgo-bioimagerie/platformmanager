<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class StockShelf extends Model {

    public function __construct() {
        $this->tableName = "stock_shelf";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("id_cabinet", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getAll($id_space){
        $sql  = " SELECT stock_shelf.*, stock_cabinets.name as cabinet ";
        $sql .= " FROM stock_shelf ";
        $sql .= " INNER JOIN stock_cabinets ON stock_shelf.id_cabinet=stock_cabinets.id ";
        $sql .= " WHERE stock_shelf.id_cabinet IN (SELECT id FROM stock_cabinets WHERE stock_cabinets.id_space=?)";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getFullName($id){
        
        if (!isset($id) || is_null($id) || $id == ""){
            return "";
        }
        
        $sql  = " SELECT stock_shelf.name as shelf, stock_cabinets.name as cabinet ";
        $sql .= " FROM stock_shelf ";
        $sql .= " INNER JOIN stock_cabinets ON stock_shelf.id_cabinet=stock_cabinets.id ";
        $sql .= " WHERE stock_shelf.id = ?";
        $req = $this->runRequest($sql, array($id));
        if ( $req->rowCount() > 0 ){
            $data = $req->fetch();
            return $data["cabinet"] . " - " . $data["shelf"]; 
        }
        return "";
    }
    
    public function getOne($id){
        $sql = "SELECT * FROM stock_shelf WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getAllForProjectSelect($id_space){
        
        $data = $this->getAll($id_space);
        
        $names = array();
        $ids = array();
        for($i = 0 ; $i < count($data) ; $i++){
            $ids[] = $data[$i]["id"];
            $names[] = $data[$i]["cabinet"] . ": " . $data[$i]["name"];
        }
        return array( "names" => $names, "ids" => $ids );
    }

    public function set($id, $name, $id_cabinet){
        
        if ($id > 0){
            $sql = "UPDATE stock_shelf SET name=?, id_cabinet=? WHERE id=?";
            $this->runRequest($sql, array(
                $name, $id_cabinet, $id
            ));
            return $id;
        }
        else{
            $sql = "INSERT INTO stock_shelf (name, id_cabinet) VALUES (?,?)";
            $this->runRequest($sql, array($name, $id_cabinet));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function delete($id) {

        $sql = "DELETE FROM stock_shelf WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
