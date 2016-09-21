<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class Status extends Model {

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
                                `id_space` INT(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_status WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get Statuss informations
     *
     * @param string $sortentry Entry that is used to sort the Statuss
     * @return multitype: array
     */
    public function getStatus($sortentry = 'id') {

        $sql = "select * from ac_status order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * get the informations of an Status
     *
     * @param int $id Id of the Status to query
     * @throws Exception id the Status is not found
     * @return mixed array
     */
    public function get($id) {
        if($id == 0){
            return array("color" => "#ffffff", "nom" => "");
        }
        
        $sql = "select * from ac_status where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new Exception("Cannot find the Status using the given id");
        }
    }

    /**
     * add an Status to the table
     *
     * @param string $name name of the Status
     * 
     */
    public function add($name, $color, $id_space) {

        $sql = "insert into ac_status(nom, color, id_space)"
                . " values(?,?,?)";
        $this->runRequest($sql, array($name, $color, $id_space));
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
    public function edit($id, $name, $color, $id_space) {

        $sql = "update ac_status set nom=?, color=?, id_space=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $color, $id_space, $id));
    }

    public function getIdFromName($name) {
        $sql = "select id from ac_status where nom=?";
        $unit = $this->runRequest($sql, array($name));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_status WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
