<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreUser.php';

/**
 * Class defining the Responsible model
 *
 * @author Sylvain Prigent
 */
class EcResponsible extends Model {

    /**
     * Create the Responsible table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ec_responsibles` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_users` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		);
		";
        $this->runRequest($sql);

        $sql1 = "CREATE TABLE IF NOT EXISTS `ec_j_user_responsible` (
		`id_user` int(11) NOT NULL,
		`id_resp` int(11) NOT NULL
		);
		";
        $this->runRequest($sql1);
    }

    /**
     * Add a user in the responsible table
     * 
     * @param int $id_user Id of the user to add in the responsible table
     */
    public function addResponsible($id_user) {

        // test if the user is already responsible
        $sql = "SELECT EXISTS(SELECT 1 FROM ec_responsibles WHERE id_users = ?)";

        $exists = $this->runRequest($sql, array($id_user));
        $out = $exists->fetch();

        if ($out[0] == 0) {
            $sql = "insert into ec_responsibles(id_users)"
                    . " values(?)";
            $this->runRequest($sql, array($id_user));
        }
    }

    /**
     * Remove a responsible from his ID
     * @param number $id_user User ID
     */
    public function removeResponsible($id_user) {
        // test if the user is already responsible
        $sql = "SELECT EXISTS(SELECT 1 FROM ec_responsibles WHERE id_users = ?)";

        $exists = $this->runRequest($sql, array($id_user));
        $out = $exists->fetch();


        if ($out[0] != 0) {
            $sql = "DELETE FROM ec_responsibles WHERE id_users = ?";
            $this->runRequest($sql, array($id_user));
        }
    }

    /**
     * Return true is a user is responsible
     * 
     * @param int $userId Id of the user test
     * @return boolean return true if the user is responsible false otherwise
     */
    public function isResponsible($userId) {
        $sql = "SELECT EXISTS(SELECT 1 FROM ec_responsibles WHERE id_users = ?)";

        $exists = $this->runRequest($sql, array($userId));
        $out = $exists->fetch();

        if ($out[0] == 0) {
            return false;
        }
        return true;
    }

    /**
     * Set a user responsible
     * @param unknown $id_user
     */
    public function setResponsible($id_user) {
        if (!$this->isResponsible($id_user)) {
            $this->addResponsible($id_user);
        }
    }

    /**
     * Get the names and firstname of the responsible users 
     * 
     * @return multitype: array of the responsible users
     */
    public function responsiblesNames() {
        $sql = "SELECT firstname, name FROM ec_users WHERE id IN (SELECT id_users FROM ec_responsibles)";
        $respPDO = $this->runRequest($sql);
        $resps = $respPDO->fetchAll();

        return $resps;
    }

    /**
     * Get the ids of the responsible users
     * 
     * @return multitype: array of the responsible users
     */
    public function responsiblesIds() {
        $sql = "SELECT id_users FROM ec_responsibles";
        $respPDO = $this->runRequest($sql);
        $resps = $respPDO->fetchAll();

        return $resps;
    }

    /**
     * return the name of a responsible user
     * 
     * @param int $id Id of the user to query
     * @return mixed array containing the firsname and the name of the responsible user
     */
    public function responsibleName($id) {
        $sql = "SELECT firstname, name FROM ec_users WHERE id=?";
        $respPDO = $this->runRequest($sql, array($id));
        $resp = $respPDO->fetch();
        /// @todo add a throw here
        return $resp;
    }

    /**
     * get the id, firstname and name of the responsibles users 
     * 
     * @return multitype: 2D array containing the users informations 
     */
    public function responsibleSummaries($sortentry = "name") {
        $sql = "SELECT id, firstname, name FROM ec_users WHERE id IN (SELECT id_users FROM ec_responsibles) ORDER BY " . $sortentry;
        $respPDO = $this->runRequest($sql);
        $resps = $respPDO->fetchAll();

        return $resps;
    }

    /**
     * Get the responsible of a given user
     * @param number $user_id ID of the user to get the responsible
     * @return number ID of the responsible
     */
    public function getUserResponsible($user_id) {
        $sql = "SELECT id_resp FROM ec_users WHERE id=?";
        $respPDO = $this->runRequest($sql, array($user_id));
        $tmp = $respPDO->fetch();
        $respID = $tmp[0];
        return $this->responsibleName($respID);
    }

    public function getResponsibleId($user_id) {
        $sql = "SELECT id_resp FROM ec_j_user_responsible WHERE id_user=?";
        $respPDO = $this->runRequest($sql, array($user_id));
        if ($respPDO->rowCount() > 0) {
            $tmp = $respPDO->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    /**
     * 
     * Test if a user is linked to a responsible
     * @param number $idUser User ID
     * @param number $idResp Responsible ID
     * @return boolean
     */
    public function isUserRespJoin($idUser, $idResp) {
        $sql = "SELECT EXISTS(SELECT 1 FROM ec_j_user_responsible WHERE id_user = ? AND id_resp = ? )";

        $exists = $this->runRequest($sql, array($idUser, $idResp));
        $out = $exists->fetch();

        if ($out[0] == 0) {
            return false;
        }
        return true;
    }

    /**
     * Remove a user/responsible join to the database
     * @param number $idUser User ID
     * @param number $idResp Responsible ID
     */
    public function removeUserRespJoin($idUser, $idResp) {
        $sql = "DELETE FROM ec_j_user_responsible WHERE id_user = ? AND id_resp = ?";
        $this->runRequest($sql, array($idUser, $idResp));
    }

    /**
     * Add a user/responsible join to the database
     * @param number $idUser User ID
     * @param number $idResp Responsible ID
     */
    public function addUserRespJoin($idUser, $idResp) {
        $sql = "INSERT INTO ec_j_user_responsible (id_user, id_resp) VALUES(?,?)";
        $pdo = $this->runRequest($sql, array($idUser, $idResp));
        return $pdo;
    }

    /**
     * Remove all the user/responsible join af a given user
     * @param number $idUser
     */
    public function removeAllUserRespJoin($idUser) {
        $sql = "DELETE FROM ec_j_user_responsible WHERE id_user = ?";
        $this->runRequest($sql, array($idUser));
    }

}
