<?php

require_once 'Framework/Model.php';
require_once 'Modules/breeding/Model/BrPricing.php';

class EsUserClientAccount extends Model {

    public function __construct() {
        $this->tableName = "es_j_user_client_accounts";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("id_account", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getUsersForAccount($id_account) {
        $sql = "SELECT id_user FROM es_j_user_client_accounts WHERE id_account=?";
        return $this->runRequest($sql, array($id_account))->fetchAll();
    }

    public function getUserAccount($id_space, $id_user) {
        $sql = "SELECT id_account FROM es_j_user_client_accounts WHERE id_user=? AND id_account IN (SELECT id FROM es_client_accounts WHERE id_space=?)";
        $tmp = $this->runRequest($sql, array($id_user, $id_space))->fetch();
        return $tmp[0];
    }

    public function set($id_user, $id_account) {
        if (!$this->exists($id_user, $id_account)) {
            $sql = 'INSERT INTO es_j_user_client_accounts (id_user, id_account) VALUES (?,?)';
            $this->runRequest($sql, array($id_user, $id_account));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function exists($id_user, $id_account){
        $sql = "SELECT id FROM es_j_user_client_accounts WHERE id_user=? AND id_account=?";
        $req = $this->runRequest($sql, array($id_user, $id_account));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function delete($id) {
        $sql = "DELETE FROM es_j_user_client_accounts WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
