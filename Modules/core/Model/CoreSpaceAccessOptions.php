<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreSpaceAccessOptions extends Model
{
    /**
     * Create the status table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "core_space_access_options";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "varchar(100)", "");
        $this->setColumnsInfo("toolname", "varchar(100)", "");
        $this->setColumnsInfo("module", "varchar(100)", "");
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM core_space_access_options WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function set($idSpace, $toolname, $module, $url)
    {
        if (!$this->exists($idSpace, $toolname)) {
            $sql = "INSERT INTO core_space_access_options (id_space, toolname, module, url) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($idSpace, $toolname, $module, $url));
        } else {
            $sql = "UPDATE core_space_access_options SET module=?, url=? WHERE id_space=? AND toolname=?";
            $this->runRequest($sql, array($module, $url, $idSpace, $toolname));
        }
    }

    public function exists($idSpace, $toolname)
    {
        $sql = "SELECT id FROM core_space_access_options WHERE id_space=? AND toolname=?";
        $req = $this->runRequest($sql, array($idSpace, $toolname));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function delete($idSpace, $toolname)
    {
        $sql = "UPDATE core_space_access_options SET deleted=1,deleted_at=NOW() WHERE toolname=? AND id_space=?";
        $this->runRequest($sql, array($toolname, $idSpace));
    }

    public function reactivate($idSpace, $toolname)
    {
        $sql = "UPDATE core_space_access_options SET deleted=0, deleted_at=NULL WHERE toolname=? AND id_space=?";
        $this->runRequest($sql, array($toolname, $idSpace));
    }
}
