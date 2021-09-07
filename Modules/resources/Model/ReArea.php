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

    public function get($id_space, $id) {
        $sql = "SELECT * FROM re_area WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getSpace($id){
        $sql = "SELECT id_space FROM re_area WHERE id=? AND deleted=0";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d? $d[0]:0;
    }
    
    public function getDefaultArea($id_space){
        $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=0 AND deleted=0";
        $req = $this->runRequest($sql, array($id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        else{
            $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=1 AND deleted=0";
            $req = $this->runRequest($sql, array($id_space));
            if ($req->rowCount() > 0){
                $tmp = $req->fetch();
                return $tmp[0];
            }
            return 0;
        }
        
    }

    public function getIdFromNameSpace($name, $id_space) {
        $sql = "SELECT id FROM re_area WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM re_area WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getName($id_space, $id) {
        $sql = "SELECT name FROM re_area WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $tmp?$tmp[0]:0;
    }

    public function getIdFromName($name) {
        $sql = "SELECT id FROM re_area WHERE name=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($name))->fetch();
        return $tmp?$tmp[0]:0;
    }

    public function set($id, $name, $restricted, $id_space) {
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE re_area SET name=?, restricted=?WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $restricted, $id, $id_space));
            return $id;
        } else {
            $sql = "INSERT INTO re_area (name, restricted, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $restricted, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id_space, $id) {
        $sql = "SELECT id from re_area WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getSiteID($id_area) {
        $sql = "SELECT id_space from re_area WHERE id=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_area));
        $tmp = $req->fetch();
        return $tmp? $tmp[0]:0;
    }

    /**
     * Get the smallest unrestricted area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestUnrestrictedID($id_space) {
        $sql = "SELECT id FROM re_area WHERE restricted=0 AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_space));
        $tmp = $req->fetch();
        return $tmp? $tmp[0]:0;
    }

    /**
     * Get the smallest area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestID($id_space) {
        $sql = "SELECT id FROM re_area WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_space));
        $tmp = $req->fetch();
        return $tmp? $tmp[0]:0;
    }

    /**
     * 
     * Get ID and Name of areas of all areas
     * @return multitype: Areas info
     */
    public function getAreasIDName($id_space) {
        $sql = "SELECT id, name FROM re_area WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function getUnrestrictedAreasIDNameForSite($id_space) {
        $sql = "SELECT id, name from re_area where id_space=? AND restricted=0 AND deleted=0";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function getAreasIDNameForSite($id_space) {
        $sql = "SELECT id, name from re_area where id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE re_area SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM re_area WHERE id = ? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id, $id_space));
    }

}
