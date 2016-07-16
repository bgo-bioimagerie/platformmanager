<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReArea extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_area";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_site", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_area WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM re_area WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function getAll($sort = "name") {
        $sql = "SELECT re_area.*, ec_sites.name AS site "
                . " FROM re_area "
                . " INNER JOIN ec_sites ON ec_sites.id = re_area.id_site "
                . "ORDER BY re_area." . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }

    public function set($id, $name, $id_site) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_area SET name=?, id_site=? WHERE id=?";
            $id = $this->runRequest($sql, array($name, $id_site, $id));
        } else {
            $sql = "INSERT INTO re_area (name, id_site) VALUES (?,?)";
            $this->runRequest($sql, array($name, $id_site));
        }
        return $id;
    }

    public function exists($id) {
        $sql = "SELECT id from re_area WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getSiteID($id_area) {
        $sql = "SELECT id_site from re_area WHERE id=?";
        $req = $this->runRequest($sql, array($id_area));
        $tmp = $req->fetch();
        return $tmp[0];
    }

    /**
     * Get the smallest area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestID() {
        $sql = "select id from re_area";
        $req = $this->runRequest($sql);
        $tmp = $req->fetch();
        return $tmp[0];
    }

    /**
     * 
     * Get ID and Name of areas of all areas
     * @return multitype: Areas info
     */
    public function getAreasIDName() {
        $sql = "select id, name from re_area;";
        $data = $this->runRequest($sql);
        return $data->fetchAll();
    }

    public function getUnrestrictedAreasIDNameForSite($id_site){
        $sql = "select id, name from re_area where id_site=?";
        $data = $this->runRequest($sql, array($id_site));
        return $data->fetchAll();
    }
    
    public function getAreasIDNameForSite($id_site){
        $sql = "select id, name from re_area where id_site=?";
        $data = $this->runRequest($sql, array($id_site));
        return $data->fetchAll();
    }
    
    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id) {
        $sql = "DELETE FROM re_area WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
