<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class Proto extends Model {

    /**
     * Create the espece table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_protos` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`nom` varchar(30) NOT NULL,
                `id_space` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_protos WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getForList($id_space) {
        $data = $this->getBySpace($id_space);
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["nom"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    /**
     * get especes informations
     *
     * @param string $sortentry Entry that is used to sort the especes
     * @return multitype: array
     */
    public function getProtos($sortentry = 'id') {

        $sql = "select * from ac_protos order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * get the informations of an espece
     *
     * @param int $id Id of the espece to query
     * @throws Exception id the espece is not found
     * @return mixed array
     */
    public function get($id) {
        if(!$id){
            return array("nom" => "");
        }
        
        $sql = "select * from ac_protos where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1){
            return $unit->fetch();
        }
        else{
            throw new Exception("Cannot find the proto using the given id");
        }
    }

    /**
     * add an espece to the table
     *
     * @param string $name name of the espece
     * 
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_protos(nom, id_space)"
                . " values(?,?)";
        $this->runRequest($sql, array($name, $id_space));
    }

    /**
     * update the information of a 
     *
     * @param int $id Id of the  to update
     * @param string $name New name of the 
     */
    public function edit($id, $name, $id_space) {

        $sql = "update ac_protos set nom=?, id_space=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $id_space, $id));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "select id from ac_protos where nom=? AND id_space=?";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getNameFromId($id) {
        $sql = "select nom from ac_protos where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_protos WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
