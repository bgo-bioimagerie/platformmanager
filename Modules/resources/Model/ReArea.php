<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReArea extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_area";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("restricted", "int(1)", 0);
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_area WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getSpace($id)
    {
        $sql = "SELECT id_space FROM re_area WHERE id=? AND deleted=0";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d ? $d[0] : 0;
    }

    public function getDefaultArea($idSpace)
    {
        $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=0 AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            $sql = "SELECT id FROM re_area WHERE id_space=? AND restricted=1 AND deleted=0";
            $req = $this->runRequest($sql, array($idSpace));
            if ($req->rowCount() > 0) {
                $tmp = $req->fetch();
                return $tmp[0];
            }
            return 0;
        }
    }

    public function getIdFromNameSpace($name, $idSpace)
    {
        $sql = "SELECT id FROM re_area WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM re_area WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM re_area WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : 0;
    }

    public function getIdFromName($name)
    {
        $sql = "SELECT id FROM re_area WHERE name=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($name))->fetch();
        return $tmp ? $tmp[0] : 0;
    }

    public function set($id, $name, $restricted, $idSpace)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE re_area SET name=?, restricted=?WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $restricted, $id, $idSpace));
            return $id;
        } else {
            $sql = "INSERT INTO re_area (name, restricted, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $restricted, $idSpace));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT id from re_area WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getSiteID($id_area)
    {
        $sql = "SELECT id_space from re_area WHERE id=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_area));
        $tmp = $req->fetch();
        return $tmp ? $tmp[0] : 0;
    }

    /**
     * Get the smallest unrestricted area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestUnrestrictedID($idSpace)
    {
        $sql = "SELECT id FROM re_area WHERE restricted=0 AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        $tmp = $req->fetch();
        return $tmp ? $tmp[0] : 0;
    }

    /**
     * Get the smallest area ID in the table
     * @return Number Smallest ID
     */
    public function getSmallestID($idSpace)
    {
        $sql = "SELECT id FROM re_area WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        $tmp = $req->fetch();
        return $tmp ? $tmp[0] : 0;
    }

    /**
     *
     * Get ID and Name of areas of all areas
     * @return multitype: Areas info
     */
    public function getAreasIDName($idSpace)
    {
        $sql = "SELECT id, name FROM re_area WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    public function getUnrestrictedAreasIDNameForSite($idSpace)
    {
        $sql = "SELECT id, name from re_area where id_space=? AND restricted=0 AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    public function getAreasIDNameForSite($idSpace)
    {
        $sql = "SELECT id, name from re_area where id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE re_area SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
