<?php

require_once 'Framework/Model.php';

/**
 * Class defining the source model
 *
 * @author Sylvain Prigent
 */
class Organe extends Model {

    /**
     * Create the organe table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_organes` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
  				`nom` varchar(30) NOT NULL,
                                `id_space` int(11) NOT NULL,
  				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_sapce) {
        $sql = "select * from ac_organes WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_sapce));
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
     * get sources informations
     *
     * @param string $sortentry Entry that is used to sort the sources
     * @return multitype: array
     */
    public function getOrganes($sortentry = 'id') {

        $sql = "select * from ac_organes order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * get the informations of a source
     *
     * @param int $id Id of the source to query
     * @throws Exception id the source is not found
     * @return mixed array
     */
    public function get($id) {
        if ($id == 0){
            return array("nom" => "");
        }
        
        $sql = "select * from ac_organes where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1){
            return $unit->fetch();
        }
        else{
            throw new Exception("Cannot find the source using the given id");
        }
    }

    /**
     * add an source to the table
     *
     * @param string $name name of the source
     *
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_organes(nom, id_space)"
                . " values(?,?)";
        $this->runRequest($sql, array($name, $id_space));
    }

    public function importOrgane($id, $name, $id_space) {

        $sql = "insert into ac_organes(id, nom, id_space)"
                . " values(?,?,?)";
        $this->runRequest($sql, array($id, $name, $id_space));
    }

    /**
     * update the information of a source
     *
     * @param int $id Id of the organ to update
     * @param string $name New name of the source
     */
    public function edit($id, $name, $id_space) {

        $sql = "update ac_organes set nom=?, id_space=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $id_space, $id));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "select id from ac_organes where nom=? AND id_space=?";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_organes WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
