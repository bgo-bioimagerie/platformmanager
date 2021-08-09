<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class AcOption extends Model {

    /**
     * Create the espece table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_options` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`nom` varchar(30) NOT NULL,
                `id_space` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * from ac_options where id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get especes informations
     *
     * @param string $sortentry Entry that is used to sort the especes
     * @return multitype: array
     */
    public function getOptions($id_space, $sortentry = 'id') {

        $sql = "SELECT * from ac_options WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
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
        
        if (!$id){
            return array("nom" => "");
        }
        
        $sql = "SELECT * from ac_options where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new PfmException("Cannot find the option using the given id", 404);
        }
    }

    /**
     * add an espece to the table
     *
     * @param string $name name of the espece
     * 
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_options(nom, id_space)"
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

        $sql = "UPDATE ac_options set nom=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array("" . $name . "", $id, $id_space));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "SELECT id from ac_options where nom=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getNameFromId($id_space, $id) {
        $sql = "SELECT nom from ac_options where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE ac_options SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM ac_options WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
