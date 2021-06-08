<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReEventData extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "re_event_data";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_event", "int(11)", "");
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function addFile($id_event, $url){
        $sql = "INSERT INTO re_event_data (id_event, url) VALUES (?,?)";
        $this->runRequest($sql, array($id_event, $url));
    }
    
    public function get($id) {
        $sql = "SELECT * FROM re_event_data WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getByEvent($id_event) {
        $sql = "SELECT * FROM re_event_data WHERE id_event=?";
        return $this->runRequest($sql, array($id_event))->fetchAll();
    }

    public function set($id, $id_event, $url) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_event_data SET id_event=?, url=? WHERE id=?";
            $id = $this->runRequest($sql, array($id_event, $url, $id));
        } else {
            $sql = "INSERT INTO re_event_data (id_event, url) VALUES (?,?)";
            $this->runRequest($sql, array($id_event, $url));
        }
        return $id;
    }

    public function exists($id) {
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
    public function delete($id) {
        $sql = "DELETE FROM re_event_data WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
