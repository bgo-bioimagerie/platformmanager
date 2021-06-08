<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjEvent extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bj_events";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_note", "int(11)", 0);
        $this->setColumnsInfo("start_time", "int(11)", 0);
        $this->setColumnsInfo("end_time", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM bj_events WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getForNote($id_note) {
        $sql = "SELECT bj_events.*, bj_notes.* FROM bj_events "
                . "INNER JOIN bj_notes ON bj_events.id_note=bj_notes.id "
                . "WHERE bj_events.id_note=?";
        return $this->runRequest($sql, array($id_note))->fetch();
    }

    public function set($id_note, $start_time, $end_time) {
        if ($this->exists($id_note)) {
            $sql = "UPDATE bj_events SET start_time=?, end_time=? WHERE id_note=?";
            $this->runRequest($sql, array($start_time, $end_time, $id_note));
            return $id_note;
        } else {
            $sql = "INSERT INTO bj_events (id_note, start_time, end_time) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_note, $start_time, $end_time));
            return $this->getDatabase()->lastInsertId();
        }
        return $id_note;
    }

    public function exists($id_note) {
        $sql = "SELECT * from bj_events WHERE id_note=?";
        $req = $this->runRequest($sql, array($id_note));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_note) {
        $sql = "DELETE FROM bj_events WHERE id_note = ?";
        $this->runRequest($sql, array($id_note));
    }

}
