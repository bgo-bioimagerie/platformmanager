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
    public function createDefault($id_space) {
        $this->add($id_space, "Quantity", "Quantité");
        $this->add($id_space, "Time minutes", "Temps en minutes");
        $this->add($id_space, "Time hours", "Temps en heures");
        $this->add($id_space, "Price", "Prix");
        $this->add($id_space, "Half day", "Demi journée");
        $this->add($id_space, "Journée", "Journée");
    }

    public function getIdFromName($id_space, $name){
        $sql = "SELECT id FROM se_service_types WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if($req->rowCount() > 0){
            $temp = $req->fetch();
            return $temp[0];
        }
        return 0;
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

        if (!$this->exists($name)) {
            $sql = "INSERT INTO se_service_types (name, local_name, id_space) VALUES(?,?,?)";
            $this->runRequest($sql, array($name, $local_name, $id_space));
        }
    }

    public function exists($id_space, $name) {
        $sql = "select * from se_service_types where name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

}
