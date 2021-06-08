<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class BjCollection extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bj_collections";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM bj_collections WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * FROM bj_collections WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function set($id, $id_space, $name) {
        if ($this->exists($id)) {
            $sql = "UPDATE bj_collections SET id_space=?, name=? WHERE id=?";
            $this->runRequest($sql, array($id_space, $name, $id));
            return $id;
        } else {
            $sql = "INSERT INTO bj_collections (id_space, name) VALUES (?,?)";
            $this->runRequest($sql, array($id_space, $name));
            return $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id) {
        $sql = "SELECT * from bj_collections WHERE id=?";
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
        $sql = "DELETE FROM bj_collections WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
