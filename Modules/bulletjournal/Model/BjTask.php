<?php

require_once 'Framework/Model.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjTask extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bj_tasks";
        //$this->setColumnsInfo("id", "int(11)", "");
        //$this->setColumnsInfo("id_note", "int(11)", 0);
        //$this->setColumnsInfo("priority", "int(5)", 0);
        //$this->setColumnsInfo("deadline", "date", "");
        //$this->primaryKey = "id";
    }
    
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bj_tasks` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `id_note` int NOT NULL DEFAULT '0',
            `priority` int NOT NULL DEFAULT '0',
            `deadline` date DEFAULT NULL,
            PRIMARY KEY (`id`)
        );";
    
        $this->runRequest($sql);
    }

    /*
    public function migrate($id){
        $modelHist = new BjTaskHistory();
        
        // get date of last status 
        $lastHist = $modelHist->getLastStatus($id);
        if (count($lastHist) > 0){
            $date = date("Y-m-d", $lastHist["date"]);
        }
        else{
            $sql = "SELECT date FROM bj_notes WHERE id=?";
            $tmp = $this->runRequest($sql, array($id))->fetch();
            $date = $tmp[0];
        }
        //echo "date = " . $date . "</br>";
        $dateArray = explode("-", $date);
        $year = $dateArray[0];
        $month = $dateArray[1];
        if($month == 12){
            $month = 1;
            $year = $year + 1;
        }
        else{
            $month = $month + 1;
        }
        
        $migratetime = mktime(0, 0, 0, $month, 1, $year);
        // migrate to next month
        $modelHist->addHist($id, BjTaskStatus::$migrated, $migratetime);
    }
    */
    
    public function openedForMigration($id_space, $year, $month){
        $firstDay = $year . "-" . $month . "-01";
        $lastDay = date("Y-m-t", strtotime($firstDay));
        
        $sql = "SELECT * FROM bj_notes "
                . "WHERE bj_notes.id_space=? AND bj_notes.date>=? AND bj_notes.date<=? AND type=2";
        $tasks = $this->runRequest($sql, array($id_space, $firstDay, $lastDay))->fetchAll();
        $openedTasks = array();
        foreach($tasks as $task){
            $sql = "SELECT * FROM bj_tasks_history WHERE id_note=? ORDER BY date DESC;";
            $req = $this->runRequest($sql, array($task["id"]));
            if($req->rowCount() == 0){
                $sql = "SELECT priority FROM bj_tasks WHERE id_note=?";
                $priority = $this->runRequest($sql, array($task["id"]))->fetch();
                $task["priority"] = $priority[0];
                
                $task["status"] = 1;
                $openedTasks[] = $task;
            }
            else{
                $lastHist = $req->fetch();
                if($lastHist["status"] == 1){
                    $sql = "SELECT priority FROM bj_tasks WHERE id_note=?";
                    $priority = $this->runRequest($sql, array($task["id"]))->fetch();
                    $task["priority"] = $priority[0];
                
                    $task["status"] = 1;
                    $openedTasks[] = $task;
                }
            }
        }
        return $openedTasks;
    }

    public function getForNote($id_space, $id_note) {
        $sql = "SELECT bj_tasks.*, bj_notes.* FROM bj_tasks "
                . "INNER JOIN bj_notes ON bj_tasks.id_note=bj_notes.id "
                . "WHERE bj_tasks.id_note=? AND bj_tasks.id_space=?";
        return $this->runRequest($sql, array($id_note, $id_space))->fetch();
    }

    public function set($id_space, $id_note, $priority, $deadline) {
        if ($this->exists($id_space, $id_note)) {
            $sql = "UPDATE bj_tasks SET priority=?, deadline=? WHERE id_note=? AND id_space=?";
            $this->runRequest($sql, array($priority, $deadline, $id_note, $id_space));
            return $id_note;
        } else {
            $sql = "INSERT INTO bj_tasks (id_note, priority, deadline, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_note, $priority, $deadline, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
        return $id_note;
    }

    public function exists($id_space, $id_note) {
        $sql = "SELECT * from bj_tasks WHERE id_note=? AND id_space=?";
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
        $sql = "DELETE FROM bj_tasks WHERE id_note = ? AND id_space=?";
        $this->runRequest($sql, array($id_note, $id_space));
    }

}
