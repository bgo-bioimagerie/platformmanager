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
        $this->setColumnsInfo("description", "varchar(500)", "");
        $this->setColumnsInfo("long_description", "text", "");
        $this->setColumnsInfo("id_category", "int(11)", 0);
        $this->setColumnsInfo("id_area", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->setColumnsInfo("image", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getDefault() {
        return array(
            "id" => 0,
            "name" => "",
            "brand" => "",
            "type" => "",
            "description" => "",
            "long_description" => "",
            "id_category" => 0,
            "id_area" => 0,
            "id_space" => 0,
            "display_order" => 0,
            "image" => ""
        );
    }
    
    public function setImage($id, $image){
        $sql = "UPDATE re_info SET image=? WHERE id=?";
        $this->runRequest($sql, array($image, $id));
    }

    public function getAll($sort = "name") {
        $sql = "SELECT * FROM re_info ORDER BY " . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function getForSpace($id_space){
        $sql = "SELECT * FROM re_info WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getForList($id_space){
        $data = $this->getForSpace($id_space);
        $names = array(); $ids = array();
        foreach($data as $d){
           $names[] =  $d["name"];
           $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    public function getAllForSelect($id_space, $sort = "name"){
        $sql = "SELECT * FROM re_info WHERE id_space=? ORDER BY " . $sort . " ASC";
        $resources = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($resources as $res){
            $names[] = $res["name"];
            $ids[] = $res["id"];
        }
        return array("names" => $names, "ids" => $ids);
        
    }

    public function get($id) {
        $sql = "SELECT * FROM re_info WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ( $req->rowCount() > 0 ){
            return $req->fetch();
        }
        return null;
    }
    
    public function getBySpace($id) {
        $sql = "SELECT re_info.*, re_category.name as category "
                . "FROM re_info "
                . "INNER JOIN re_category ON re_info.id_category = re_category.id "
                . "WHERE re_info.id_space=?";
        return $this->runRequest($sql, array($id))->fetchAll();
    }
    
    public function getIdByName($name){
        $sql = "SELECT id FROM re_info WHERE name=?";
        $tmp = $this->runRequest($sql, array($name))->fetch();
        return $tmp[0];
    }
    
    public function getIdByNameSpace($name, $id_space){
        $sql = "SELECT id FROM re_info WHERE name=? AND id_space=?";
        $tmp = $this->runRequest($sql, array($name, $id_space))->fetch();
        return $tmp[0];
    }

    public function getAreaID($id){
        $sql = "SELECT id_area FROM re_info WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }
    
    public function getName($id) {
        $sql = "SELECT name FROM re_info WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function set($id, $name, $brand, $type, $description, $long_description, $id_category, $id_area, $id_space, $display_order) {

        if (!$this->exists($id)) {
            $sql = "INSERT INTO re_info (name, brand, type, description, long_description, id_category, id_area, id_space, display_order) "
                    . "VALUES (?,?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($name, $brand, $type, $description, $long_description, $id_category, $id_area, $id_space, $display_order));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE re_info SET name=?, brand=?, type=?, description=?, long_description=?, id_category=?, id_area=?, id_space=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $brand, $type, $description, $long_description, $id_category, $id_area, $id_space, $display_order, $id));
            return $id;
        }
    }

    public function exists($id) {
        $sql = "SELECT id FROM re_info WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get the first resource ID for a given area
     * @param unknown $areaId
     * @return mixed
     */
    public function firstResourceIDForArea($areaId) {
        $sql = "select id from re_info where id_area=? ORDER BY display_order ASC;";
        $req = $this->runRequest($sql, array($areaId));
        $tmp = $req->fetch();
        return $tmp[0];
    }

    /**
     * Get the resources IDs and names for a given Area
     * @param unknown $areaId
     * @return multitype:
     */
    public function resourceIDNameForArea($areaId) {
        $sql = "select id, name from re_info where id_area=? ORDER BY display_order";
        $data = $this->runRequest($sql, array($areaId));
        return $data->fetchAll();
    }

    /**
     * Get the resources info for a given area
     * @param unknown $areaId
     * @return multitype:
     */
    public function resourcesForArea($areaId) {
        $sql = "select * from re_info where id_area=? ORDER BY display_order";
        $data = $this->runRequest($sql, array($areaId));
        return $data->fetchAll();
    }

    public function delete($id) {
        $sql = "DELETE FROM re_info WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
