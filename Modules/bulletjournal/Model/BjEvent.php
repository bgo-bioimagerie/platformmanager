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

        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_note", "int(11)", 0);
        $this->setColumnsInfo("start_time", "int(11)", 0);
        $this->setColumnsInfo("end_time", "int(11)", 0);
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `bj_events` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_note` int NOT NULL DEFAULT 0,
            `start_time` int NOT NULL DEFAULT 0,
            `end_time` int NOT NULL DEFAULT 0,
            `id_space` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
          )';
        $this->runRequest($sql);
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM bj_events WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getForNote($id_space, $id_note) {
        $sql = "SELECT bj_events.*, bj_notes.* FROM bj_events "
                . "INNER JOIN bj_notes ON bj_events.id_note=bj_notes.id "
                . "WHERE bj_events.id_note=? AND bj_events.id_space=? AND bj_events.deleted=0";
        return $this->runRequest($sql, array($id_note, $id_space))->fetch();
    }

    public function set($id_space, $id_note, $start_time, $end_time) {
        if ($this->exists($id_space, $id_note)) {
            $sql = "UPDATE bj_events SET start_time=?, end_time=? WHERE id_note=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($start_time, $end_time, $id_note, $id_space));
        } else {
            $sql = "INSERT INTO bj_events (id_note, start_time, end_time, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_note, $start_time, $end_time, $id_space));
            $id_note = $this->getDatabase()->lastInsertId();
        }
        return $id_note;
    }

    public function exists($id_space, $id_note) {
        $sql = "SELECT * from bj_events WHERE id_note=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_note, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_space, $id_note) {
        $sql = "DELETE FROM bj_events WHERE id_note =? AND id_space=?";
        $this->runRequest($sql, array($id_note, $id_space));
    }

}
