<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReRespsStatus extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_resps_status";
        $sql = 'CREATE TABLE IF NOT EXISTS `re_resps_status` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(250) DEFAULT NULL,
            `id_space` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
          )';
        $this->runRequest($sql);
        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `re_resps_status` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(250) DEFAULT NULL,
            `id_space` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
          )';
        $this->runRequest($sql);
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM re_resps_status WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getName($id_space, $id) {
        $sql = "SELECT name FROM re_resps_status WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $tmp? $tmp[0]: null;
    }

    public function getForSpace($id_space){
         $sql = "SELECT * FROM re_resps_status WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function set($id, $name, $id_space) {
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE re_resps_status SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $id_space));
        } else {
            $sql = "INSERT INTO re_resps_status (name, id_space) VALUES (?, ?)";
            $this->runRequest($sql, array($name, $id_space));
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function exists($id_space, $id) {
        $sql = "SELECT id from re_resps_status WHERE id=? AND id_space=? AND deleted=0";
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
        $sql = "UPDATE re_resps SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
