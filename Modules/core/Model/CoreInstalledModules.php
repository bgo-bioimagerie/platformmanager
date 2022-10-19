<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreInstalledModules extends Model
{
    public function __construct()
    {
        $this->tableName = "core_installed_modules";
    }

    /**
     * Create the status table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `core_installed_modules` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(30) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function setModule($name)
    {
        if (!$this->isModule($name)) {
            $sql = "INSERT INTO core_installed_modules (name) VALUES (?)";
            $this->runRequest($sql, array($name));
        }
    }

    public function isModule($name)
    {
        $sql = "SELECT id FROM core_installed_modules WHERE name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getModules()
    {
        $sql = "SELECT * FROM core_installed_modules ORDER BY name ASC;";
        return $this->runRequest($sql)->fetchAll();
    }
}
