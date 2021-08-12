<?php

require_once 'Framework/Model.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

function cmpvisas($a, $b) {
    return strcmp($a["desc"], $b["desc"]);
}

/**
 * Class defining the Visa model
 *
 * @author Sylvain Prigent
 */
class ReVisa extends Model {

    /**
     * Create the table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `re_visas` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_resource_category` int(11) NOT NULL,
		`id_instructor` int(11) NOT NULL,
		`instructor_status` int(11) NOT NULL,
        `is_active` int(0) NOT NULL,
		PRIMARY KEY (`id`)
		);";

        $pdo = $this->runRequest($sql);
        $this->addColumn('re_visas', 'is_active', 'int(0)', 1);
        return $pdo;
    }

    public function getForListByCategory($id_space, $id_resource_category){
        $sql = "SELECT * FROM re_visas WHERE id_resource_category=? AND is_active=1 AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_resource_category, $id_space))->fetchAll();
        
        $names = array();
        $ids = array();
        foreach ($data as $d){
            $modelUser = new CoreUser();
            $names[] = $modelUser->getUserInitiales($d["id_instructor"]);
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    public function mergeUsers($users){
        for($i = 1 ; $i < count($users) ; $i++){
            $sql = "UPDATE re_visas SET id_instructor=? WHERE id_instructor=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }
    
    public function getIdFromInfo($id_space, $id_resource_category, $id_instructor){
        $sql = "SELECT id FROM re_visas WHERE id_resource_category=? AND id_instructor=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_resource_category, $id_instructor, $id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function setActive($id_space, $id, $active){
        $sql = "UPDATE re_visas SET is_active=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($active, $id, $id_space));
    }
    
    /**
     * Create the default empty Visa
     * 
     * @return PDOStatement
     */
    public function createDefaultVisa($id_space) {
        $sql = "insert into re_visas(id_resource_category, id_instructor, instructor_status, id_space)"
                . " values(?,?,?, ?)";
        $this->runRequest($sql, array(0, 1, 1, $id_space));
    }
    
    public function importVisa($id_space, $id, $id_cat, $id_instructor, $instructor_status){
        $sql = "insert into re_visas(id, id_resource_category, id_instructor, instructor_status, id_space)"
                . " values(?,?,?,?,?)";
        $this->runRequest($sql, array($id, $id_cat, $id_instructor, $instructor_status, $id_space));
    }

    public function getSpaceInstructors($id_space){
        $sql = "SELECT DISTINCT id_instructor FROM re_visas WHERE deleted=0 AND id_resource_category IN (SELECT DISTINCT id FROM re_category WHERE id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function getForSpace($id_space){
        $sql = "SELECT * FROM re_visas WHERE deleted=0 AND id_resource_category IN (SELECT DISTINCT id FROM re_category WHERE id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll(); 
    }
    
