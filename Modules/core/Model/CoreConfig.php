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
		`id` varchar(30) NOT NULL DEFAULT '',
		`value` text NOT NULL DEFAULT '',
		PRIMARY KEY (`id`)
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
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
    public function isKey($key) {
        $sql = "select id from core_config where id='" . $key . "'";
        $unit = $this->runRequest($sql);
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
    public function addParam($key, $value) {
        $sql = "INSERT INTO core_config (id, value) VALUES(?,?)";
        $this->runRequest($sql, array($key, $value));
    }

    /**
     * Update a parameter
     * @param string $key
     * @param string $value
     */
    public function updateParam($key, $value) {
        $sql = "update core_config set value=? where id=?";
        $this->runRequest($sql, array($value, $key));
    }

    /**
     * Get a parameter
     * @param string $key
     * @return string: value
     */
    public function getParam($key) {
        $sql = "SELECT value FROM core_config WHERE id=?";
        $req = $this->runRequest($sql, array($key));

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
    public function setParam($key, $value) {
        if ($this->isKey($key)) {
            $this->updateParam($key, $value);
        } else {
            $this->addParam($key, $value);
        }
    }
}
