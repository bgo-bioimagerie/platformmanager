<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreDashboardItem extends Model {

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
     public function __construct() {

        $this->tableName = "core_dashboard_items";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_section", "int(11)", 0);
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->setColumnsInfo("role", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("icon", "varchar(255)", "");
        $this->setColumnsInfo("color", "varchar(7)", "");
        $this->setColumnsInfo("bgcolor", "varchar(7)", "");
        $this->setColumnsInfo("width", "int(2)", "");
        $this->setColumnsInfo("display_order", "int(11)", "");
        $this->primaryKey = "id";
    }
    
    public function getSpaceMenus($id_space, $userRole){
        $sql = "SELECT * FROM core_dashboard_sections WHERE id_space=? ORDER BY display_order ASC;";
        $sections = $this->runRequest($sql, array($id_space))->fetchAll();
        
        $data = array();
        foreach($sections as $s){
            $sql = "SELECT * FROM core_dashboard_items WHERE id_section=? AND role<=?";
            $items = $this->runRequest($sql, array($s["id"], $userRole));
            foreach($items as $i){
                $data[] = array( "name" => $i["name"], "icon" => $i["icon"], 
                    "url" => $i["url"], "bgcolor" => $i["bgcolor"], 
                    "color" => $i["color"]);
            }
        }
        return $data;
    }
    
    public function getForSpace($id_space){
        $sql = "SELECT * FROM core_dashboard_items WHERE id_section IN ( SELECT id FROM core_dashboard_sections WHERE id_space=? ORDER BY display_order ASC);";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getAll($id_section) {
        $sql = "SELECT * FROM core_dashboard_items WHERE id_section=?";
        return $this->runRequest($sql, array($id_section))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM core_dashboard_items WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM core_dashboard_items WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }

    public function set($id, $id_section, $url, $role, $name, $icon, $color, $bgcolor, $width, $display_order) {
        if ($id == 0) {
            $sql = 'INSERT INTO core_dashboard_items (id_section, url, role, name, icon, '
                    . 'color, bgcolor, width, display_order) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_section, $url, $role, $name, $icon, $color, $bgcolor, $width, $display_order));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE core_dashboard_items SET id_section=?, url=?, role=?, name=?, icon=?, color=?, bgcolor=?, width=?, display_order=? WHERE id=?';
            $this->runRequest($sql, array($id_section, $url, $role, $name, $icon, $color, $bgcolor, $width, $display_order, $id));
            return $id;
        }
    }

    public function getForSection($id_section, $role){
        $sql = "SELECT * FROM core_dashboard_items WHERE id_section=? AND role<=? ORDER BY display_order ASC;";
        return $this->runRequest($sql, array($id_section, $role))->fetchAll();
    }


    public function getForList($id_section) {
        $sql = "SELECT * FROM core_dashboard_items WHERE id_section=?";
        $data = $this->runRequest($sql, array($id_section))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function delete($id) {
        $sql = "DELETE FROM core_dashboard_items WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
