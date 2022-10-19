<?php

require_once 'Framework/Model.php';

/**
 * @deprecated unused
 *
 * @author Sylvain Prigent
 */
class BjCollection extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "bj_collections";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM bj_collections WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getBySpace($idSpace)
    {
        $sql = "SELECT * FROM bj_collections WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function set($id, $idSpace, $name)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE bj_collections SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $idSpace));
        } else {
            $sql = "INSERT INTO bj_collections (id_space, name) VALUES (?,?)";
            $this->runRequest($sql, array($idSpace, $name));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT * from bj_collections WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE bj_collections SET deleted=1,deleted_at=NOW() WHERE id=? ANd id_space=?";
        //$sql = "DELETE FROM bj_collections WHERE id = ?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
