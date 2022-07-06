<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class Acii extends Model {

    public function __construct() {
        $this->tableName = "ac_aciis";
    }

    /**
     * Create the espece table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_aciis` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`nom` varchar(30) NOT NULL,
                `id_space` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $this->runRequest($sql);
        $this->baseSchema();
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * from ac_aciis WHERE id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get especes informations
     *
     * @param string $sortentry Entry that is used to sort the especes
     * @return multitype: array
     */
    public function getAciis($id_space, $sortentry = 'id') {

        $sql = "SELECT * from ac_aciis WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get the informations of an espece
     *
     * @param int $id Id of the espece to query
     * @throws Exception id the espece is not found
     * @return mixed array
     */
    public function get($id_space, $id) {
        if(!$id){
            return array( "id" => 0, "nom" => "");
        }
        
        $sql = "SELECT * from ac_aciis where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1){
            return $unit->fetch();
        }
        else{
            throw new PfmParamException("Cannot find the dem using the given id", 404);
        }
    }

    /**
     * add an espece to the table
     *
     * @param string $name name of the espece
     * 
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_aciis(nom, id_space)"
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

        $sql = "update ac_aciis set nom=? where id=? AND id_space=?";
        $this->runRequest($sql, array("" . $name . "", $id, $id_space));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "select id from ac_aciis where nom=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getNameFromId($id_space, $id) {
        $sql = "select nom from ac_aciis where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE ac_aciis SET deleted=1,deleted_at=NOW() WHERE id=? and id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
