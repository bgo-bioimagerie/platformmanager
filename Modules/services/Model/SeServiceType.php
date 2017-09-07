<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeServiceType extends Model {

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

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Create the default empty Unit
     * 
     * @return PDOStatement
     */
    public function createDefault() {


        $this->add("Quantity", "Quantité");
        $this->add("Time minutes", "Temps en minutes");
        $this->add("Time hours", "Temps en heures");
        $this->add("Price", "Prix");
        $this->add("Half day", "Demi journée");
        $this->add("Journée", "Journée");
    }

    public function getIdFromName($name){
        $sql = "SELECT id FROM se_service_types WHERE name=?";
        $req = $this->runRequest($sql, array($name));
        if($req->rowCount() > 0){
            $temp = $req->fetch();
            return $temp[0];
        }
        return 0;
    }
    
    public function getAll() {
        $sql = "SELECT * FROM se_service_types ORDER BY local_name ASC;";
        $req = $this->runRequest($sql);
        return $req->fetchAll();
    }
    
    public function getAllForSelect(){
        $sql = "SELECT * FROM se_service_types ORDER BY local_name ASC;";
        $req = $this->runRequest($sql)->fetchAll();
        $names = array(); $ids = array();
        foreach($req as $d){
           $names[] = $d["local_name"];
           $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function getLocalName($id) {
        $sql = "SELECT local_name FROM se_service_types WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        $f = $req->fetch();
        return $f[0];
    }

    public function add($name, $local_name) {

        if (!$this->exists($name)) {
            $sql = "INSERT INTO se_service_types (name, local_name) VALUES(?, ?)";
            $this->runRequest($sql, array($name, $local_name));
        }
    }

    public function exists($name) {
        $sql = "select * from se_service_types where name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

}
