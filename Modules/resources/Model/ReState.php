<?php

require_once 'Framework/Model.php';
require_once 'Framework/Constants.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReState extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_state";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("color", "varchar(7)", Constants::COLOR_WHITE);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_state WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM re_state WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM re_state WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function set($id, $name, $color, $idSpace)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE re_state SET name=?, color=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $color, $id, $idSpace));
        } else {
            $sql = "INSERT INTO re_state (name, color, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $color, $idSpace));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT id from re_state WHERE id=? AND id_space=? AND deleted=0";
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
        $sql = "UPDATE re_state SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
