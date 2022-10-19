<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReEventData extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_event_data";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_event", "int(11)", "");
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function addFile($idSpace, $id_event, $url)
    {
        $sql = "INSERT INTO re_event_data (id_event, url, id_space) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_event, $url, $idSpace));
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_event_data WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getByEvent($idSpace, $id_event)
    {
        $sql = "SELECT * FROM re_event_data WHERE id_event=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_event, $idSpace))->fetchAll();
    }

    public function set($idSpace, $id, $id_event, $url)
    {
        if ($this->exists($idSpace, $id)) {
            $sql = "UPDATE re_event_data SET id_event=?, url=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_event, $url, $id, $idSpace));
        } else {
            $sql = "INSERT INTO re_event_data (id_event, url, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_event, $url, $idSpace));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id)
    {
        $sql = "SELECT id from re_event_data WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id)
    {
        $sql = "DELETE FROM re_event_data WHERE id = ?";
        $this->runRequest($sql, array($id));
    }
}
