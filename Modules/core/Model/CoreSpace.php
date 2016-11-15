<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreSpace extends Model {

    public static $VISITOR = 1;
    public static $USER = 2;
    public static $MANAGER = 3;
    public static $ADMIN = 4;

    public static function roles($lang) {

        $names = array(CoreTranslator::Visitor($lang), CoreTranslator::User($lang),
            CoreTranslator::Manager($lang), CoreTranslator::Admin($lang));
        $ids = array(1, 2, 3, 4);
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_spaces` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(30) NOT NULL DEFAULT '',
                `status` int(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);

        $sql2 = "CREATE TABLE IF NOT EXISTS `core_j_spaces_user` (
		`id_user` int(11) NOT NULL DEFAULT 1,
		`id_space` int(11) NOT NULL DEFAULT 1,
                `status` int(1) NOT NULL DEFAULT 1
		);";
        $this->runRequest($sql2);

        // name = module
        $sql3 = "CREATE TABLE IF NOT EXISTS `core_space_menus` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_space` int(1) NOT NULL DEFAULT 1,
		`module` varchar(60) NOT NULL DEFAULT '',
                `url` varchar(120) NOT NULL DEFAULT '',
                `icon` varchar(120) NOT NULL DEFAULT '',
                `user_role` int(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql3);
    }

    public function setSpaceMenu($id_space, $module, $url, $icon, $user_role) {
        if ($this->isSpaceMenu($id_space, $url)) {
            $sql = "UPDATE core_space_menus SET module=?, icon=?, user_role=? WHERE id_space=? AND url=?";
            $this->runRequest($sql, array($module, $icon, $user_role, $id_space, $url));
        } else {
            $sql = "INSERT INTO core_space_menus (id_space, module, url, icon, user_role) VALUES(?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $module, $url, $icon, $user_role));
        }
    }

    public function getSpaceMenusRole($id_space, $url) {
        $sql = "SELECT user_role FROM core_space_menus WHERE id_space=? AND url=?";
        $req = $this->runRequest($sql, array($id_space, $url))->fetch();
        return $req[0];
    }

    public function isSpaceMenu($id_space, $url) {
        $sql = "SELECT id FROM core_space_menus WHERE id_space=? AND url=?";
        $req = $this->runRequest($sql, array($id_space, $url));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getSpaceMenus($id_space, $user_role) {
        $sql = "SELECT * FROM core_space_menus WHERE id_space=? AND user_role<=?";
        return $this->runRequest($sql, array($id_space, $user_role))->fetchAll();
    }
    
    public function getAllSpaceMenusModules($id_space){
        $sql = "SELECT DISTINCT module FROM core_space_menus WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getAllSpaceMenus($id_space){
        $sql = "SELECT * FROM core_space_menus WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getUserSpaceRole($id_space, $id_user) {
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_space=? AND id_user=?";
        $req = $this->runRequest($sql, array($id_space, $id_user));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp["status"];
        } else {
            if ($this->isSpacePublic($id_space)) {
                return CoreSpace::$USER;
            }
            return 0;
        }
    }

    public function isUserMenuSpaceAuthorized($menuUrl, $id_space, $id_user) {
        // is menu public
        $sql = "SELECT user_role FROM core_space_menus WHERE url=? AND id_space=?";
        $roleArrray = $this->runRequest($sql, array($menuUrl, $id_space))->fetch();
        $menuRole = $roleArrray[0];

        if ($this->isSpacePublic($id_space)) {
            if ($menuRole < CoreSpace::$MANAGER) {
                return 1;
            } else {
                $userRole = $this->getUserSpaceRole($id_space, $id_user);
                if ($userRole >= $menuRole) {
                    return 1;
                }
                return 0;
            }
        } else {
            $userRole = $this->getUserSpaceRole($id_space, $id_user);
            if ($userRole >= $menuRole) {
                return 1;
            }
            return 0;
        }

    }

    public function isSpacePublic($id_space) {
        $sql = "SELECT status FROM core_spaces WHERE id=?";
        $req = $this->runRequest($sql, array($id_space))->fetch();
        return $req[0];
    }

    public function getSpace($id) {
        $sql = "SELECT * FROM core_spaces WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getSpaces($sort) {
        $sql = "SELECT * FROM core_spaces ORDER BY " . $sort . " ASC;";
        return $this->runRequest($sql)->fetchAll();
    }

    public function setSpace($id, $name, $status) {
        if ($this->isSpace($id)) {
            $this->editSpace($id, $name, $status);
            return $id;
        } else {
            $this->addSpace($name, $status);
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function isSpace($id) {
        $sql = "SELECT id FROM core_spaces WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function spaceAdmins($id) {
        $sql = "SELECT id_user FROM core_j_spaces_user WHERE id_space=? AND status=4";
        $data = $this->runRequest($sql, array($id))->fetchAll();
        $users = array();
        foreach ($data as $d) {
            $users[] = $d["id_user"];
        }
        return $users;
    }

    public function addSpace($name, $status) {
        $sql = "INSERT INTO core_spaces (name, status) VALUES (?,?)";
        $this->runRequest($sql, array($name, $status));
    }

    public function editSpace($id, $name, $status) {
        $sql = "UPDATE core_spaces SET name=?, status=? WHERE id=?";
        $this->runRequest($sql, array($name, $status, $id));
    }

    public function setUser($id_user, $id_space, $status) {
        if ($this->isUser($id_user, $id_space)) {
            $sql = "UPDATE core_j_spaces_user SET status=? WHERE id_user=? AND id_space=?";
            $this->runRequest($sql, array($status, $id_user, $id_space));
        } else {
            $sql = "INSERT INTO core_j_spaces_user (id_user, id_space, status) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_user, $id_space, $status));
        }
    }

    public function isUser($id_user, $id_space) {
        $sql = "SELECT id_user FROM core_j_spaces_user WHERE id_user=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getUsers($id_space) {
        $sql = "SELECT core_j_spaces_user.id_user AS id, core_j_spaces_user.status AS role, "
                . "core_users.name AS name, core_users.firstname AS firstname "
                . "FROM core_j_spaces_user "
                . "INNER JOIN core_users ON core_j_spaces_user.id_user = core_users.id "
                . "WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function setAdmins($id, $id_admins) {

        // remove existing admins
        $sql = "SELECT * FROM core_j_spaces_user WHERE id_space=? AND status=?";
        $alreadyAdmins = $this->runRequest($sql, array($id, CoreSpace::$ADMIN))->fetchAll();
        foreach ($alreadyAdmins as $aadm) {
            $found = false;
            foreach ($id_admins as $cidadm) {
                if ($cidadm == $aadm["id_user"]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sql = "DELETE FROM core_j_spaces_user WHERE id_space=? AND id_user=? AND status=?";
                $this->runRequest($sql, array($id, $aadm["id_user"], CoreSpace::$ADMIN));
            }
        }

        // add admins
        foreach ($id_admins as $adm) {
            $this->setUser($adm, $id, CoreSpace::$ADMIN);
        }
    }

    public function deleteUser($id_space, $id_user) {
        $sql = "DELETE FROM core_j_spaces_user WHERE id_space=? AND id_user=?";
        $this->runRequest($sql, array($id_space, $id_user));
    }

    public function delete($id){
        $sql = "DELETE FROM core_spaces WHERE id=?";
        $this->runRequest($sql, array($id));
    }
}
