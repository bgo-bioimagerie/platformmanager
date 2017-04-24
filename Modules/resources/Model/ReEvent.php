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
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("id_eventtype", "int(11)", 0);
        $this->setColumnsInfo("id_state", "int(11)", 0);
        $this->setColumnsInfo("comment", "text", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_event WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getLastStateColor($id_resource){
        $sql = "SELECT re_state.color as color"
                . " FROM re_event "
                . " INNER JOIN re_state ON re_event.id_state = re_state.id"
                . " WHERE id_resource=? ORDER BY date DESC;";
        $data = $this->runRequest($sql, array($id_resource))->fetch();
        return $data[0];
        
    }
    
    public function getAll($sort = "date") {
        $sql = "SELECT * FROM re_event ORDER BY " . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function getByResource($id){
        $sql = "SELECT * FROM re_event WHERE id_resource=?";
        return $this->runRequest($sql, array($id))->fetchAll();
        
    }
    
    public function addDefault($id_resource, $id_user){
        $sql = "INSERT INTO re_event (date, id_resource, id_user, id_eventtype, id_state, comment) VALUES (?,?,?,?,?,?)";
        $this->runRequest($sql, array(date("Y-m-d", time()), $id_resource, $id_user, 1, 1, ""));
        return $this->getDatabase()->lastInsertId();
    }

    public function set($id, $id_resource, $date, $id_user, $id_eventtype, $id_state, $comment) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_event SET date=?, id_resource=?, id_user=?, id_eventtype=?, id_state=?, comment=? WHERE id=?";
            $id = $this->runRequest($sql, array($date, $id_resource, $id_user, $id_eventtype, $id_state, $comment, $id));
        } else {
            $sql = "INSERT INTO re_event (date, id_resource, id_user, id_eventtype, id_state, comment) VALUES (?,?,?,?,?,?)";
            $this->runRequest($sql, array($date, $id_resource, $id_user, $id_eventtype, $id_state, $comment));
        }
        return $id;
    }

    public function exists($id) {
        $sql = "SELECT id from re_event WHERE id=?";
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
    public function delete($id) {
        $sql = "DELETE FROM re_event WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
