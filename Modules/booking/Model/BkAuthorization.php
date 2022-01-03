<?php

require_once 'Framework/Model.php';
require_once 'Modules/resources/Model/ReResps.php';

/**
 * Class defining the Authorization model
 *
 * @author Sylvain Prigent
 */
class BkAuthorization extends Model {

    /**
     * Create the authorization table
     *
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "bk_authorization";
        //$this->setColumnsInfo("id", "int(11)", "");
        //$this->setColumnsInfo("user_id", "int(11)", 0);
        //$this->setColumnsInfo("resource_id", "int(11)", 0);
        //$this->setColumnsInfo("visa_id", "int(11)", 0);
        //$this->setColumnsInfo("date", "date", "");
        //$this->setColumnsInfo("date_desactivation", "date", "");
        //$this->setColumnsInfo("is_active", "int(1)", 1);
        //$this->primaryKey = "id";

    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_authorization` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `user_id` int NOT NULL DEFAULT '0',
            `resource_id` int NOT NULL DEFAULT '0',
            `visa_id` int NOT NULL DEFAULT '0',
            `date` date DEFAULT NULL,
            `date_desactivation` date DEFAULT NULL,
            `is_active` int NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
        );";
    
        $this->runRequest($sql);
    }

    public function mergeUsers($users){
        for($i = 1 ; $i < count($users) ; $i++){
            $sql = "UPDATE bk_authorization SET user_id=? WHERE user_id=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }

    public function getForResourceAndUser($id_space, $id_resource_category, $id_user){
        $sql = "SELECT * FROM bk_authorization WHERE resource_id=? AND user_id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_resource_category, $id_user, $id_space))->fetchAll();
    }

    public function add($id_space, $user_id, $resource_id, $visa_id, $date){
        if($date == "") {
            $date = null;
        }
        $sql = "INSERT INTO bk_authorization (user_id, resource_id, visa_id, date, is_active, id_space) VALUES (?,?,?,?,?,?)";
        $this->runRequest($sql, array($user_id, $resource_id, $visa_id, $date, 1, $id_space));
    }

    public function set($id_space, $id, $user_id, $resource_id, $visa_id, $date, $date_desactivation, $is_active){
        if($date == "") {
            $date = null;
        }
        if($date_desactivation == "") {
            $date = null;
        }
        $sql = "UPDATE bk_authorization SET user_id=?, resource_id=?, visa_id=?, date=?, date_desactivation=?, is_active=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($user_id, $resource_id, $visa_id, $date, $date_desactivation, $is_active, $id, $id_space));
    }

    /**
     * Set an authorization status
     * @param number $id ID of the authorization
     * @param number $active Active status
     */
    public function setActive($id_space, $id, $active) {
        $sql = "update bk_authorization set is_active=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array(
            $active,
            $id,
            $id_space
        ));
    }

    /**
     * Set an authorization unactive
     * @param number $id ID of the authorization
     */
    public function unactivate($id_space, $id) {
        $sql = "update bk_authorization set is_active=0 where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array(
            $id,
            $id_space
        ));
    }

    /**
     * Set an authorization active
     * @param number $id ID of the authorization
     */
    public function activate($id_space, $id) {
        $sql = "update bk_authorization set is_active=1 where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array(
            $id,
            $id_space
        ));
    }

    public function get($id_space, $id) {
        $sql = "SELECT * from bk_authorization where id=? AND id_space=? AND deleted=0";
        $auth = $this->runRequest($sql, array($id, $id_space));
        return $auth->fetch();
    }

    /**
     * Check if a user have an authorization for a given resource
     * @param number $id_resource ID of the resource
     * @param unknown $id_user ID of the user
     * @return boolean
     */
    public function hasAuthorization($id_space, $id_resource, $id_user) {
        $sql = "SELECT id from bk_authorization where user_id=? AND resource_id=? AND is_active=1 AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_user, $id_resource, $id_space));
        return ($data->rowCount() >= 1);
    }

    public function getLastActiveAuthorization($id_space, $id_resource, $id_user){
        $sql = "SELECT * from bk_authorization where user_id=? AND resource_id=? AND is_active=1 AND id_space=? AND deleted=0 ORDER BY date DESC;";
        $data = $this->runRequest($sql, array($id_user, $id_resource, $id_space));
        if ($data->rowCount() >= 1){
            return $data->fetch();
        }
        else{
            return array();
        }
    }

    public function getTotalForPeriod($id_space, $period_begin, $period_end){
        $sql = 'SELECT * FROM bk_authorization WHERE deleted=0 AND id_space=? AND date>=? AND date<=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0)';
        $req = $this->runRequest($sql, array($id_space, $period_begin, $period_end, $id_space));
        return $req->rowCount();
    }

    public function getDistinctUserForPeriod($id_space, $period_begin, $period_end){
        $sql = 'SELECT DISTINCT user_id FROM bk_authorization WHERE deleted=0 AND id_space=? AND date>=? AND date<=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0 )';
        $req = $this->runRequest($sql, array($id_space, $period_begin, $period_end, $id_space));
        return $req->rowCount();
    }

    public function getDistinctUnitForPeriod($id_space, $period_begin, $period_end){
        $sql = 'SELECT DISTINCT lab_id FROM bk_authorization WHERE deleted=0 AND id_space=? AND date>=? AND date<=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0 )';
        $req = $this->runRequest($sql, array($id_space, $period_begin, $period_end, $id_space));
        return $req->rowCount();
    }

    public function getDistinctVisaForPeriod($id_space, $period_begin, $period_end){
        $sql = 'SELECT DISTINCT visa_id FROM bk_authorization WHERE deleted=0 AND id_space=? AND date>=? AND date<=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0 )';
        $req = $this->runRequest($sql, array($id_space, $period_begin, $period_end, $id_space));
        return $req->rowCount();
    }

    public function getDistinctResourceForPeriod($id_space, $period_begin, $period_end){
        $sql = 'SELECT DISTINCT resource_id FROM bk_authorization WHERE deleted=0 AND id_space=? AND date>=? AND date<=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0 )';
        $req = $this->runRequest($sql, array($id_space, $period_begin, $period_end, $id_space));
        return $req->rowCount();
    }

    public function getNewPeopleForPeriod($id_space, $period_begin, $period_end){
        $sql_search_1 = 'SELECT DISTINCT user_id FROM bk_authorization WHERE deleted=0 AND id_space=? AND date >=? AND date <=? AND resource_id IN ( SELECT id FROM re_category WHERE id_space=? AND deleted=0 ) ORDER BY date';
        $req = $this->runRequest($sql_search_1, array($id_space, $period_begin, $period_end, $id_space));
        $res_distinct_nf = $req->fetchAll();
        $new_people = 0;
        foreach ($res_distinct_nf as $rDN) {
            $nf = $rDN[0];
            $q = array('start' => $period_begin, 'user_id' => $nf, 'id_space' => $id_space);
            $sql = 'SELECT id FROM bk_authorization WHERE user_id=:user_id AND date<:start AND deleted=0 AND id_space=:id_space ORDER BY date';
            $req = $this->runRequest($sql, $q);
            $num = $req->rowCount();
            if ($num == 0) {
                $new_people++;
            }
        }
        return $new_people;
    }

    public function getForResourceInstructorPeriod($id_space, $resource_id, $instructor_id, $period_begin, $period_end){
        $sql = "SELECT * FROM bk_authorization WHERE deleted=0 AND id_space=? AND resource_id=? AND date>=? AND date<=? AND visa_id IN (SELECT id FROM re_visas WHERE id_instructor=? AND id_space=? AND deleted=0) ";
        $req = $this->runRequest($sql, array($id_space, $resource_id, $period_begin, $period_end, $instructor_id, $id_space));
        return $req->fetchAll();
    }

    // unit = client
    public function getFormResourceUnitPeriod($id_space, $resource_id, $unit_id, $period_begin, $period_end){

        $sql = "SELECT * FROM bk_authorization WHERE deleted=0 AND id_space=? AND resource_id=? AND date>=? AND date<=? AND user_id IN (SELECT id_user from cl_j_client_user WHERE id_client=? AND id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_space, $resource_id, $period_begin, $period_end, $unit_id, $id_space));
        return $req->fetchAll();
    }

    public function getActiveAuthorizationSummaryForResourceCategory($id_space, $resource_id, $lang) {

        $sql = "SELECT DISTINCT core_users.name, auth.visa_id, auth.date, core_users.firstname, core_users.email, core_users.id as user_id " .
                "FROM bk_authorization AS auth " .
                "INNER JOIN core_users ON auth.user_id=core_users.id " .
                "WHERE auth.resource_id=? AND auth.is_active=1 AND auth.id_space=? AND auth.deleted=0 "
                . " ORDER BY core_users.name ASC;";

        $req = $this->runRequest($sql, array($resource_id, $id_space));
        $auth = $req->fetchAll();

        $modelVisa = new ReVisa();
        $modelClient = new ClClientUser();
        for ($i = 0; $i < count($auth); $i++) {
            $auth[$i]["visa"] = $modelVisa->getVisaShortDescription($id_space, $auth[$i]["visa_id"], $lang);
            $uc = $modelClient->getUserClientAccounts($auth[$i]["user_id"], $id_space);
            $auth[$i]["unitName"] = "";
            if($uc && !empty($uc)) {
                $auth[$i]["unitName"] = $uc[0]["name"];
            }
        }
        return $auth;
    }

    /**
     * Get all the active authorizations for a given resource
     * @param number $resource_id
     * @return multitype: Authorizations informations
     * 
     *  @bug osallou: refer to ec_units, get unit name from user
     * TODO: to be tested
     */
    public function getActiveAuthorizationForResourceCategory($id_space, $resource_id) {
        $sql = "SELECT bk_authorization.id, bk_authorization.date, core_users.id AS user_id, core_users.name AS userName, core_users.firstname AS userFirstname, core_users.email AS userEmail, se_visa.name AS visa, re_category.name AS resource
					from bk_authorization
					     INNER JOIN core_users on bk_authorization.user_id = core_users.id
					     INNER JOIN se_visa on bk_authorization.visa_id = se_visa.id
					     INNER JOIN re_category on bk_authorization.resource_id = re_category.id
				WHERE bk_authorization.resource_id=? AND bk_authorization.is_active=1 AND bk_authorization.deleted=0 AND bk_authorization.id_space=?
				ORDER BY core_users.name;";
        $req = $this->runRequest($sql, array($resource_id, $id_space));
        $auth = $req->fetchAll();

        $modelClient = new ClClientUser();
        for ($i = 0; $i < count($auth); $i++) {
            $uc = $modelClient->getUserClientAccounts($auth[$i]["user_id"], $id_space);
            $auth[$i]["unitName"] = "";
            if($uc && !empty($uc)) {
                $auth[$i]["unitName"] = $uc[0]["name"];
            }
        }

        return $auth;

    }

    /**
     * Remove a visa
     * @param number $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_authorization set deleted=1,deleted_at=NOW() WHERE id = ? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
