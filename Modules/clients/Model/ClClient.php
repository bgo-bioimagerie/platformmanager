<?php

require_once 'Framework/Model.php';
require_once 'Modules/clients/Model/ClPricing.php';

class ClClient extends Model {

    public function __construct() {
        $this->tableName = "cl_clients";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("contact_name", "varchar(255)", "");
        $this->setColumnsInfo("institution", "varchar(255)", "");
        $this->setColumnsInfo("building_floor", "varchar(255)", "");
        $this->setColumnsInfo("service", "varchar(255)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("zip_code", "varchar(20)", "");
        $this->setColumnsInfo("city", "varchar(255)", "");
        $this->setColumnsInfo("country", "varchar(255)", "");
        $this->setColumnsInfo("phone", "varchar(20)", "");
        $this->setColumnsInfo("email", "varchar(255)", "");
        $this->setColumnsInfo("pricing", "int(11)", "");
        $this->primaryKey = "id";
    }
    
    public function getPricingID($id){
        $sql = "SELECT pricing FROM cl_clients WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM cl_clients WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $modelPricing = new ClPricing();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["pricing_name"] = $modelPricing->getName($data[$i]["pricing"]);
        }
        return $data;
    }
    
    public function getName($id){
        $sql = "SELECT name FROM cl_clients WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();
        return $data[0];
    }
    
    public function getIdFromName($name){
        $sql = "SELECT id FROM cl_clients WHERE name=?";
        $data = $this->runRequest($sql, array($name))->fetch();
        return $data[0];
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM cl_clients WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function get($id) {
        if ($id == 0) {
            return array(
                "id" => 0,
                "name" => "",
                "contact_name" => "",
                "institution" => "",
                "building_floor" => "",
                "service" => "",
                "address" => "",
                "zip_code" => "",
                "city" => "",
                "country" => "",
                "phone" => "",
                "email" => "",
                "pricing" => 0
            );
        }

        $sql = "SELECT * FROM cl_clients WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_space, $name, $contact_name, $institution, $building_floor, $service, $address, $zip_code, $city, $country, $phone, $email, $pricing) {
        if ($id == 0) {
            $sql = 'INSERT INTO cl_clients (id_space, name, contact_name, institution, building_floor, service, address, 
            zip_code, city, country, phone, email, pricing) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $contact_name, $institution, $building_floor, $service, $address,
                $zip_code, $city, $country, $phone, $email, $pricing));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_clients SET id_space=?, name=?, contact_name=?, institution=?, building_floor=?, service=?, address=?, 
            zip_code=?, city=?, country=?, phone=?, email=?, pricing=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $contact_name, $institution, $building_floor, $service, $address,
                $zip_code, $city, $country, $phone, $email, $pricing, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM cl_clients WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
