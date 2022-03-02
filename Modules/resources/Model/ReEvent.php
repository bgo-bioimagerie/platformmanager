<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReEvent extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "re_event";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("id_eventtype", "int(11)", 0);
        $this->setColumnsInfo("id_state", "int(11)", 0);
        $this->setColumnsInfo("comment", "text", "");
        $this->primaryKey = "id";
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM re_event WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getLastStateColor($id_space, $id_resource){
        $sql = "SELECT re_state.color as color"
                . " FROM re_event "
                . " INNER JOIN re_state ON re_event.id_state = re_state.id"
                . " WHERE id_resource=? AND re_event.id_space=? AND re_event.deleted=0 ORDER BY date DESC;";
        $data = $this->runRequest($sql, array($id_resource, $id_space))->fetch();
        return  $data ? $data[0] : null;   
    }

    public function getLastStateColors($id_space, array $id_resources){
        $sql = "SELECT id_resource, re_state.color as color"
                . " FROM re_event "
                . " INNER JOIN re_state ON re_event.id_state = re_state.id"
                . " WHERE id_resource in (".implode(',', $id_resources).") AND re_event.id_space=? AND re_event.deleted=0 ORDER BY date DESC;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
        //return  $data;   
    }
    
    public function getAll($id_space, $sort = "date") {
        $sql = "SELECT * FROM re_event WHERE id_space=? AND deleted=0 ORDER BY " . $sort . " ASC";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getByResource($id_space, $id){
        $sql = "SELECT * FROM re_event WHERE id_resource=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetchAll();
        
    }
    
    public function addDefault($id_space, $id_resource, $id_user){
        $sql = "INSERT INTO re_event (date, id_resource, id_user, id_eventtype, id_state, comment, id_space) VALUES (?,?,?,?,?,?,?)";
        $this->runRequest($sql, array(date("Y-m-d", time()), $id_resource, $id_user, 1, 1, "", $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    public function set($id_space, $id, $id_resource, $date, $id_user, $id_eventtype, $id_state, $comment) {
        if($date == "") {
            $date = null;
        }
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE re_event SET date=?, id_resource=?, id_user=?, id_eventtype=?, id_state=?, comment=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($date, $id_resource, $id_user, $id_eventtype, $id_state, $comment, $id, $id_space));
        } else {
            $sql = "INSERT INTO re_event (date, id_resource, id_user, id_eventtype, id_state, comment, id_space) VALUES (?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($date, $id_resource, $id_user, $id_eventtype, $id_state, $comment, $id_space));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id_space, $id) {
        $sql = "SELECT id from re_event WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE re_event SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
