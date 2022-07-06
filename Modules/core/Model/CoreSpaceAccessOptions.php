<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreSpaceAccessOptions extends Model {

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        $this->tableName = "core_space_access_options";
        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "varchar(100)", "");
        $this->setColumnsInfo("toolname", "varchar(100)", "");
        $this->setColumnsInfo("module", "varchar(100)", "");
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->primaryKey = "id";
        */

    }

    public function createTable()
    {
        $sql='CREATE TABLE IF NOT EXISTS `core_space_access_options` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL DEFAULT 0,
            `toolname` varchar(100) DEFAULT NULL,
            `module` varchar(100) DEFAULT NULL,
            `url` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        )';
        $this->runRequest($sql);
        $this->baseSchema();
    }
    
    public function getAll($id_space){
        $sql = "SELECT * FROM core_space_access_options WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function set($id_space, $toolname, $module, $url) {
        if (!$this->exists($id_space, $toolname)){
            $sql = "INSERT INTO core_space_access_options (id_space, toolname, module, url) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_space, $toolname, $module, $url));
        }
        else{
            $sql = "UPDATE core_space_access_options SET module=?, url=? WHERE id_space=? AND toolname=?";
            $this->runRequest($sql, array($module, $url, $id_space, $toolname));
        }
    }
    
    public function exists($id_space, $toolname){
        $sql = "SELECT id FROM core_space_access_options WHERE id_space=? AND toolname=?";
        $req = $this->runRequest($sql, array($id_space, $toolname));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }
    
    public function delete($id_space, $toolname) {
        $sql = "UPDATE core_space_access_options SET deleted=1,deleted_at=NOW() WHERE toolname=? AND id_space=?";
        $this->runRequest($sql, array($toolname, $id_space));
    }

    public function reactivate($id_space, $toolname) {
        $sql = "UPDATE core_space_access_options SET deleted=0, deleted_at=NULL WHERE toolname=? AND id_space=?";
        $this->runRequest($sql, array($toolname, $id_space));
    }
    
}
