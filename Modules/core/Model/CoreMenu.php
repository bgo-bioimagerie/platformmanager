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
		);";
        $this->runRequest($sql);

	$sql2 = "CREATE TABLE IF NOT EXISTS `core_datamenu` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(40) NOT NULL DEFAULT '',
		`link` varchar(150) NOT NULL DEFAULT '',
		`icon` varchar(300) NOT NULL DEFAULT '',
                `id_menu` int(11) NOT NULL DEFAULT 1,
                `color` varchar(7) NOT NULL DEFAULT '#428bca',
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql2);
        
        $this->addColumn('core_datamenu', 'color', "varchar(7)", "#428bca");
        
        
                
        $sql3 = "CREATE TABLE IF NOT EXISTS `core_menu` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(100) NOT NULL DEFAULT '',
                `display_order` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql3);
    }

    public function getItemsFormMenu($id_menu){
        $sql = "SELECT * FROM core_datamenu WHERE id_menu=?";
        $req = $this->runRequest($sql, array($id_menu))->fetchAll();
        return $req;
    }
    public function getItem($id){
        $sql = "SELECT * FROM core_datamenu WHERE id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req;
    }
    
    public function menuName($id){
        $sql = "SELECT name FROM core_menu WHERE id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req[0];
    }
    
    public function getItems(){
        $sql = "SELECT * FROM core_datamenu ORDER BY name ASC;";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function getMenus($sort){
        $sql = "SELECT * FROM core_menu ORDER BY " . $sort . " ASC;";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function setMenu($id, $name, $displayOrder){
        if ($this->isMenu($id)){
            $sql = "UPDATE core_menu SET name=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $displayOrder, $id));
            return $id;
        }
        else{
            $sql= "INSERT INTO core_menu (name, display_order) VALUES(?,?)";
            $this->runRequest($sql, array($name, $displayOrder));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function isMenu($id){
        $sql = "select id from core_menu where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1){
            return true;
        }
        else{
            return false;
        }
    }
    
        public function removeUnlistedMenus($packageID) {

        $sql = "select id from core_menu";
        $req = $this->runRequest($sql);
        $databasePackages = $req->fetchAll();

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage["id"] == $pid) {
                    //echo "found package " . $pid . "in the database <br/>";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                //echo "delete pacjkage id = " . $dbPackage["id"] . " package-id = " . $dbPackage["id_package"] . "<br/>"; 
                $this->deleteMenu($dbPackage["id"]);
            }
        }
    }
    
    public function deleteMenu($id){
        $sql = "DELETE FROM core_menu WHERE id = ?";
        $this->runRequest($sql, array($id));
    }
    
    public function setAdminMenu($name, $link, $icon, $status){
        if ($status > 0){
            if (!$this->isAdminMenu($name)) {
                $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
                $this->runRequest($sql, array($name, $link, $icon));
            }
        }
        else{
            $sql = "DELETE FROM core_adminmenu WHERE name=?";
            $this->runRequest($sql, array($name));
        }
    }
    
    /**
     * Add the default menus
     */
    public function addCoreDefaultMenus() {
        if (!$this->isAdminMenu("Update")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Update", "coreupdate", "glyphicon-th-large"));
        }

        if (!$this->isAdminMenu("Menus")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Menus", "coremenus", "glyphicon-th-list"));
        }
        
        if (!$this->isAdminMenu("Spaces")) {
            $sql = "INSERT INTO core_adminmenu (name, link, icon) VALUES(?,?,?)";
            $this->runRequest($sql, array("Spaces", "spaceadmin", "glyphicon-briefcase"));
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
    public function addDataMenu($name, $link, $icon, $color) {
        $sql = "INSERT INTO core_datamenu (name, link, icon, color) VALUES(?,?,?,?,?)";
        $pdo = $this->runRequest($sql, array($name, $link, $icon, $color));
        return $pdo;
    }

    /**
     * Remove a data (tool) menu
     * @param string $name Menu name
     * @return PDOStatement
     */
    public function removeDataMenu($id) {
        $sql = "DELETE FROM core_datamenu WHERE id=?";
        $this->runRequest($sql, array($id));
    }

    /**
     * Check if a data (tool) menu exists
     * @param unknown $name
     * @return boolean
     */
    public function isDataMenu($name) {
        $sql = "select id from core_datamenu where id=?";
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
     * @param string $id ID
     * @param string $name Menu Name
     * @param string $url Url link
     * @param string $id_menu ID of the parent menu
     */
    public function setDataMenu($id, $name, $url, $id_menu, $color) {

        if ($this->isDataMenu($id)){
            $sql = "UPDATE core_datamenu SET name=?, link=?, id_menu=?, color=? WHERE id=?";
            $this->runRequest($sql, array($name, $url, $id_menu, $color, $id));
            return $id;
        }
        else{
            $sql = "INSERT INTO core_datamenu (name, link, id_menu, color) VALUES(?,?,?,?)";
            $this->runRequest($sql, array($name, $url, $id_menu,$color));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function setDataMenuIcon($id, $url){
        $sql = "UPDATE core_datamenu SET icon=? WHERE id=?";
        $this->runRequest($sql, array($url, $id));
    }

}
