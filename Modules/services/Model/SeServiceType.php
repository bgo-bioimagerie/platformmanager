<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeServiceType extends Model {


    // Should we leave createTable() here for upgrade ?
    /**
     * Create the unit table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `se_service_types` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(100) NOT NULL DEFAULT '',
		`local_name` varchar(100) NOT NULL DEFAULT '',		
		PRIMARY KEY (`id`)
		);";
        return $this->runRequest($sql);
    }

    /**
     * All of service types
     */
    private static $serviceTypes = array(
        "Quantity",
        "Time minutes",
        "Time hours",
        "Price",
        "Half day",
        "Day"
    );
    
    /**
     * get all service types
     * 
     * @return array(string) serviceTypes
     */
    public function getTypes() {
        return self::$serviceTypes;
    }

    /**
     * get a service type
     * 
     * @param int|string $id_type
     * 
     * @return array(string) serviceTypes
     */
    public function getType($id_type) {
        return self::$serviceTypes[intval($id_type)];
    }

    /**
     * get all service types formatted for
     * framework to create a select field in form
     * 
     * @return array("names" => array(string), "ids" => array(int))
     */
    public function getAllForSelect() {
        $names = [];
        $ids = [];
        foreach(self::$serviceTypes as $id => $name) {
            array_push($ids, $id);
            array_push($names, $name);
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get position of a type name in serviceTypes array
     * 
     * @param string $name
     * 
     * @return int|bool index in serviceTypes array | false
     */
    public function getIdFromName($name){
        return array_search($name, self::$serviceTypes);
    }

    public function exists($id_space, $name) {
        $sql = "select * from se_service_types where name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        return ($req->rowCount() == 1);
    }

    // DEPRECATED: using se_service_types table
    /*

     * Create the default empty Unit
     * 
     * @return PDOStatement
    public function createDefault($id_space) {
        $this->add($id_space, "Quantity", "Quantité");
        $this->add($id_space, "Time minutes", "Temps en minutes");
        $this->add($id_space, "Time hours", "Temps en heures");
        $this->add($id_space, "Price", "Prix");
        $this->add($id_space, "Half day", "Demi journée");
        $this->add($id_space, "Journée", "Journée");
    }
    
    public function getAll($id_space) {
        $sql = "SELECT * FROM se_service_types WHERE id_space=? AND deleted=0 ORDER BY local_name ASC;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function getAllForSelect($id_space){
        $sql = "SELECT * FROM se_service_types WHERE id_space=? AND deleted=0 ORDER BY local_name ASC;";
        $req = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array(); $ids = array();
        foreach($req as $d){
           $names[] = $d["local_name"];
           $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function getLocalName($id_space, $id) {
        $sql = "SELECT local_name FROM se_service_types WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        $f = $req->fetch();
        return $f[0];
    }
    
    public function add($id_space, $name, $local_name) {
        if (!$this->exists($id_space, $name)) {
            $sql = "INSERT INTO se_service_types (name, local_name, id_space) VALUES(?,?,?)";
            $this->runRequest($sql, array($name, $local_name, $id_space));
        }
    }

    public function exists($id_space, $name) {
        $sql = "select * from se_service_types where name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        return ($req->rowCount() == 1);
    }
    */

}