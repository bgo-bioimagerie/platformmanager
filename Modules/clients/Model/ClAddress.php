<?php

require_once 'Framework/Model.php';

class ClAddress extends Model
{
    public function __construct()
    {
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

    public function get($id_space, $id)
    {
        if (!$id) {
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

        $sql = "SELECT * FROM cl_addresses WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function set($id_space, $id, $institution, $building_floor, $service, $address, $zip_code, $city, $country)
    {
        if (!$id) {
            $sql = 'INSERT INTO cl_addresses (institution, building_floor, service, address, zip_code, city, country, id_space) VALUES (?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($institution, $building_floor, $service, $address, $zip_code, $city, $country, $id_space));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_addresses SET institution=?, building_floor=?, service=?, address=?, zip_code=?, city=?, country=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($institution, $building_floor, $service, $address, $zip_code, $city, $country, $id, $id_space));
            return $id;
        }
    }

    public function delete($id_space, $id)
    {
        $sql = "UPDATE cl_addresses SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }
}
