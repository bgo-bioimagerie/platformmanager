<?php

require_once 'Framework/Model.php';

/**
 * Class defining the source model
 *
 * @author Sylvain Prigent
 */
class Source extends Model {

    /**
     * Create the source table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_sources` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
  				`nom` varchar(30) NOT NULL,
                                `id_space` int(11) NOT NULL,
  				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_sources WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }
    
    public function getForList($id_space){
        $data = $this->getBySpace($id_space);
        $names = array();
        $ids = array();
        foreach($data as $d){
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
    public function getSources($sortentry = 'id') {

        $sql = "select * from ac_sources order by " . $sortentry . " ASC;";
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
        if ($id == 0) {
            return array("nom" => "");
        }

        $sql = "select * from ac_sources where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new Exception("Cannot find the source using the given id:" . $id . "<br/>");
        }
    }

    /**
     * add an source to the table
     *
     * @param string $name name of the source
     *
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_sources(nom, id_space)"
                . " values(?,?)";
        $this->runRequest($sql, array($name, $id_space));
    }

    public function importSource($id, $name, $id_space) {

        $sql = "insert into ac_sources(id, nom, id_space)"
                . " values(?, ?, ?)";
        $this->runRequest($sql, array($id, $name, $id_space));
    }

    /**
     * update the information of a source
     *
     * @param int $id Id of the source to update
     * @param string $name New name of the source
     */
    public function edit($id, $name, $id_space) {

        $sql = "update ac_sources set nom=?, id_space=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $id_space, $id));
    }

    public function getIdFromName($name, $id_space) {
        $sql = "select id from ac_sources where nom=? AND id_space=?";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_sources WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
