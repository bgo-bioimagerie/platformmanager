<?php

require_once 'Framework/Model.php';

/**
 * Model to store the module menu configuration
 *
 * @author Sylvain Prigent
 */
class CoreAdminMenu extends Model {

    /**
     * Create the menus tables
     *
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "core_adminmenu";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(40)", "");
        $this->setColumnsInfo("link", "varchar(150)", "");
        $this->setColumnsInfo("icon", "varchar(40)", "");
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function setAdminMenu($name, $link, $icon, $status) {
        if ($status > 0) {
            if (!$this->isAdminMenu($name)) {
                $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
                $this->runRequest($sql, array($name, $link, $icon));
            }
        } else {
            $sql = "DELETE FROM core_adminmenu WHERE name=?";
            $this->runRequest($sql, array($name));
        }
    }

    /**
     * Add the default menus
     */
    public function addCoreDefaultMenus() {

        if (!$this->isAdminMenu("Menus")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Menus", "coremainmenus", "bi-list"));
        }
        else{
            $sql = "UPDATE core_adminmenu SET link=?, icon=? WHERE name=?";
            $this->runRequest($sql, array("coremainmenus", "bi-list", "Menus"));
        }

        if (!$this->isAdminMenu("Spaces")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Spaces", "spaceadmin", "bi-briefcase"));
        }

        if (!$this->isAdminMenu("Core")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Core", "coreconfigadmin", "bi-gear-fill"));
        }

        if (!$this->isAdminMenu("Users")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Users", "coreusers", "bi-gear-fill"));
        }
    }

    // Admin menu methods
    /**
     * Add an admin menu
     * @param string $name Menu name
     * @param string $link Url link
     * @param string $icon Menu bootstrap icon (for home page)
     * @return PDOStatement
     */
    public function addAdminMenu($name, $link, $icon) {
        $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
        $pdo = $this->runRequest($sql, array($name, $link, $icon));
        return $pdo;
    }

    /**
     * Remove an admin menu
     * @param unknown $name Menu name
     * @return PDOStatement
     */
    public function removeAdminMenu($name) {
        $sql = "DELETE FROM core_adminmenu
				WHERE name='" . $name . "';";
        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Check if an admin menu exists
     * @param string $name Menu name
     * @return boolean
     */
    public function isAdminMenu($name) {
        $sql = "select id from core_adminmenu where name=?";
        $unit = $this->runRequest($sql, array($name));
        if ($unit->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all admin menus query
     * @return multitype
     */
    public function getAdminMenus() {
        $sql = "select name, link, icon from core_adminmenu";
        $data = $this->runRequest($sql);
        return $data->fetchAll();
    }

}
