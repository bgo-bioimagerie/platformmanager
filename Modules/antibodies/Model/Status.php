<?php

require_once 'Framework/Model.php';
require_once 'Framework/Constants.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class Status extends Model {

    public function __construct() {
        $this->tableName = "ac_status";
    }

    /**
     * Create the Status table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_status` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`nom` varchar(30) NOT NULL,
				`color` varchar(7) NOT NULL,
                `display_order` INT(11) NOT NULL,
                `id_space` INT(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_status WHERE id_space=? AND deleted=0 ORDER BY display_order ASC;";
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
     * get Statuss informations
     *
     * @param string $sortentry Entry that is used to sort the Statuss
     * @return multitype: array
     */
    public function getStatus($id_space, $sortentry = 'id') {
        $sql = "select * from ac_status WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get the informations of an Status
     *
     * @param int $id Id of the Status to query
     * @throws Exception id the Status is not found
     * @return mixed array
     */
    public function get($id_space, $id) {
        if(!$id){
            return array("color" => Constants::COLOR_WHITE, "nom" => "", "display_order" => 0);
        }
        
        $sql = "select * from ac_status where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new PfmException("Cannot find the Status using the given id", 404);
        }
    }

    /**
     * add an Status to the table
     *
     * @param string $name name of the Status
     * 
     */
    public function add($name, $color, $display_order, $id_space) {

        $sql = "insert into ac_status(nom, color, display_order, id_space)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($name, $color, $display_order, $id_space));
    }

    public function importStatus($id, $name, $color, $id_space) {

        $sql = "insert into ac_status(id, nom, color, id_space)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($id, $name, $color, $id_space));
    }

    /**
     * update the information of a 
     *
     * @param int $id Id of the  to update
     * @param string $name New name of the 
     */
    public function edit($id, $name, $color, $display_order, $id_space) {

        $sql = "update ac_status set nom=?, color=?, display_order=? where id=? AND id_space=?";
        $this->runRequest($sql, array("" . $name . "", $color, $display_order, $id, $id_space));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "select id from ac_status where nom=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE ac_status SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
