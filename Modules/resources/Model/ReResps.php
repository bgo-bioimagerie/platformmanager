<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ReResps extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_resps";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("id_status", "int(11)", 0);
        $this->primaryKey = "id";
    }

    // [multi_-tenant] : filter by id_space ?
    public function getResourcesManagersEmails($id_space, $id_resource) {

        $sql = "SELECT email FROM core_users WHERE id IN (SELECT id_user FROM re_resps WHERE id_resource=? AND id_status>0 AND id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_resource, $id_space));
        return $req->fetchAll();
    }

    public function mergeUsers($users) {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "UPDATE re_resps SET id_user=? WHERE id_user=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }

    public function getResps($id_space, $id_resource) {
        $sql = "SELECT * FROM re_resps WHERE id_resource=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();
    }

    public function addResp($id_space, $id_resource, $id_user, $id_status) {
        $sql = "INSERT INTO re_resps (id_resource, id_user, id_status, id_space) VALUES (?,?,?,?)";
        $this->runRequest($sql, array($id_resource, $id_user, $id_status, $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    public function setResp($id_space, $id_resource, $id_user, $id_status) {
        $id = $this->getResRespID($id_space, $id_resource, $id_user);
        if ($id > 0) {
            $sql = "UPDATE re_resps SET id_resource=?, id_user=?, id_status=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_resource, $id_user, $id_status, $id, $id_space));
        } else {
            $this->addResp($id_space, $id_resource, $id_user, $id_status);
        }
    }

    public function getResRespID($id_space, $id_resource, $id_user) {
        $sql = "SELECT id FROM re_resps WHERE id_resource=? AND id_user=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_resource, $id_user, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function clean($id_space, $id_resource, $id_users) {
        $sql = "SELECT id, id_user FROM re_resps WHERE id_resource=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();

        foreach ($data as $dat) {
            if (!in_array($dat["id_user"], $id_users)) {
                $sql = "DELETE FROM re_resps WHERE id=? AND id_space=? AND deleted=0";
                $this->runRequest($sql, array($dat["id"]));
            }
        }
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id) {
        $sql = "DELETE FROM re_resps WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
