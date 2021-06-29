<?php

require_once 'Framework/Model.php';

class ClAddress extends Model {

    public function __construct() {
        $this->tableName = "cl_addresses";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("institution", "varchar(255)", "");
        $this->setColumnsInfo("building_floor", "varchar(255)", "");
        $this->setColumnsInfo("service", "varchar(255)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("zip_code", "varchar(20)", "");
        $this->setColumnsInfo("city", "varchar(255)", "");
        $this->setColumnsInfo("country", "varchar(255)", "");
        $this->primaryKey = "id";
    }
    
    public function get($id){
        if (!$id){
            return array(
                "id" => 0,
                "institution" => "",
                "building_floor" => "",
                "service" => "",
                "address" => "",
                "zip_code" => "",
                "city" => "",
                "country" => "",
            );
        }
        
        $sql = "SELECT * FROM cl_addresses WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $institution, $building_floor, $service, $address, $zip_code, $city, $country) {
        if (!$id) {
            $sql = 'INSERT INTO cl_addresses (institution, building_floor, service, address, zip_code, city, country) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($institution, $building_floor, $service, $address, $zip_code, $city, $country));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_addresses SET institution=?, building_floor=?, service=?, address=?, zip_code=?, city=?, country=? WHERE id=?';
            $this->runRequest($sql, array($institution, $building_floor, $service, $address, $zip_code, $city, $country, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM cl_addresses WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
