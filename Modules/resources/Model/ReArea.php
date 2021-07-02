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
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("restricted", "int(1)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_area WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getSpace($id){
        $sql = "SELECT id_space FROM re_area WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }
    
    public function getDefaultArea($id_space){
        $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=0";
        $req = $this->runRequest($sql, array($id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        else{
            $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=1";
            $req = $this->runRequest($sql, array($id_space));
            if ($req->rowCount() > 0){
                $tmp = $req->fetch();
                return $tmp[0];
            }
            return 0;
        }
        
    }

    public function getIdFromNameSpace($name, $id_space) {
        $sql = "SELECT id FROM re_area WHERE name=? AND id_space=?";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM re_area WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getName($id) {
        $sql = "SELECT name FROM re_area WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function getIdFromName($name) {
        $sql = "SELECT id FROM re_area WHERE name=?";
        $tmp = $this->runRequest($sql, array($name))->fetch();
        return $tmp[0];
    }

    public function set($id, $name, $restricted, $id_space) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_area SET name=?, restricted=?, id_space=? WHERE id=?";
            $this->runRequest($sql, array($name, $restricted, $id_space, $id));
            return $id;
        } else {
            $sql = "INSERT INTO re_area (name, restricted, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $restricted, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
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
        $sql = "SELECT id_space from re_area WHERE id=?";
        $req = $this->runRequest($sql, array($id_area));
        $tmp = $req->fetch();
        return $tmp[0];
    }

    /**
     * Get the smallest unrestricted area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestUnrestrictedID() {
        $sql = "select id from re_area where restricted=0";
        $req = $this->runRequest($sql);
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

    public function getUnrestrictedAreasIDNameForSite($id_space) {
        $sql = "select id, name from re_area where id_space=? AND restricted=0";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function getAreasIDNameForSite($id_space) {
        $sql = "select id, name from re_area where id_space=?";
        $data = $this->runRequest($sql, array($id_space));
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
