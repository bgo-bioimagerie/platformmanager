<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReEventType extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_event_type";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_site", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_event_type WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM re_event_type WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function getAll($sort = "name") {
        $sql = "SELECT re_event_type.*, ec_sites.name AS site "
                . " FROM re_event_type "
                . " INNER JOIN ec_sites ON ec_sites.id = re_event_type.id_site "
                . "ORDER BY re_event_type." . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }

    public function set($id, $name, $id_site) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_event_type SET name=?, id_site=? WHERE id=?";
            $id = $this->runRequest($sql, array($name, $id_site, $id));
        } else {
            $sql = "INSERT INTO re_event_type (name, id_site) VALUES (?,?)";
            $this->runRequest($sql, array($name, $id_site));
        }
        return $id;
    }

    public function exists($id) {
        $sql = "SELECT id from re_event_type WHERE id=?";
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
        $sql = "DELETE FROM re_event_type WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
