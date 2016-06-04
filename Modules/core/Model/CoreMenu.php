<?php

require_once 'Framework/Model.php';

/**
 * Model to store the module menu configuration
 *
 * @author Sylvain Prigent
 */
class CoreMenu extends Model {

    /**
     * Create the menus tables
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_adminmenu` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(40) NOT NULL DEFAULT '',
		`link` varchar(150) NOT NULL DEFAULT '',
		`icon` varchar(40) NOT NULL DEFAULT '',		
		PRIMARY KEY (`id`)
		);

		CREATE TABLE IF NOT EXISTS `core_datamenu` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(40) NOT NULL DEFAULT '',
		`link` varchar(150) NOT NULL DEFAULT '',
		`usertype` int(11) NOT NULL,
		`icon` varchar(40) NOT NULL DEFAULT '',			
		PRIMARY KEY (`id`)
		);
		";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Add the default menus
     */
    public function addCoreDefaultMenus() {
        if (!$this->isAdminMenu("Modules")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Modules", "coremodulesmanager", "glyphicon-th-large"));
        }

        if (!$this->isDataMenu("users/institutions")) {
            $sql = "INSERT INTO core_datamenu (name, link, usertype, icon) VALUES(?,?,?,?)";
            $this->runRequest($sql, array("users/institutions", "coreusers", 3, "glyphicon-user"));
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
        if ($unit->rowCount() == 1){
            return true;
        }
        else{
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

    // data menu methods
    /**
     * Add a data (tool) menu
     * @param string $name Menu Name
     * @param string $link Url link
     * @param string $usertype Index of user who can see this menu
     * @param string $icon Menu icon (bootstrap icon)
     * @return PDOStatement
     */
    public function addDataMenu($name, $link, $usertype, $icon) {
        $sql = "INSERT INTO core_datamenu (name, link, usertype, icon) VALUES(?,?,?,?)";
        $pdo = $this->runRequest($sql, array($name, $link, $usertype, $icon));
        return $pdo;
    }

    /**
     * Remove a data (tool) menu
     * @param string $name Menu name
     * @return PDOStatement
     */
    public function removeDataMenu($name) {
        $sql = "DELETE FROM core_datamenu
				WHERE name='" . $name . "';";
        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Check if a data (tool) menu exists
     * @param unknown $name
     * @return boolean
     */
    public function isDataMenu($name) {
        $sql = "select id from core_datamenu where name=?";
        $unit = $this->runRequest($sql, array($name));
        if ($unit->rowCount() == 1){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Set (add if not exists therwise update) a data (tool) menu
     * @param string $name Menu Name
     * @param string $link Url link
     * @param string $usertype Index of user who can see this menu
     * @param string $icon Menu icon (bootstrap icon)
     */
    public function setDataMenu($name, $link, $usertype, $icon) {

        //echo "user type = ". $usertype . "</br>";
        $exists = $this->isDataMenu($name);
        if (!$exists && $usertype == 0) {
            //echo "do nothing </br>";
            return;
        }
        if ($exists && $usertype == 0) {
            //echo "remove </br>";
            $this->removeDataMenu($name);
            return;
        }
        if (!$exists && $usertype > 0) {
            //echo "add data menu </br>";
            $this->addDataMenu($name, $link, $usertype, $icon);
            return;
        }
        if ($exists && $usertype > 0) {
            //echo "update nothing </br>";
            $this->updateDataMenu($name, $link, $usertype, $icon);
            return;
        }
    }

    /**
     * Update a data (tool) menu
     * @param string $name Menu Name
     * @param string $link Url link
     * @param string $usertype Index of user who can see this menu
     * @param string $icon Menu icon (bootstrap icon)
     */
    public function updateDataMenu($name, $link, $usertype, $icon) {
        $sql = "select id from core_datamenu where name=?";
        $req = $this->runRequest($sql, array($name));
        $idt = $req->fetch();
        $id = $idt[0];

        $sqlu = "update core_datamenu set name=?, link=?, usertype=?, icon=? where id=?";
        $this->runRequest($sqlu, array("" . $name . "", "" . $link . "",
            "" . $usertype . "", "" . $icon . "", $id));
    }

    /**
     * Get the user type allowed to see a menu
     * @param string $name Menu name
     * @return number Allowed user type
     */
    public function getDataMenusUserType($name) {
        if ($this->isDataMenu($name)) {
            $sql = "select usertype from core_datamenu where name=?";
            $data = $this->runRequest($sql, array($name));
            $tmp = $data->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getMenuStatusByName($name){
        
        $sql1 = "SELECT usertype FROM core_datamenu WHERE name=?";
        $req1 = $this->runRequest($sql1, array($name));
        if ($req1->rowCount() > 0){
            $tmp = $req1->fetch();
            return $tmp[0];
        }
        $sql2 = "SELECT id FROM core_adminmenu WHERE name=?";
        $req2 = $this->runRequest($sql2, array($name));
        if ($req2->rowCount() > 0){
            return 5;
        }
        return 0;
    }

    /**
     * Get all the data (tool) menus for a given user type
     * @param number $user_status User type
     * @return multitype: Menus informations
     */
    public function getDataMenus($user_status = 1) {
        $sql = "select name, link, icon, usertype from core_datamenu where usertype<=?";
        $data = $this->runRequest($sql, array($user_status));
        return $data->fetchAll();
    }

}
