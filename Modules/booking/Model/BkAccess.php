<?php

require_once 'Framework/Model.php';

/**
 * Class defining the BkAccess model
 *
 * @author Sylvain Prigent
 */
class BkAccess extends Model
{
    public static int $AccessUser = 1;
    public static int $AccessAuthorizedUser = 2;
    public static int $AccessManager = 3;
    public static int $AccessAdmin = 4;

    /**
     * Create the BkAccess table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "bk_access";
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("id_access", "int(11)", 0);
    }

    public function set($idSpace, $id_resources, $id_access)
    {
        if ($this->exists($idSpace, $id_resources)) {
            $sql = "UPDATE bk_access SET id_access=? WHERE id_resource=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_access, $id_resources, $idSpace));
        } else {
            $sql = "INSERT INTO bk_access (id_resource, id_access, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_resources, $id_access, $idSpace));
        }
    }

    public function getAll($idSpace, $sortentry = 'id_access')
    {
        $sql = "SELECT * FROM bk_access WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    public function get($idSpace, $id_resource)
    {
        $sql = "SELECT * FROM bk_access WHERE id_resource=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_resource, $idSpace));
        return $user->fetch();
    }

    public function getAccessId($idSpace, $id)
    {
        $sql = "SELECT id_access FROM bk_access WHERE id_resource=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id, $idSpace));
        $tmp = $user->fetch();
        return  $tmp ? $tmp[0] : null;
    }

    public function getAccessIds($idSpace, array $ids)
    {
        if (empty($ids)) {
            return [];
        }
        $sql = "SELECT id_resource, id_access FROM bk_access WHERE id_resource IN (".implode(',', $ids).") AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }



    /**
     * CHeck if a color code exists from name
     * @param unknown $id
     * @return boolean
     */
    public function exists($idSpace, $id)
    {
        $sql = "select * from bk_access where id_resource=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        return ($req->rowCount() == 1);
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($idSpace, $id_resource)
    {
        $sql = "UPDATE bk_access SET deleted=1,deleted_at=NOW() WHERE id_resource=? AND id_space=?";
        $this->runRequest($sql, array($id_resource, $idSpace));
    }
}
