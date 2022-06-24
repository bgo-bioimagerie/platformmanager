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


        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_note", "int(11)", 0);
        $this->setColumnsInfo("status", "int(5)", 1);
        $this->setColumnsInfo("date", "int(11)", 0);
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `bj_tasks_history` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_note` int NOT NULL DEFAULT 0,
            `status` int NOT NULL DEFAULT 1,
            `date` int NOT NULL DEFAULT 0,
            `id_space` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
          )';
        $this->runRequest($sql);
        $this->baseSchema();
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM bj_tasks_history WHERE id=? AND id_space=?";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getLastStatus($id_space, $id_note){
        $sql = "SELECT * FROM bj_tasks_history WHERE id_note=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_note, $id_space));
        if($req->rowCount() > 0){
            return $req->fetch();
        }
        return array();
    }

    public function getForNote($id_space, $id_note) {
        $sql = "SELECT * FROM bj_tasks_history WHERE id_note=? AND id_space=? ORDER BY date DESC;";
        return $this->runRequest($sql, array($id_note, $id_space))->fetchAll();
    }

    public function addHist($id_space, $id_note, $status, $date){
        $sql = "INSERT INTO bj_tasks_history (id_note, status, date, id_space) VALUES (?,?,?,?)";
        $this->runRequest($sql, array($id_note, $status, $date, $id_space));
    }
    
    public function set($id_space, $id_note, $status, $date) {
        if ($this->exists($id_space, $id_note)) {
            $sql = "UPDATE bj_tasks_history SET status=?, date=? WHERE id_note=? AND id_space=?";
            $this->runRequest($sql, array($status, $date, $id_note));
        } else {
            $sql = "INSERT INTO bj_tasks_history (id_note, status, date, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_note, $status, $date, $id_space));
            $id_note = $this->getDatabase()->lastInsertId();
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
