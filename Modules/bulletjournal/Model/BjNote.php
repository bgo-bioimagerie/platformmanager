<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjNote extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "bj_notes";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("type", "int(11)", 0);
        $this->setColumnsInfo("content", "text", "");
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("is_month_task", "int(1)", "0");
        $this->primaryKey = "id";
    }

    public function get($id_space, $id)
    {
        $sql = "SELECT * FROM bj_notes WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getforCollection($id_space, $id_collection)
    {
        $sql = "SELECT * FROM bj_notes WHERE id IN (SELECT id_note FROM bj_j_collections_notes WHERE id_collection=? AND id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_collection, $id_space));
        $notes = $req->fetchAll();

        return $this->getNoteInfos($notes);
    }

    public function getAllForMonth($id_space, $month, $year)
    {
        $firstDay = $year . "-" . $month . "-01";
        $lastDay = date("Y-m-t", strtotime($firstDay));

        $sql = "SELECT * FROM bj_notes WHERE id_space=? AND date>=? AND date<=?";
        $req = $this->runRequest($sql, array($id_space, $firstDay, $lastDay));
        $notes = $req->fetchAll();

        // select migrated notes
        $firstDaytime = mktime(0, 0, 0, $month, 1, $year);
        $sql2 = "SELECT * FROM bj_tasks_history WHERE status=4 AND date=? AND id_space=?";
        $migratedHist = $this->runRequest($sql2, array($firstDaytime, $id_space))->fetchAll();
        foreach ($migratedHist as $hist) {
            $sql = "SELECT * FROM bj_notes WHERE id=?";
            $tmpNote = $this->runRequest($sql, array($hist["id_note"]))->fetch();
            $tmpNote["migrated"] = 1;
            $tmpNote["is_month_task"] = 1;
            $notes[] = $tmpNote;
        }

        // get notes info
        return $this->getNoteInfos($notes);
    }

    public function getNoteInfos($notes)
    {
        for ($i = 0 ; $i < count($notes) ; $i++) {
            if ($notes[$i]["type"] == 2) {
                $sql = "SELECT * FROM bj_tasks WHERE id_note=?";
                $taskInfo = $this->runRequest($sql, array($notes[$i]["id"]))->fetch();
                $notes[$i]["priority"] = 0;
                if ($taskInfo) {
                    $notes[$i]["priority"] = $taskInfo["priority"];
                }

                $sqlh = "SELECT * FROM bj_tasks_history WHERE id_note=? ORDER BY date DESC;";
                $hist = $this->runRequest($sqlh, array($notes[$i]["id"]))->fetch();
                $notes[$i]["status"] = 1;
                if ($hist && !empty($hist)) {
                    $notes[$i]["status"] = $hist["status"];
                }
                if (!isset($notes[$i]["migrated"])) {
                    $notes[$i]["migrated"] = 0;
                }
            }
        }
        return $notes;
    }


    public function getForMonth($id_space, $month, $year, $is_month_task)
    {
        $firstDay = $year . "-" . $month . "-01";
        $lastDay = date("Y-m-t", strtotime($firstDay));

        $sql = "SELECT * FROM bj_notes WHERE id_space=? AND date>=? AND date<=? AND is_month_task=?";
        $req = $this->runRequest($sql, array($id_space, $firstDay, $lastDay, $is_month_task));
        $notes = $req->fetchAll();
        for ($i = 0 ; $i < count($notes) ; $i++) {
            if ($notes[$i]["type"] == 2) {
                $sql = "SELECT * FROM bj_tasks WHERE id_note=?";
                $taskInfo = $this->runRequest($sql, array($notes[$i]["id"]))->fetch();
                $notes[$i]["priority"] = $taskInfo["priority"];

                $sqlh = "SELECT * FROM bj_tasks_history WHERE id_note=? ORDER BY date ASC;";
                $hist = $this->runRequest($sqlh, array($notes[$i]["id"]))->fetch();
                $notes[$i]["status"] = 1;
                if (count($hist) > 0) {
                    $notes[$i]["status"] = $hist["status"];
                }
            }
        }
        return $notes;
    }

    public function getForSpace($id_space)
    {
        $sql = "SELECT * FROM bj_notes WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getName($id_space, $id)
    {
        $sql = "SELECT name FROM bj_notes WHERE id=? AND id_space=?";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function set($id, $id_space, $name, $type, $content, $date, $is_month_task)
    {
        if ($date == "") {
            $date = null;
        }
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE bj_notes SET name=?, type=?, content=?, date=?, is_month_task=? WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($name, $type, $content, $date, $is_month_task, $id, $id_space));
        } else {
            $sql = "INSERT INTO bj_notes (id_space, name, type, content, date, is_month_task) VALUES (?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $name, $type, $content, $date, $is_month_task));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id_space, $id)
    {
        $sql = "SELECT id from bj_notes WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a node
     * @param number $id ID
     */
    public function delete($id_space, $id)
    {
        $sql = "DELETE FROM bj_notes WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }
}
