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
		PRIMARY KEY (`id`)
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Create the default empty Visa
     * 
     * @return PDOStatement
     */
    public function createDefaultVisa() {
        $sql = "insert into re_visas(id_resource_category, id_instructor, instructor_status)"
                . " values(?,?,?)";
        $this->runRequest($sql, array(0, 1, 1));
    }
    
    public function importVisa($id, $id_cat, $id_instructor, $instructor_status){
        $sql = "insert into re_visas(id, id_resource_category, id_instructor, instructor_status)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($id, $id_cat, $id_instructor, $instructor_status));
    }

    public function getSpaceInstructors($id_space){
        $sql = "SELECT DISTINCT id_instructor FROM re_visas WHERE id_resource_category IN (SELECT DISTINCT id FROM re_category WHERE id_space=?)";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function getForSpace($id_space){
        $sql = "SELECT * FROM re_visas WHERE id_resource_category IN (SELECT DISTINCT id FROM re_category WHERE id_space=?)";
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
    
        $sql = "SELECT * FROM re_visas WHERE id_resource_category IN (SELECT id FROM re_category WHERE id_space=?) order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * add a visa to the table
     *
     * @param string $name name of the visa
     */
    public function addVisa($id_resource_category, $id_instructor, $instructor_status) {

        $sql = "insert into re_visas(id_resource_category, id_instructor, instructor_status)"
                . " values(?,?,?)";
        $this->runRequest($sql, array($id_resource_category, $id_instructor, $instructor_status));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * update the information of a visa
     *
     * @param int $id Id of the unit to update
     * @param string $name New name of the unit
     */
    public function editVisa($id, $id_resource_category, $id_instructor, $instructor_status) {

        $sql = "update re_visas set id_resource_category=?, id_instructor=?, instructor_status=? where id=?";
        $this->runRequest($sql, array($id_resource_category, $id_instructor, $instructor_status, $id));
    }

    public function setVisas($id, $id_resource_category, $id_instructor, $instructor_status){
        if ($id > 0){
            $this->editVisa($id, $id_resource_category, $id_instructor, $instructor_status);
            return $id;
        }
        else{
            return $this->addVisa($id_resource_category, $id_instructor, $instructor_status);
        }
    }
    /**
     * get the informations of a visa
     *
     * @param int $id Id of the unit to query
     * @throws Exception id the unit is not found
     * @return mixed array
     */
    public function getVisa($id) {
        $sql = "select * from re_visas where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();  // get the first line of the result
        } else {
            throw new Exception("Cannot find the visa using the given id");
        }
    }

    public function getVisaFromResourceAndInstructor($resource_id, $id_AF) {
        $sql = "select * from re_visas where id_resource_category=? and id_instructor=?";
        $unit = $this->runRequest($sql, array($resource_id, $id_AF));
        if ($unit->rowCount() == 1) {
            $val = $unit->fetch();
            return $val[0];  // get the first line of the result
        } else{
            return 0;
        }
    }

    public function getVisaShortDescription($id, $lang) {
        $sql = "select * from re_visas where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $visaInfo = $req->fetch();  // get the first line of the result

            $modelUser = new CoreUser();
            $instructor = $modelUser->getUserInitiales($visaInfo["id_instructor"]);

            return $instructor;
        } else{
            return "";
        }
    }

    public function getVisaDescription($id, $lang) {
        $sql = "select * from re_visas where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $visaInfo = $req->fetch();  // get the first line of the result

            return $this->getVisaDesc($visaInfo, $lang);
        } else{
            return "";
        }
    }

    private function getVisaDesc($visaInfo, $lang) {
        $modelUser = new CoreUser();
        $instructor = $modelUser->getUserFUllName($visaInfo["id_instructor"]);

        $modelResourceCat = new ReCategory();
        $resourceName = $modelResourceCat->getName($visaInfo["id_resource_category"]);

        $instructorStatus = ResourcesTranslator::Instructor($lang);
        if ($visaInfo["instructor_status"] == 2) {
            $instructorStatus = ResourcesTranslator::Responsible($lang);
        }

        return $instructor . " - " . $instructorStatus . " - " . $resourceName;
    }

    public function getVisasDesc($id_resource, $lang) {
        $sql = "select * from re_visas where id_resource_category=?";
        $req = $this->runRequest($sql, array($id_resource));
        $visasInfo = $req->fetchAll();  // get the first line of the result

        $visas = array();
        foreach ($visasInfo as $visaInfo) {
            $v["id"] = $visaInfo["id"];
            $v["desc"] = $this->getVisaDesc($visaInfo, $lang);
            $visas[] = $v;
        }

        usort($visas, "cmpvisas");

        return $visas;
    }

    public function getAllVisasDesc($lang) {
        $sql = "select * from re_visas";
        $req = $this->runRequest($sql);
        $visasInfo = $req->fetchAll();  // get the first line of the result

        $visas = array();
        foreach ($visasInfo as $visaInfo) {
            $v["id"] = $visaInfo["id"];
            $v["desc"] = $this->getVisaDesc($visaInfo, $lang);
            $visas[] = $v;
        }

        usort($visas, "cmpvisas");

        return $visas;
    }

    /**
     * Remove a visa
     * @param number $id
     */
    public function delete($id) {
        $sql = "DELETE FROM re_visas WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

    public function getAllInstructors() {
        $sql = "select distinct id_instructor from re_visas";
        $req = $this->runRequest($sql);
        $instructors = $req->fetchAll();

        $modelUser = new CoreUser();

        for ($i = 0; $i < count($instructors); $i++) {
            $instructors[$i]["name_instructor"] = $modelUser->getUserFUllName($instructors[$i]["id_instructor"]);
        }

        return $instructors;
    }

}
