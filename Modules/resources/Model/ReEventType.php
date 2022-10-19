<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReEventType extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_event_type";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_event_type WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM re_event_type WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM re_event_type WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function set($id, $name, $idSpace)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE re_event_type SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $idSpace));
        } else {
            $sql = "INSERT INTO re_event_type (name, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($name, $idSpace));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT id from re_event_type WHERE id=? AND id_space=? AND deleted=0";
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
        $sql = "UPDATE re_event SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
