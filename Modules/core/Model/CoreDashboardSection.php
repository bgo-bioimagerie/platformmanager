<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreDashboardSection extends Model {

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "core_dashboard_sections";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("display_order", "int(11)", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM core_dashboard_sections WHERE id_space=? ORDER BY display_order ASC";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM core_dashboard_sections WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM core_dashboard_sections WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }

    public function set($id, $id_space, $name, $display_order) {
        if ($id == 0) {
            $sql = 'INSERT INTO core_dashboard_sections (id_space, name, display_order) VALUES (?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $display_order));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE core_dashboard_sections SET id_space=?, name=?, display_order=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $name, $display_order, $id));
            return $id;
        }
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM core_dashboard_sections WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM core_dashboard_sections WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
