<?php

require_once 'Framework/Model.php';

class ClClientUser extends Model {

    public function __construct() {
        $this->tableName = "cl_j_client_user";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getUsersInfo($id_client) {
        $sql = "SELECT * FROM core_users WHERE id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=?)";
        return $this->runRequest($sql, array($id_client))->fetchAll();
    }
    
    public function getUserClientAccounts($id_user, $id_space){
        
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND id IN (SELECT id_client FROM cl_j_client_user WHERE id_user=?)";
        return $this->runRequest( $sql, array($id_space, $id_user) )->fetchAll();
    }
    

    public function set($id_client, $id_user){
        if (!$this->exists($id_client, $id_user)){
            $sql = "INSERT INTO cl_j_client_user (id_client, id_user) VALUES (?,?)";
            $this->runRequest($sql, array($id_client, $id_user));
        }
    }
    
    public function exists($id_client, $id_user){
        $sql = "SELECT id FROM cl_j_client_user WHERE id_client=? AND id_user=?";
        $req = $this->runRequest($sql, array($id_client, $id_user));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function delete($id) {
        $sql = "DELETE FROM cl_j_client_user WHERE id=?";
        $this->runRequest($sql, array($id));
    }

    
    public function deleteClientUser($id_client, $id_user) {
        $sql = "DELETE FROM cl_j_client_user WHERE id_client=? AND id_user=?";
        $this->runRequest($sql, array($id_client, $id_user));
    }
}
