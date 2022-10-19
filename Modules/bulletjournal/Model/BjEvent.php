<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjEvent extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "bj_events";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_note", "int(11)", 0);
        $this->setColumnsInfo("start_time", "int(11)", 0);
        $this->setColumnsInfo("end_time", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM bj_events WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getForNote($idSpace, $id_note)
    {
        $sql = "SELECT bj_events.*, bj_notes.* FROM bj_events "
                . "INNER JOIN bj_notes ON bj_events.id_note=bj_notes.id "
                . "WHERE bj_events.id_note=? AND bj_events.id_space=? AND bj_events.deleted=0";
        return $this->runRequest($sql, array($id_note, $idSpace))->fetch();
    }

    public function set($idSpace, $id_note, $start_time, $end_time)
    {
        if ($this->exists($idSpace, $id_note)) {
            $sql = "UPDATE bj_events SET start_time=?, end_time=? WHERE id_note=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($start_time, $end_time, $id_note, $idSpace));
        } else {
            $sql = "INSERT INTO bj_events (id_note, start_time, end_time, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_note, $start_time, $end_time, $idSpace));
            $id_note = $this->getDatabase()->lastInsertId();
        }
        return $id_note;
    }

    public function exists($idSpace, $id_note)
    {
        $sql = "SELECT * from bj_events WHERE id_note=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_note, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($idSpace, $id_note)
    {
        $sql = "DELETE FROM bj_events WHERE id_note =? AND id_space=?";
        $this->runRequest($sql, array($id_note, $idSpace));
    }
}
