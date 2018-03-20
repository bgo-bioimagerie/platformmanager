<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeVisa extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `se_visa` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_space` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function mergeUsers($users) {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "UPDATE se_visa SET id_user=? WHERE id_user=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }

    public function getIdFromUser($id_user, $id_space) {
        $sql = "SELECT id FROM se_visa WHERE id_user=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_user, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    /**
     * 
     * 
     * @param type $id
     * @param type $name
     */
    public function set($id, $id_user, $id_space) {
        if ($id == 0) {
            $sql = "INSERT INTO se_visa (id_user, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($id_user, $id_space));
        } else {
            $sql = "UPDATE se_visa SET id_user=?, id_space=? WHERE id=?";
            $this->runRequest($sql, array($id_user, $id_space, $id));
        }
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM se_visa WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $modelUser = new CoreUser();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["user_name"] = $modelUser->getUserFUllName($data[$i]['id_user']);
        }
        return $data;
    }

    public function get($id) {
        $sql = "SELECT * FROM se_visa WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM se_visa WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $ids = array();
        $names = array();
        $ids[] = "";
        $names[] = "";
        $modelUser = new CoreUser();
        foreach ($data as $dat) {
            $ids[] = $dat['id'];
            $names[] = $modelUser->getUserInitiales($dat['id_user']);
        }
        return array('ids' => $ids, 'names' => $names);
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id) {

        $sql = "DELETE FROM se_visa WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
