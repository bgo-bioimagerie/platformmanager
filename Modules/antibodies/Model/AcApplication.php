<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class AcApplication extends Model {

    /**
     * Create the espece table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_applications` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(30) NOT NULL,
                                `id_space` int(11),
				PRIMARY KEY (`id`)
				);";

        $this->runRequest($sql);

        /*
          if (!$this->isEntryApp("--")){
          $this->addApplication("--");
          }
         */
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_applications WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getForList($id_space) {
        $data = $this->getBySpace($id_space);
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
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
    public function getApplications($sortentry = 'id') {

        $sql = "select * from ac_applications order by " . $sortentry . " ASC;";
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
        if ($id == 0) {
            return array("name" => "");
        }
        $sql = "select * from ac_applications where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new Exception("Cannot find the staining using the given id");
        }
    }

    /**
     * add an espece to the table
     *
     * @param string $name name of the espece
     * 
     */
    public function add($name, $id_space) {

        $sql = "insert into ac_applications(name, id_space)"
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

        $sql = "update ac_applications set name=?, id_space=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $id_space, $id));
    }

    public function getIdFromName($name) {
        $sql = "select id from ac_applications where name=?";
        $unit = $this->runRequest($sql, array($name));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getNameFromId($id) {
        $sql = "select name from ac_applications where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function isEntryApp($name) {
        $sql = "select id from ac_applications where name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_applications WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
