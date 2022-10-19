<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Site model
 *
 * @author Sylvain Prigent
 */
class ReCategory extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_category";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_category WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getBySpace($idSpace)
    {
        $sql = "SELECT * FROM re_category WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getIdFromNameSpace($name, $idSpace)
    {
        $sql = "SELECT id FROM re_category WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $name, $idSpace)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE re_category SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $idSpace));
            return $id;
        } else {
            $sql = "INSERT INTO re_category (name, id_space) VALUES (?, ?)";
            $this->runRequest($sql, array($name, $idSpace));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT id from re_category WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getForList($idSpace)
    {
        $sql = "SELECT * FROM re_category WHERE id_space=? AND deleted=0 ORDER BY name ASC;";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get the name of a resources category
     *
     * @param int $id Id of the resources category to query
     * @throws Exception if the resources category is not found
     * @return mixed array
     */
    public function getName($idSpace, $id)
    {
        $sql = "select name from re_category where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        }
        return "";
    }

    public function getIdFromName($idSpace, $name)
    {
        $sql = "SELECT id from re_category where name=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $idSpace));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        }
        return "";
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE re_category SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
