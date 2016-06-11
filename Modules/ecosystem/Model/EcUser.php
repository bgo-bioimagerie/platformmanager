<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';

/**
 * Class defining the User model
 *
 * @author Sylvain Prigent
 */
class EcUser extends Model {

    /**
     * Create the user table
     *
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `ec_users` (
		`id` int(11) NOT NULL,
		`phone` varchar(30) NOT NULL DEFAULT '',
		`id_unit` int(11) NOT NULL DEFAULT 1,	
		`date_convention` DATE NOT NULL DEFAULT '0000-00-00',
                `is_responsible` INT(1) NOT NULL DEFAULT 0,
                `convention_url` TEXT(255) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function importCoreUsers() {
        $sql1 = "SELECT * FROM core_users WHERE id NOT IN (SELECT id FROM ec_users)";
        $users = $this->runRequest($sql1)->fetchAll();
        foreach ($users as $user) {
            $sql = "INSERT INTO ec_users (id) VALUES(?)";
            $this->runRequest($sql, array($user["id"]));
        }
    }

    public function add($name, $firstname, $login, $pwd, $email, $phone, $unit, $is_responsible, $status_id, $date_convention, $date_end_contract) {
        $model = new CoreUser();
        $id = $model->add($login, $pwd, $name, $firstname, $email, $status_id, $date_end_contract, 1);

        $sql = "INSERT INTO ec_users (id, phone, id_unit, is_responsible, date_convention) VALUES (?,?,?,?,?)";
        $this->runRequest($sql, array($id, $phone, $unit, $is_responsible, $date_convention));
        return $id;
    }
    
    public function edit($id, $name, $firstname, $login, $email, $phone, $unit, $is_responsible, $id_status, $date_convention, $date_end_contract, $is_active){
        
        $modelUser = new CoreUser();
        $modelUser->edit($id, $login, $name, $firstname, $email, $id_status, $date_end_contract, $is_active);
        
        $sql = "UPDATE ec_users SET phone=?, id_unit=?, is_responsible=?, date_convention=? WHERE id=?";
        $this->runRequest($sql, array($phone, $unit, $is_responsible, $date_convention, $id));
    }

    public function getDefault() {
        return array("id" => 0,
            "login" => "",
            "name" => "",
            "firstname" => "",
            "email" => "",
            "status_id" => 0,
            "source" => "local",
            "is_active" => 1,
            "date_created" => "",
            "date_end_contract" => "",
            "phone" => '',
            "id_unit" => 1,
            "date_convention" => "",
            "is_responsible" => 0,
            "convention_url" => "",
            "id_resps" => array());
    }

    public function getInfo($id){
        $sql = "SELECT core_users.*, ec_users.* "
                . "FROM core_users "
                . "INNER JOIN ec_users ON core_users.id = ec_users.id "
                . "WHERE core_users.id=?";
        $userInfo = $this->runRequest($sql, array($id))->fetch();
        
        $userInfo["id_resps"] = $this->getUserResponsibles($id);
        
        return $userInfo;
    }
    
    public function setConventionUrl($id_user, $url) {
        $sql = "UPDATE ec_users SET convention_url=? WHERE id=?";
        $this->runRequest($sql, array($url, $id_user));
    }

    public function getActiveUsersInfo($active) {
        $sql = "SELECT core.*, ec.*, ecunit.name as unit, corestatus.name as status "
                . "FROM ec_users as ec "
                . "INNER JOIN core_users as core ON ec.id = core.id "
                . "INNER JOIN ec_units as ecunit ON ec.id_unit = ecunit.id "
                . "INNER JOIN core_status as corestatus ON core.status_id = corestatus.id "
                . "WHERE core.is_active=?";
        return $this->runRequest($sql, array($active))->fetchAll();
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

    public function delete($id) {
        $sql = "DELETE FROM ec_users WHERE id=?";
        $this->runRequest($sql, array($id));

        $sql1 = "DELETE FROM core_users WHERE id=?";
        $this->runRequest($sql1, array($id));
    }

}
