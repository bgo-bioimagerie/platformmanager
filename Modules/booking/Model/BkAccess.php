<?php

require_once 'Framework/Model.php';

/**
 * Class defining the BkAccess model
 *
 * @author Sylvain Prigent
 */
class BkAccess extends Model {

    /**
     * Create the BkAccess table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bk_access";
        //$this->setColumnsInfo("id_resource", "int(11)", 0);
        //$this->setColumnsInfo("id_access", "int(11)", 0);
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_access` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_resource` int NOT NULL DEFAULT '0',
            `id_access` int NOT NULL DEFAULT '0',
            `id_space` int NOT NULL,
            PRIMARY KEY (`id`)
        );";
    
        $this->runRequest($sql);
    }

    public function set($id_space, $id_resources, $id_access){
        if ($this->exists($id_space, $id_resources)){
            $sql = "UPDATE bk_access SET id_access=? WHERE id_resource=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_access, $id_resources, $id_space));
        }
        else{
            $sql = "INSERT INTO bk_access (id_resource, id_access, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_resources, $id_access, $id_space));
        }
    }

    public function getAll($id_space, $sortentry = 'id') {
        $sql = "SELECT * FROM bk_access WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function get($id_space, $id_resource) {
        $sql = "SELECT * FROM bk_access WHERE id_resource=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_resource, $id_space));
        return $user->fetch();
    }
    
    public function getAccessId($id_space, $id) {

        $sql = "SELECT id_access FROM bk_access WHERE id_resource=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id, $id_space));
        $tmp = $user->fetch();
        return  $tmp ? $tmp[0] : null;
    }
    
    

    /**
     * CHeck if a color code exists from name
     * @param unknown $id
     * @return boolean
     */
    public function exists($id_space, $id) {
        $sql = "select * from bk_access where id_resource=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        return ($req->rowCount() == 1);
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($id_space, $id_resource) {
        $sql = "UPDATE bk_access SET deleted=1,deleted_at=NOW() WHERE id_resource=? AND id_space=?";
        $this->runRequest($sql, array($id_resource, $id_space));
    }

}