    /**
     * get visas informations
     * 
     * @param string $sortentry Entry that is used to sort the visas
     * @return multitype: array
     */
    public function getVisas($sortentry = 'id') {

        $sql = "select * from re_visas order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }
    
    public function getVisasBySpace($id_space, $sortentry = 'id') {
    
        $sql = "SELECT * FROM re_visas WHERE deleted=0 AND id_resource_category IN (SELECT id FROM re_category WHERE id_space=? AND delelted=0) order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }
    
    public function getActiveBySpace($id_space, $sortentry = 'id'){
        $sql = "SELECT * FROM re_visas WHERE deleted=0 AND id_resource_category IN (SELECT id FROM re_category WHERE id_space=? AND deleted=0) AND is_active=1 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * add a visa to the table
     *
     * @param string $name name of the visa
     */
    public function addVisa($id_space, $id_resource_category, $id_instructor, $instructor_status) {

        $sql = "insert into re_visas(id_resource_category, id_instructor, instructor_status, id_space)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($id_resource_category, $id_instructor, $instructor_status, $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * update the information of a visa
     *
     * @param int $id Id of the unit to update
     * @param string $name New name of the unit
     */
    public function editVisa($id_space, $id, $id_resource_category, $id_instructor, $instructor_status) {

        $sql = "UPDATE re_visas set id_resource_category=?, id_instructor=?, instructor_status=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_resource_category, $id_instructor, $instructor_status, $id, $id_space));
    }

    public function setVisas($id_space, $id, $id_resource_category, $id_instructor, $instructor_status){
        if ($id > 0){
            $this->editVisa($id_space, $id, $id_resource_category, $id_instructor, $instructor_status);
            return $id;
        }
        else{
            return $this->addVisa($id_space, $id_resource_category, $id_instructor, $instructor_status);
        }
    }
    /**
     * get the informations of a visa
     *
     * @param int $id Id of the unit to query
     * @throws Exception id the unit is not found
     * @return mixed array
     */
    public function getVisa($id_space, $id) {
        $sql = "SELECT * from re_visas where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();  // get the first line of the result
        } else {
            throw new Exception("Cannot find the visa using the given id");
        }
    }

    public function getVisaFromResourceAndInstructor($id_space, $resource_id, $id_AF) {
        $sql = "SELECT * from re_visas where id_resource_category=? and id_instructor=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($resource_id, $id_AF, $id_space));
        if ($unit->rowCount() == 1) {
            $val = $unit->fetch();
            return $val[0];  // get the first line of the result
        } else{
            return 0;
        }
    }

    public function getVisaShortDescription($id_space, $id, $lang) {
        $sql = "SELECT * from re_visas where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            $visaInfo = $req->fetch();  // get the first line of the result

            $modelUser = new CoreUser();
            $instructor = $modelUser->getUserInitiales($visaInfo["id_instructor"]);

            return $instructor;
        } else{
            return "";
        }
    }

    public function getVisaDescription($id_space, $id, $lang) {
        $sql = "SELECT * from re_visas where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            $visaInfo = $req->fetch();  // get the first line of the result

            return $this->getVisaDesc($visaInfo, $lang);
        } else{
            return "";
        }
    }

    private function getVisaDesc($id_space, $visaInfo, $lang) {
        $modelUser = new CoreUser();
        $instructor = $modelUser->getUserFUllName($visaInfo["id_instructor"]);

        $modelResourceCat = new ReCategory();
        $resourceName = $modelResourceCat->getName($id_space, $visaInfo["id_resource_category"]);

        $instructorStatus = ResourcesTranslator::Instructor($lang);
        if ($visaInfo["instructor_status"] == 2) {
            $instructorStatus = ResourcesTranslator::Responsible($lang);
        }

        return $instructor . " - " . $instructorStatus . " - " . $resourceName;
    }

    public function getVisasDesc($id_space, $id_resource, $lang) {
        $sql = "SELECT * from re_visas where id_resource_category=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_resource, $id_space));
        $visasInfo = $req->fetchAll();  // get the first line of the result

        $visas = array();
        foreach ($visasInfo as $visaInfo) {
            $v["id"] = $visaInfo["id"];
            $v["desc"] = $this->getVisaDesc($id_space, $visaInfo, $lang);
            $visas[] = $v;
        }

        usort($visas, "cmpvisas");

        return $visas;
    }

    public function getAllVisasDesc($id_space, $lang) {
        $sql = "SELECT * from re_visas WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_space));
        $visasInfo = $req->fetchAll();  // get the first line of the result

        $visas = array();
        foreach ($visasInfo as $visaInfo) {
            $v["id"] = $visaInfo["id"];
            $v["desc"] = $this->getVisaDesc($id_space, $visaInfo, $lang);
            $visas[] = $v;
        }

        usort($visas, "cmpvisas");

        return $visas;
    }

    /**
     * Remove a visa
     * @param number $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE re_visas SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    public function getAllInstructors($id_space) {
        $sql = "select distinct id_instructor from re_visas WHERE delete=0 AND id_resource_category IN (SELECT id FROM re_category WHERE id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_space));
        $instructors = $req->fetchAll();

        $modelUser = new CoreUser();

        for ($i = 0; $i < count($instructors); $i++) {
            $instructors[$i]["name_instructor"] = $modelUser->getUserFUllName($instructors[$i]["id_instructor"]);
        }

        return $instructors;
    }

}
