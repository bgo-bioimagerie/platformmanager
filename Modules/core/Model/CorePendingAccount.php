<?php

require_once 'Framework/Model.php';

class CorePendingAccount extends Model {

    public function __construct() {
        $this->tableName = "core_pending_accounts";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("validated", "int(1)", 0);
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->setColumnsInfo("validated_by", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function validate($id, $validated_by){
        $sql = "UPDATE core_pending_accounts SET validated=?, date=?, validated_by=? WHERE id=?";
        $this->runRequest($sql, array(1, date('Y-m-d'), $validated_by, $id));
    }
    
    public function add($id_user, $id_space){
        $sql = "INSERT INTO core_pending_accounts (id_user, id_space, validated) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_user, $id_space, 0));
    }
    
    public function getPendingForSpace($id_space){
        $sql = "SELECT * FROM core_pending_accounts WHERE id_space=? AND validated=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getSpaceIdsForPending($id_user){
        $sql = "SELECT id_space FROM core_pending_accounts WHERE id_user=? AND validated=0";
        return $this->runRequest($sql, array($id_user))->fetchAll();
    }

    public function getBySpaceIdAndUserId($id_space, $id_user){
        $sql = "SELECT * FROM core_pending_accounts WHERE id_space=? AND id_user=?";
        return $this->runRequest($sql, array($id_space, $id_user))->fetch();
    }
    
    public function get($id){
        $sql = "SELECT * FROM core_pending_accounts WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function deleteByPendingAccountId($id_pendingAccount){
        $sql = "DELETE FROM core_pending_accounts WHERE id=?";
        $this->runRequest($sql, array($id_pendingAccount));
    }

    public function deleteBySpaceIdAndUserId($id_space, $id_user){
        $sql = "DELETE FROM core_pending_accounts WHERE (id_space=? AND id_user=?)";
        $this->runRequest($sql, array($id_space, $id_user));
    }
 
}
