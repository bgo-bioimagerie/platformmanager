<?php

require_once 'Framework/Model.php';

/**
 * case validated=0 and validated_by IS NULL => request is pending 
 * case validated=0 and validated_by NOT NULL => space admin has rejected the join request
 * case validated=1 and validated_by NOT NULL => space admin has accepted the join request
 * 
 */
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

    public function invalidate($id, $validated_by){
        Configuration::getLogger()->debug("INVALIDATE", ["validated" => $validated_by, "pendingId" => $id]);
        $sql = "UPDATE core_pending_accounts SET validated=?, date=?, validated_by=? WHERE id=?";
        $this->runRequest($sql, array(0, date('Y-m-d'), $validated_by, $id));
    }

    public function add($id_user, $id_space){
        $sql = "INSERT INTO core_pending_accounts (id_user, id_space, validated) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_user, $id_space, 0));
    }

    /**
     * 
     * Returns true if there is at least 1 association between the space and the user in core_pending_accounts 
     * 
     * @param int $id_space
     * @param int $id_user
     * 
     * @return bool
     */
    public function exists($id_space, $id_user){
        $sql = "SELECT id FROM core_pending_accounts WHERE id_user=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    /**
     * 
     * Returns true if user has requested to join a space and his request is still not accepted / rejected
     * 
     * @param int $id_space
     * @param int $id_user
     * 
     * @return bool
     */
    public function isActuallyPending($id_space, $id_user){
        $sql = "SELECT id FROM core_pending_accounts WHERE id_user=? AND id_space=? AND validated=0 AND validated_by IS NULL";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }
 
    public function getPendingForSpace($id_space){
        $sql = "SELECT * FROM core_pending_accounts WHERE id_space=? AND validated=0 AND validated_by IS NULL";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getSpaceIdsForPending($id_user){
        $sql = "SELECT id_space FROM core_pending_accounts WHERE id_user=? AND validated=0 AND validated_by IS NULL";
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
