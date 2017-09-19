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
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $sql2 = "CREATE TABLE IF NOT EXISTS `ec_j_belonging_units` (
		`id_unit` int(11) NOT NULL,
                `id_belonging` int(11) NOT NULL,
		`id_space` int(11) NOT NULL
		);";

        $this->runRequest($sql2);
    }
    
    public function mergeUnits($units){
        
        // remove useless belongings joint
        for($i = 1 ; $i<count($units) ; $i++){
            $sql = "DELETE FROM ec_j_belonging_units WHERE id_unit=?";
            $this->runRequest($sql, array($units[$i]));
        }
        
        // remove units
        for($i = 1 ; $i<count($units) ; $i++){
            $sql = "DELETE FROM ec_units WHERE id=?";
            $this->runRequest($sql, array($units[$i]));
        }

    }
    
    public function getIdFromName($name){
        $sql = "SELECT id FROM ec_units WHERE name=?";
        $data= $this->runRequest($sql, array($name));
        if ($data->rowCount() > 0){
            $tmp = $data->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function copyToMultipleBelonging($id_space){
        $sql = "SELECT * FROM ec_units";
        $units = $this->runRequest($sql)->fetchAll();
        foreach($units as $unit){
            $this->setBelonging($id_space, $unit["id_belonging"], $unit["id"]);
        }
    }

    public function getBelonging($id_unit, $id_space) {
        $sql = "SELECT id_belonging FROM ec_j_belonging_units WHERE id_unit=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_unit, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function setBelonging($id_space, $id_belonging, $id_unit) {
        
        $sql = "DELETE FROM ec_j_belonging_units WHERE id_unit=? AND id_space=?";
        $this->runRequest($sql, array($id_unit, $id_space));
        
        //echo "set belonging: " . $id_space . " " . $id_belonging . " " . $id_unit . "<br/>"; 
        
        if ($this->isSetUnit($id_space, $id_unit)) {
            //echo "update belonging <br>";
            $sql = "UPDATE ec_j_belonging_units SET id_belonging=? WHERE id_unit=? AND id_space=?";
            $this->runRequest($sql, array($id_belonging, $id_unit, $id_space));
        } else {
            //echo "insert belonging: $id_space <br>";
            $sql = "INSERT INTO ec_j_belonging_units (id_unit, id_belonging, id_space) VALUES(?,?,?)";
            $this->runRequest($sql, array($id_unit, $id_belonging, $id_space));
        }
    }

    public function isSetUnit($id_space, $id_unit) {
        $sql = "SELECT id_unit FROM ec_j_belonging_units WHERE id_space=? AND id_unit=?";
        $req = $this->runRequest($sql, array($id_space, $id_unit));
        //echo "num bel = " . $req->rowCount() . "<br/>";
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
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
    public function getUnits($id_space, $sortentry = 'id') {

        $sql = "SELECT * FROM ec_units ORDER BY " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        $units = $user->fetchAll();

        for ($i = 0; $i < count($units); $i++) {
            //echo "unit id = " . $units[$i]["id"] ."<br/>"; 
            $sql = "SELECT id_belonging FROM ec_j_belonging_units WHERE id_space=? AND id_unit=?";
            $req = $this->runRequest($sql, array($id_space, $units[$i]["id"]));
            if ($req->rowCount() == 1) {
                $sql2 = "SELECT name FROM ec_belongings WHERE id=?";
                $val = $req->fetch();
                //echo "belonging id = " . $val[0] . "<br/>";
                $belonging = $this->runRequest($sql2, array($val[0]))->fetch();
                //echo "belonging name = " . $belonging[0] . "<br/>";
                $units[$i]["belonging"] = $belonging[0];
            } else {
                $units[$i]["belonging"] = "";
            }
        }
        return $units;
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
    public function addUnit($name, $address) {

        $sql = "insert into ec_units(name, address)"
                . " values(?, ?)";
        $this->runRequest($sql, array($name, $address));
        return $this->getDatabase()->lastInsertId();
    }

    public function importUnit($id, $name, $address, $id_belonging) {
        $sql = "insert into ec_units(id, name, address, id_belonging)"
                . " values(?, ?, ?, ?)";
        $this->runRequest($sql, array($id, $name, $address, $id_belonging));
    }

    public function importUnit2($name, $address) {
        $sql = "SELECT name FROM ec_units WHERE name=?";
        $req = $this->runRequest($sql, array("name"));
        if ($req->rowCount() == 0) {
            $sql = "insert into ec_units(name, address)"
                    . " values(?, ?)";
            $this->runRequest($sql, array($name, $address));
            return $this->getDatabase()->lastInsertId();
        } else {
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
    public function editUnit($id, $name, $address) {

        $sql = "update ec_units set name=?, address=? where id=?";
        $this->runRequest($sql, array($name, $address, $id));
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
    public function set($id, $name, $address) {
        if (!$this->isUnit($id)) {
            return $this->addUnit($name, $address);
        } else {
            $this->editUnit($id, $name, $address);
            return $id;
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
            
            echo "Cannot find the unit using the given name:" . $name . "<br/>";
            return 0;
            //throw new Exception("Cannot find the unit using the given name:" . $name);
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
