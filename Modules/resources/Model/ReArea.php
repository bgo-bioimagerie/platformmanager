<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReArea extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "re_area";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_area WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAll($sort = "name") {
        $sql = "SELECT * FROM re_area ORDER BY " . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }

    public function set($id, $name) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_area SET name=? WHERE id=?";
            $id = $this->runRequest($sql, array($name, $id));
        } else {
            $sql = "INSERT INTO re_area (name) VALUES (?)";
            $this->runRequest($sql, array($name));
        }
        return $id;
    }

    public function exists($id) {
        $sql = "SELECT id from re_area WHERE id=?";
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
        $sql = "DELETE FROM re_area WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
