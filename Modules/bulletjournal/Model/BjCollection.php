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

    public function get($id_space, $id) {
        $sql = "SELECT * FROM bj_collections WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * FROM bj_collections WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function set($id, $id_space, $name) {
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE bj_collections SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $id_space));
        } else {
            $sql = "INSERT INTO bj_collections (id_space, name) VALUES (?,?)";
            $this->runRequest($sql, array($id_space, $name));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id_space, $id) {
        $sql = "SELECT * from bj_collections WHERE id=? AND id_space=? AND deleted=0";
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
        $sql = "UPDATE bj_collections SET deleted=1,deleted_at=NOW() WHERE id=? ANd id_space=?";
        //$sql = "DELETE FROM bj_collections WHERE id = ?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
