<?php

require_once 'Framework/Model.php';

/**
 * Class defining the config model
 *
 * @author Sylvain Prigent
 */
class CoreConfig extends Model {

    /**
     * Create the table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_config` (
                `keyname` varchar(30) NOT NULL DEFAULT '',
		`value` text NOT NULL DEFAULT '',
                `id_space` int(11) NOT NULL DEFAULT 0
		);";

        $this->runRequest($sql);
        
        $sqlCol = "SHOW COLUMNS FROM `core_config` LIKE 'id';";
        $reqCol = $this->runRequest($sqlCol);
       
        if ($reqCol->rowCount() > 0){
            $sql2 = "ALTER TABLE core_config CHANGE id `keyname` varchar(30) NOT NULL;";
            $this->runRequest($sql2);
            $sql3 = "alter table core_config drop primary key;";
            $this->runRequest($sql3);
        }
        
        $this->addColumn('core_config', 'id_space', 'int(11)', 0);
    }

    /**
     * Create the application contact
     * 
     * @return PDOStatement
     */
    public function createDefaultConfig() {

        $this->setParam("admin_email", "firstname.name@adress.com");
        $this->setParam("user_desactivate", "0");
        $this->setParam("logo", "Themes/logo.jpg");
        $this->setParam("home_title", "Database");
        $this->setParam("home_message", "");
    }

    /**
     * Check if a config key exists
     */
    public function isKey($key, $id_space) {
        $sql = "SELECT keyname FROM core_config WHERE keyname=? AND id_space=?";
        $unit = $this->runRequest($sql, array($key, $id_space));
        if ($unit->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a config parameter
     * @param string $key
     * @param string $value
     */
    public function addParam($key, $value, $id_space = 0) {
        $sql = "INSERT INTO core_config (keyname, value, id_space) VALUES(?,?,?)";
        $this->runRequest($sql, array($key, $value, $id_space));
    }

    /**
     * Update a parameter
     * @param string $key
     * @param string $value
     */
    public function updateParam($key, $value, $id_space = 0) {
        $sql = "update core_config set value=?  where keyname=? AND id_space=?";
        $this->runRequest($sql, array($value, $id_space, $key));
    }

    /**
     * Get a parameter
     * @param string $key
     * @return string: value
     */
    public function getParam($key) {
        $sql = "SELECT value FROM core_config WHERE keyname=?";
        $req = $this->runRequest($sql, array($key));

        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

   /**
     * Get a parameter
     * @param string $key
     * @return string: value
     */
    public function getParamSpace($key, $id_space) {
        $sql = "SELECT value FROM core_config WHERE keyname=? AND id_space=?";
        $req = $this->runRequest($sql, array($key, $id_space));

        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    /**
     * Set a parameter (add if not exists, otherwise update)
     * @param string $key
     * @param string $value
     */
    public function setParam($key, $value, $id_space = 0) {
        if ($this->isKey($key, $id_space)) {
            $this->updateParam($key, $value, $id_space);
        } else {
            $this->addParam($key, $value, $id_space);
        }
    }

    public function initParam($key, $value) {
        if (!$this->isKey($key, 0)) {
            $this->addParam($key, $value);
        }
    }

}
