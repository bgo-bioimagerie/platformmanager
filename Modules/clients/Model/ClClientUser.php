<?php

require_once 'Framework/Model.php';

class ClClientUser extends Model {

    public function __construct() {
        $this->tableName = "cl_j_client_user";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getUsersInfo($id_space, $id_client) {
        $sql = "SELECT * FROM core_users WHERE id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=? AND id_space=? AND deleted=0)";
        return $this->runRequest($sql, array($id_client, $id_space))->fetchAll();
    }
    
    public function getUserClientAccounts($id_user, $id_space){
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0 AND id IN (SELECT id_client FROM cl_j_client_user WHERE id_user=?) ORDER BY name";
        return $this->runRequest( $sql, array($id_space, $id_user))->fetchAll();
    }

    public function getClientUsersAccounts($id_client, $id_space){
        $sql = "SELECT * FROM core_users WHERE deleted=0 AND id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=? AND id_space=?) ORDER BY name";
        return $this->runRequest( $sql, array($id_client, $id_space))->fetchAll();
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM cl_j_client_user WHERE id_space=? AND deleted=0;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function set($id_space, $id_client, $id_user){
        if (!$this->exists($id_space, $id_client, $id_user)){
            $sql = "INSERT INTO cl_j_client_user (id_client, id_user, id_space) VALUES (?,?, ?)";
            $this->runRequest($sql, array($id_client, $id_user, $id_space));
        }
    }
    
    public function exists($id_space, $id_client, $id_user){
        $sql = "SELECT id FROM cl_j_client_user WHERE id_client=? AND id_user=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_client, $id_user, $id_space));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function delete($id_space ,$id) {
        $sql = "DELETE FROM cl_j_client_user WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }
    
    public function deleteClientUser($id_space, $id_client, $id_user) {
        $sql = "DELETE FROM cl_j_client_user WHERE id_client=? AND id_user=? AND id_space=?";
        $this->runRequest($sql, array($id_client, $id_user, $id_space));
    }
}
