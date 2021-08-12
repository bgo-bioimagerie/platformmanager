<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreSpaceUser extends Model {

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "core_j_spaces_user";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", "");
        $this->setColumnsInfo("status", "varchar(100)", "");
        $this->setColumnsInfo("date_convention", "date", "");
        $this->setColumnsInfo("convention_url", "varchar(255)", "");
        $this->setColumnsInfo("date_contract_end", "date", "");
        $this->primaryKey = "id";

    }
    
    public function setRole($id_user, $id_space, $role){
        if ( !$this->exists($id_user, $id_space) ){
            $sql = "INSERT INTO core_j_spaces_user (id_user, id_space, status) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_user, $id_space, $role));
            Events::send([
                "action" => Events::ACTION_SPACE_USER_JOIN,
                "space" => ["id" => intval($id_space)],
                "user" => ["id" => intval($id_user)]
            ]);
        }
        else{
            $sql = "UPDATE core_j_spaces_user SET status=? WHERE id_user=? AND id_space=?";
            $this->runRequest($sql, array($role, $id_user, $id_space));
            Events::send([
                "action" => Events::ACTION_SPACE_USER_ROLEUPDATE,
                "space" => ["id" => intval($id_space)],
                "user" => ["id" => intval($id_user)],
                "role" => $role
            ]);    
        }
        
        if ( $role > 0 ){
            $sql = "UPDATE core_users SET is_active=? where id=?";
            $this->runRequest($sql, array(1, $id_user));
        }
    }
    
    public function exists($id_user, $id_space){
        $sql = "SELECT id FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function setDateEndContract($id_user, $id_space, $date_contract_end){
        if($date_contract_end == "") {
            $date_contract_end = null;
        }
        $sql = "UPDATE core_j_spaces_user SET date_contract_end=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($date_contract_end, $id_user, $id_space));
    }
    
    public function setDateConvention($id_user, $id_space, $date_convention){
        if($date_convention == "") {
            $date_convention = null;
        }
        $sql = "UPDATE core_j_spaces_user SET date_convention=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($date_convention, $id_user, $id_space));        
    }
    
    public function setConventionUrl($id_user, $id_space, $convention_url){
        $sql = "UPDATE core_j_spaces_user SET convention_url=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($convention_url, $id_user, $id_space)); 
    }
    
    public function getUserSpaceInfo($id_user){
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_user=?";
        return $this->runRequest($sql, array($id_user))->fetchAll();
    }
   
    /**
     * Get user specific space info and role
     */
    public function getUserSpaceInfo2($id_space, $id_user){
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_space=? AND id_user=?";
        return $this->runRequest($sql, array($id_space, $id_user))->fetch();
    }

    /**
     * Remove user from space
     * 
     * @param int $id_space
     * @param int $id_user
     * @param int $status optional status filter
     */
    public function delete($id_space, $id_user, $status=null){
        $count = 0;
        if($status != null) {
            $sql = "DELETE FROM core_j_spaces_user WHERE id_user=? AND id_space=? AND status=?";
            $pdo = $this->runRequest($sql, array($id_user, $id_space, $status));
            $count = $pdo->rowCount();
        } else {
            $sql = "DELETE FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
            $pdo = $this->runRequest($sql, array($id_user, $id_space));
            $count = $pdo->rowCount();
        }

        if($count > 0) {
            // Update eventually pending accounts status
            $modelSpacePending = new CorePendingAccount();
            $modelSpacePending->updateWhenUnjoin($id_user, $id_space);
            Events::send([
                "action" => Events::ACTION_SPACE_USER_UNJOIN,
                "space" => ["id" => intval($id_space)],
                "user" => ["id" => intval($id_user)]
            ]);
        }
    }

    /**
     * 
     * Fetch users for a space filtered by name and role
     * 
     * @param int $id_space
     * @param string $letter
     * @param int $active
     * 
     * @return array of users for the selected space 
     */

    public function getUsersOfSpaceByLetter($id_space, $letter, $active) {
        $letter = ($letter === "All") ? "" : $letter;
        $sql =
            "SELECT core_users.*,
                core_j_spaces_user.date_convention,
                core_j_spaces_user.date_contract_end,
                convention_url
            FROM core_users
            INNER JOIN core_j_spaces_user
            ON core_users.id = core_j_spaces_user.id_user
            WHERE core_j_spaces_user.id_space=?
            AND core_users.is_active=1
            AND core_users.validated=1";

        $sql .= ($active === 0)
            ? " AND core_j_spaces_user.status=0"
            : " AND core_j_spaces_user.status>0";

        if ($letter !== "") {
            $sql .= " AND name LIKE '" . $letter . "%'";
        }

        $sql .= " ORDER BY name ASC";

        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
}
