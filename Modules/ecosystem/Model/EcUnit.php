<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model
 *
 * @author Sylvain Prigent
 */
class EcUnit extends Model {

    /**
     * Create the unit table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ec_units` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL DEFAULT '',
		`address` varchar(350) NOT NULL DEFAULT '',
		`id_belonging` int(11) NOT NULL,		
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn("ec_units", "id_belonging", "int(11)", 1);
    }

    /**
     * Create the default empty Unit
     * 
     * @return PDOStatement
     */
    public function createDefaultUnit() {

        if (!$this->isUnit(1)) {
            $sql = "INSERT INTO ec_units (name, address) VALUES(?,?)";
            $this->runRequest($sql, array("--", "--"));
        }
    }

    /**
     * get units informations
     * 
     * @param string $sortentry Entry that is used to sort the units
     * @return multitype: array
     */
    public function getUnits($sortentry = 'id') {

        $sql = "SELECT units.* ,
    				   belongings.name AS belonging
    			FROM ec_units AS units
    			INNER JOIN ec_belongings AS belongings ON units.id_belonging = belongings.id
    			ORDER BY " . $sortentry . " ASC;";

        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    public function getUnitsForList($sortentry = 'id') {
        $sql = "SELECT id, name FROM ec_units ORDER BY " . $sortentry . " ASC;";
        $req = $this->runRequest($sql)->fetchAll();
        $ids = array();
        $names = array();
        $ids[] = 0;
        $names[] = "--";
        foreach ($req as $r) {
            $ids[] = $r["id"];
            $names[] = $r["name"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get the names of all the units
     *
     * @return multitype: array
     */
    public function unitsName() {

        $sql = "select name from ec_units";
        $units = $this->runRequest($sql);
        return $units->fetchAll();
    }

    /**
     * Get the units ids and names
     *
     * @return array
     */
    public function unitsIDName() {

        $sql = "select id, name from ec_units";
        $units = $this->runRequest($sql);
        return $units->fetchAll();
    }

    /**
     * add a unit to the table
     *
     * @param string $name name of the unit
     * @param string $address address of the unit
     */
    public function addUnit($name, $address, $id_belonging) {

        $sql = "insert into ec_units(name, address, id_belonging)"
                . " values(?, ?, ?)";
        $this->runRequest($sql, array($name, $address, $id_belonging));
    }

    public function importUnit($id, $name, $address, $id_belonging) {
        $sql = "insert into ec_units(id, name, address, id_belonging)"
                . " values(?, ?, ?, ?)";
        $this->runRequest($sql, array($id, $name, $address, $id_belonging));
    }
    
    public function importUnit2($name, $address, $id_belonging){
        $sql = "SELECT name FROM ec_units WHERE name=?";
        $req = $this->runRequest($sql, array("name"));
        if($req->rowCount() == 0){
            $sql = "insert into ec_units(name, address, id_belonging)"
                . " values(?, ?, ?)";
            $this->runRequest($sql, array($name, $address, $id_belonging));
            return $this->getDatabase()->lastInsertId();
        }
        else{
            $u = $req->fetch();
            return $u[0];
        }
    }

    /**
     * update the information of a unit
     *
     * @param int $id Id of the unit to update
     * @param string $name New name of the unit
     * @param string $address New Address of the unit
     */
    public function editUnit($id, $name, $address, $id_belonging) {

        $sql = "update ec_units set name=?, address=?, id_belonging=? where id=?";
        $this->runRequest($sql, array($name, $address, $id_belonging, $id));
    }

    /**
     * Check if a unit exists
     * @param string $id Unit id
     * @return boolean
     */
    public function isUnit($id) {
        $sql = "select * from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set a unit (add if not exists)
     * @param string $name Unit name
     * @param string $address Unit adress
     */
    public function set($id, $name, $address, $id_belonging) {
        if (!$this->isUnit($id)) {
            $this->addUnit($name, $address, $id_belonging);
        } else {
            $this->editUnit($id, $name, $address, $id_belonging);
        }
    }

    /**
     * get the informations of a unit
     *
     * @param int $id Id of the unit to query
     * @throws Exception id the unit is not found
     * @return mixed array
     */
    public function getUnit($id) {
        $sql = "select * from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new Exception("Cannot find the unit using the given id: " . $id);
        }
    }

    /**
     * get the informations of a unit
     *
     * @param int $id Id of the unit to query
     * @throws Exception id the unit is not found
     * @return mixed array
     */
    public function getInfo($id) {
        $sql = "select * from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new Exception("Cannot find the unit using the given id");
        }
    }

    /**
     * get the name of a unit
     *
     * @param int $id Id of the unit to query
     * @throws Exception if the unit is not found
     * @return mixed array
     */
    public function getUnitName($id) {
        $sql = "select name from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    public function getAdress($id) {
        $sql = "select address from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    public function getBelonging($id) {
        $sql = "select id_belonging from ec_units where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    /**
     * get the id of a unit from it's name
     * 
     * @param string $name Name of the unit
     * @throws Exception if the unit connot be found
     * @return mixed array
     */
    public function getUnitId($name) {
        $sql = "select id from ec_units where name=?";
        $unit = $this->runRequest($sql, array($name));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            throw new Exception("Cannot find the unit using the given name:" . $name);
        }
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id) {
        $sql = "DELETE FROM ec_units WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
