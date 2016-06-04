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
		`tel` varchar(30) NOT NULL DEFAULT '',
		`id_unit` int(11) NOT NULL DEFAULT 1,	
		`date_convention` DATE NOT NULL DEFAULT '0000-00-00',
                `is_responsible` INT(1) NOT NULL DEFAULT 0,
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

}
