<?php

require_once 'Framework/Model.php';

class ClClientUser extends Model
{
    public function __construct()
    {
        $this->tableName = "cl_j_client_user";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getUsersInfo($idSpace, $id_client)
    {
        $sql = "SELECT * FROM core_users WHERE id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=? AND id_space=? AND deleted=0)";
        return $this->runRequest($sql, array($id_client, $idSpace))->fetchAll();
    }

    public function getUserClientAccounts($idUser, $idSpace)
    {
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0 AND id IN (SELECT id_client FROM cl_j_client_user WHERE id_user=?) ORDER BY name";
        return $this->runRequest($sql, array($idSpace, $idUser))->fetchAll();
    }

    public function getClientUsersAccounts($id_client, $idSpace)
    {
        $sql = "SELECT * FROM core_users WHERE deleted=0 AND id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=? AND id_space=?) ORDER BY name";
        return $this->runRequest($sql, array($id_client, $idSpace))->fetchAll();
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM cl_j_client_user WHERE id_space=? AND deleted=0;";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function set($idSpace, $id_client, $idUser)
    {
        if (!$this->exists($idSpace, $id_client, $idUser)) {
            $sql = "INSERT INTO cl_j_client_user (id_client, id_user, id_space) VALUES (?,?, ?)";
            $this->runRequest($sql, array($id_client, $idUser, $idSpace));
        }
    }

    public function exists($idSpace, $id_client, $idUser)
    {
        $sql = "SELECT id FROM cl_j_client_user WHERE id_client=? AND id_user=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_client, $idUser, $idSpace));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function delete($idSpace, $id)
    {
        $sql = "DELETE FROM cl_j_client_user WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

    public function deleteClientUser($idSpace, $id_client, $idUser)
    {
        $sql = "DELETE FROM cl_j_client_user WHERE id_client=? AND id_user=? AND id_space=?";
        $this->runRequest($sql, array($id_client, $idUser, $idSpace));
    }

    public function deleteClientUsers($idSpace, $id_client)
    {
        $sql = "DELETE FROM cl_j_client_user WHERE id_client=?AND id_space=?";
        $this->runRequest($sql, array($id_client, $idSpace));
    }
}
