<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreSpaceUser extends Model
{
    /**
     * Create the status table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
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

    public function managersOrAdmin($idSpace)
    {
        $sql = "SELECT * from core_j_spaces_user WHERE id_space=? AND status>".CoreSpace::$USER;
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function admins()
    {
        $sql = 'SELECT * from core_users
            INNER JOIN core_j_spaces_user
            ON core_users.id = core_j_spaces_user.id_user
            WHERE core_j_spaces_user.status='.CoreSpace::$ADMIN;
        return $this->runRequest($sql)->fetchAll();
    }

    public function setRole($idUser, $idSpace, $role)
    {
        if (!$this->exists($idUser, $idSpace)) {
            $sql = "INSERT INTO core_j_spaces_user (id_user, id_space, status) VALUES (?,?,?)";
            $this->runRequest($sql, array($idUser, $idSpace, $role));
            Events::send([
                "action" => Events::ACTION_SPACE_USER_JOIN,
                "space" => ["id" => intval($idSpace)],
                "user" => ["id" => intval($idUser)]
            ]);
        } else {
            $sql = "UPDATE core_j_spaces_user SET status=? WHERE id_user=? AND id_space=?";
            $this->runRequest($sql, array($role, $idUser, $idSpace));
            Events::send([
                "action" => Events::ACTION_SPACE_USER_ROLEUPDATE,
                "space" => ["id" => intval($idSpace)],
                "user" => ["id" => intval($idUser)],
                "role" => $role
            ]);
        }

        if ($role > 0) {
            $sql = "UPDATE core_users SET is_active=? where id=?";
            $this->runRequest($sql, array(1, $idUser));
        }
    }

    public function exists($idUser, $idSpace)
    {
        $sql = "SELECT id FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
        $req = $this->runRequest($sql, array($idUser, $idSpace));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function setDateEndContract($idUser, $idSpace, $date_contract_end)
    {
        if ($date_contract_end == "") {
            $date_contract_end = null;
        }
        $sql = "UPDATE core_j_spaces_user SET date_contract_end=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($date_contract_end, $idUser, $idSpace));
    }

    public function setDateConvention($idUser, $idSpace, $date_convention)
    {
        if ($date_convention == "") {
            $date_convention = null;
        }
        $sql = "UPDATE core_j_spaces_user SET date_convention=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($date_convention, $idUser, $idSpace));
    }

    public function setConventionUrl($idUser, $idSpace, $convention_url)
    {
        $sql = "UPDATE core_j_spaces_user SET convention_url=? WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($convention_url, $idUser, $idSpace));
    }

    public function getUserSpaceInfo($idUser)
    {
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_user=?";
        return $this->runRequest($sql, array($idUser))->fetchAll();
    }

    /**
     * Get user specific space info and role
     */
    public function getUserSpaceInfo2($idSpace, $idUser)
    {
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_space=? AND id_user=?";
        return $this->runRequest($sql, array($idSpace, $idUser))->fetch();
    }

    /**
     * Remove user from space
     *
     * @param int $idSpace
     * @param int $idUser
     * @param int $status optional status filter
     */
    public function delete($idSpace, $idUser, $status=null)
    {
        $sql = "SELECT status FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
        $res = $this->runRequest($sql, array($idUser, $idSpace));
        $role = 0;
        if ($res->rowCount() == 1) {
            $obj = $res->fetch();
            $role = $obj['status'];
        }

        $count = 0;
        if ($status != null) {
            $sql = "DELETE FROM core_j_spaces_user WHERE id_user=? AND id_space=? AND status=?";
            $pdo = $this->runRequest($sql, array($idUser, $idSpace, $status));
            $count = $pdo->rowCount();
        } else {
            $sql = "DELETE FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
            $pdo = $this->runRequest($sql, array($idUser, $idSpace));
            $count = $pdo->rowCount();
        }

        if ($count > 0) {
            // Update eventually pending accounts status
            $modelSpacePending = new CorePendingAccount();
            $modelSpacePending->updateWhenUnjoin($idUser, $idSpace);
            Events::send([
                "action" => Events::ACTION_SPACE_USER_UNJOIN,
                "space" => ["id" => intval($idSpace)],
                "user" => ["id" => intval($idUser)],
                "role" => $role
            ]);
        }
    }

    /**
     *
     * Fetch users for a space filtered by name and role
     *
     * @param int $idSpace
     * @param string $letter
     * @param int $active
     *
     * @return array of users for the selected space
     */

    public function getUsersOfSpaceByLetter($idSpace, $letter, $active)
    {
        $letter = ($letter === "All") ? "" : $letter;
        $sql =
            "SELECT core_users.*,
                users_info.unit,
                users_info.organization,
                core_j_spaces_user.date_convention,
                core_j_spaces_user.date_contract_end,
                convention_url
            FROM core_users
            INNER JOIN core_j_spaces_user
            ON core_users.id = core_j_spaces_user.id_user
            LEFT JOIN users_info
            ON users_info.id_core = core_users.id
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

        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }
}
