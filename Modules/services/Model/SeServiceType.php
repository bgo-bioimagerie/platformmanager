<?php

require_once 'Framework/Model.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeServiceType extends Model {

    private static $serviceTypes;

     /**
     * Constructor
     */
    public function __construct() {
        self::$serviceTypes[1] = "Quantity";
        self::$serviceTypes[2] = "Time minutes";
        self::$serviceTypes[3] = "Time hours";
        self::$serviceTypes[4] = "Price";
        self::$serviceTypes[5] = "Half day";
        self::$serviceTypes[6] = "Day";
        $this->tableName = 'se_service_types';

    }

    public function updateServiceTypesReferences() {
        // extract service types names and ids from database
        $sql = "SELECT `id`, `name` FROM se_service_types";
        $typesData = $this->runRequest($sql, array())->fetchAll();
        $tmpTypes = [];
        $modifiedServiceIds = [];
        $serviceTypesArray = self::$serviceTypes;
        $nbDifferentTypes = 6;

        for ($i=0; $i<$nbDifferentTypes; $i++) {
            // format data get from database to match $serviceTypes format for further comparisons
            $tmpTypes[$typesData[$i]["id"]] = $typesData[$i]["name"];

            // set an array with en and fr names of service type elem
            $okValues = array($serviceTypesArray[$typesData[$i]["id"]], ServicesTranslator::serviceTypes($serviceTypesArray[$typesData[$i]["id"]], "fr"));
            // is db value equal to fr or en name ?
            if (!in_array($tmpTypes[$typesData[$i]["id"]], $okValues)) {
                // if not, get all referencing services
                $sql = "SELECT `id` FROM se_services WHERE type_id=?";
                $serviceIdsReq = $this->runRequest($sql, array($typesData[$i]["id"]))->fetchAll();

                $serviceIds = [];
                foreach ($serviceIdsReq as $serviceId) {
                    if (!in_array($serviceId["id"], $modifiedServiceIds)) {
                        array_push($serviceIds, $serviceId["id"]);
                    }
                }
                //then update
                foreach ($serviceIds as $serviceId) {
                    $sql = "UPDATE se_services SET type_id=? WHERE id=?";
                    // get index by name in $serviceTypesArray
                    $typeIndex = array_search(ServicesTranslator::serviceTypes($typesData[$i]["name"], "en"), $serviceTypesArray);
                    // set serviceId in a blacklist to avoid it to be modified a second time (in case two elements are exactly inverted between database and $serviceTypes array)
                    array_push($modifiedServiceIds, $serviceId);
                    $this->runRequest($sql, array($typeIndex, $serviceId));
                }
            }
        }
    }

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

    // DEPRECATED: using se_service_types table
    /*

    public function exists($id_space, $name) {
        $sql = "select * from se_service_types where name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        return ($req->rowCount() == 1);
    }

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