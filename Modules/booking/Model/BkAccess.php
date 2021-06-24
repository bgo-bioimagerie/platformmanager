<?php

require_once 'Framework/Model.php';

/**
 * Class defining the SyColorCode model
 *
 * @author Sylvain Prigent
 */
class BkAccess extends Model {

    /**
     * Create the SyColorCode table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bk_access";
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("id_access", "int(11)", 0);
    }

    public function set($id_resources, $id_access){
        if ($this->exists($id_resources)){
            $sql = "UPDATE bk_access SET id_access=? WHERE id_resource=?";
            $this->runRequest($sql, array($id_access, $id_resources));
        }
        else{
            $sql = "INSERT INTO bk_access (id_resource, id_access) VALUES (?,?)";
            $this->runRequest($sql, array($id_resources, $id_access));
        }
    }

    public function getAll($sortentry = 'id') {

        $sql = "select * from bk_access order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    public function get($id) {

        $sql = "SELECT * FROM bk_access WHERE id_resource=?";
        $user = $this->runRequest($sql, array($id));
        return $user->fetch();
    }
    
    public function getAccessId($id) {

        $sql = "SELECT id_access FROM bk_access WHERE id_resource=?";
        $user = $this->runRequest($sql, array($id));
        $tmp = $user->fetch();
        return  $tmp ? $tmp[0] : null;
    }
    
    

    /**
     * CHeck if a color code exists from name
     * @param unknown $id
     * @return boolean
     */
    public function exists($id) {
        $sql = "select * from bk_access where id_resource=?";
        $req = $this->runRequest($sql, array($id));
        return ($req->rowCount() == 1);
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($id) {
        $sql = "DELETE FROM bk_access WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
