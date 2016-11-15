<?php

require_once 'Framework/Model.php';

class BjTaskStatus{
    
    static public $open=1;
    static public $done=2;
    static public $canceled=3;
    static public $migrated=4;
}

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjTaskHistory extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bj_tasks_history";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_note", "int(11)", 0);
        $this->setColumnsInfo("status", "int(5)", 1);
        $this->setColumnsInfo("date", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM bj_tasks_history WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getLastStatus($id_note){
        $sql = "SELECT * FROM bj_tasks_history WHERE id_note=?";
        $req = $this->runRequest($sql, array($id_note));
        if($req->rowCount() > 0){
            return $req->fetch();
        }
        return array();
    }

    public function getForNote($id_note) {
        $sql = "SELECT * FROM bj_tasks_history WHERE id_note=? ORDER BY date DESC;";
        return $this->runRequest($sql, array($id_note))->fetchAll();
    }

    public function addHist($id_note, $status, $date){
        $sql = "INSERT INTO bj_tasks_history (id_note, status, date) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_note, $status, $date));
    }
    
    public function set($id_note, $status, $date) {
        if ($this->exists($id_note)) {
            $sql = "UPDATE bj_tasks_history SET status=?, date=? WHERE id_note=?";
            $this->runRequest($sql, array($status, $date, $id_note));
            return $id_note;
        } else {
            $sql = "INSERT INTO bj_tasks_history (id_note, status, date) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_note, $status, $date));
            return $this->getDatabase()->lastInsertId();
        }
        return $id_note;
    }

    public function exists($id_note) {
        $sql = "SELECT * from bj_tasks_history WHERE id_note=?";
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
        $sql = "DELETE FROM bj_tasks_history WHERE id_note = ?";
        $this->runRequest($sql, array($id_note));
    }

}
