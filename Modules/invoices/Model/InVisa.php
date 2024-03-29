<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreUser.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class InVisa extends Model
{
    public function __construct()
    {
        $this->tableName = "in_visa";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `in_visa` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_space` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function mergeUsers($users)
    {
        for ($i = 1 ; $i < count($users) ; $i++) {
            $sql = "UPDATE in_visa SET id_user=? WHERE id_user=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }

    /**
     *
     * @param type $id
     * @param type $name
     */
    public function set($id, $id_user, $id_space)
    {
        if (!$id) {
            $sql = "INSERT INTO in_visa (id_user, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($id_user, $id_space));
        } else {
            $sql = "UPDATE in_visa SET id_user=? WHERE id_space=? AND id=?";
            $this->runRequest($sql, array($id_user, $id_space, $id));
        }
    }

    public function getIdFromUser($id_user, $id_space)
    {
        $sql = "SELECT id FROM in_visa WHERE id_user=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getAll($id_space)
    {
        $sql = "SELECT * FROM in_visa WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $modelUser = new CoreUser();
        for ($i = 0 ; $i < count($data) ; $i++) {
            $data[$i]["user_name"] = $modelUser->getUserFUllName($data[$i]['id_user']);
        }
        return $data;
    }

    public function get($id_space, $id)
    {
        $sql = "SELECT * FROM in_visa WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getForList($id_space)
    {
        $sql = "SELECT * FROM in_visa WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $ids = array();
        $names = array();
        $ids[] = 0;
        $names[] = "";
        $modelUser = new CoreUser();
        foreach ($data as $dat) {
            $ids[] = $dat['id'];
            $names[] = $modelUser->getUserInitiales($dat['id_user']);
        }
        return array('ids' => $ids, 'names' => $names);
    }

    public function getVisaName($id_space, $id)
    {
        $sql = "SELECT * FROM in_visa WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();
        if (!$data) {
            return null;
        }
        $modelUser = new CoreUser();
        return $modelUser->getUserFUllName($data["id_user"]);
    }

    public function getVisaNameShort($id_space, $id)
    {
        $sql = "SELECT * FROM in_visa WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();
        if (!$data) {
            return null;
        }
        $modelUser = new CoreUser();
        return $modelUser->getUserInitials($data["id_user"]);
    }


    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id_space, $id)
    {
        $sql = "UPDATE in_visa SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }
}
