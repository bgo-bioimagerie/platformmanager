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

        $sql1 = "CREATE TABLE IF NOT EXISTS `ec_j_user_responsible` (
		`id_user` int(11) NOT NULL,
		`id_resp` int(11) NOT NULL
		);
		";
        $this->runRequest($sql1);
    }

    
        /**
     * GEt the responsible of a given user
     * @param number $id User id
     * @return number Responsible ID
     */
    public function getUserResponsibles($id) {

        $sql = "SELECT id_resp FROM ec_j_user_responsible WHERE id_user = ?";
        $req = $this->runRequest($sql, array($id));
        $userr = $req->fetchAll();

        for ($i = 0; $i < count($userr); $i++) {
            $userr[$i]["id"] = $userr[$i]["id_resp"];
            $userr[$i]["fullname"] = $this->getUserFUllName($userr[$i]["id_resp"]);
        }
        return $userr;
    }
    
        /**
     * get the firstname and name of a user from it's id
     *
     * @param int $id
     *        	Id of the user to get
     * @throws Exception
     * @return string "firstname name"
     */
    public function getUserFUllName($id) {
        $sql = "select firstname, name from core_users where id=?";
        $user = $this->runRequest($sql, array(
            $id
                ));

        if ($user->rowCount() == 1) {
            $userf = $user->fetch();
            return $userf ['name'] . " " . $userf ['firstname'];
        } else {
            return "";
        }
    }
    
    /**
     * Get the names and firstname of the responsible users 
     * 
     * @return multitype: array of the responsible users
     */
    public function responsiblesNames() {
        $sql = "SELECT core.firstname, core.name "
                . " FROM ec_users"
                . " INNER JOIN core_users as core ON ec_users.id = core.id "
                . " WHERE ec_users.is_responsible=1";
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
        $sql = "SELECT id FROM ec_users WHERE is_responsible=1";
        $respPDO = $this->runRequest($sql);
        $resps = $respPDO->fetchAll();

        return $resps;
    }

    /**
     * get the id, firstname and name of the responsibles users 
     * 
     * @return multitype: 2D array containing the users informations 
     */
    public function responsibleSummaries($sortentry = "name") {
        $sql = "SELECT core_users.id, core_users.firstname, core_users.name "
                . "FROM core_users "
                . "INNER JOIN ec_users ON core_users.id = ec_users.id "
                . "WHERE ec_users.is_responsible=1 ORDER BY " . $sortentry;
        $respPDO = $this->runRequest($sql);
        $resps = $respPDO->fetchAll();

        return $resps;
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
    
    public function import($idUser, $idResp){
        if(!$this->isUserRespJoin($idUser, $idResp)){
            $this->addUserRespJoin($idUser, $idResp);
        }
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
    
    public function setResponsibles($id_user, $responsibles){
        $this->removeAllUserRespJoin($id_user);
        foreach($responsibles as $resp){
            $this->addUserRespJoin($id_user, $resp);
        }
    }

}
