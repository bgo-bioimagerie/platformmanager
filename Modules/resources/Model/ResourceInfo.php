<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model
 * @author Sylvain Prigent
 */
class ResourceInfo extends Model {

    /**
     * Create the unit table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_info";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(150)", "");
        $this->setColumnsInfo("brand", "varchar(250)", "");
        $this->setColumnsInfo("type", "varchar(250)", "");
        $this->setColumnsInfo("desciption", "text", "");
        $this->setColumnsInfo("id_category", "int(11)", 0);
        $this->setColumnsInfo("id_area", "int(11)", 0);
        $this->setColumnsInfo("id_site", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getDefault(){
        return array(
            "id" => 0,
            "name" => "",
            "brand" => "",
            "type" => "",
            "desciption" => "",
            "id_category" => 0,
            "id_area" => 0,
            "id_site" => 0,
            "display_order" => 0
        );
    }
    
    public function getAll($sort = "name"){
        $sql = "SELECT * FROM re_info ORDER BY ". $sort." ASC";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function get($id){
        $sql = "SELECT * FROM re_info WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getName($id){
        $sql = "SELECT name FROM re_info WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }
    
    public function set($id, $name, $brand, $type, $desciption, $id_category, $id_area, $id_site, $display_order){
       
        if (!$this->exists($id)){
            $sql = "INSERT INTO re_info (name, brand, type, desciption, id_category, id_area, id_site, display_order) "
                    . "VALUES (?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($name, $brand, $type, $desciption, $id_category, $id_area, $id_site, $display_order));
            return $this->getDatabase()->lastInsertId();
        }
        else{
            $sql = "UPDATE re_info SET name=?, brand=?, type=?, desciption=?, id_category=?, id_area=?, id_site=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $brand, $type, $desciption, $id_category, $id_area, $id_site, $display_order, $id));
            return $id;
        }
    }
    
    public function exists($id){
        $sql = "SELECT id FROM re_info WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }
    
    public function delete($id){
        $sql = "DELETE FROM re_info WHERE id=?";
        $this->runRequest($sql, array($id));
    }
}
