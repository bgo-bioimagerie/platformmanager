<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Belonging model
 *
 * @author Sylvain Prigent
 */
class EcBelonging extends Model {

    /**
     * Create the belonging table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ec_belongings` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL DEFAULT '',
		`color` varchar(7) NOT NULL DEFAULT '#ffffff',
		`type` int(1) NOT NULL DEFAULT 1,
                `id_space` int(11) NOT NULL DEFAULT 0,
                `display_order` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
        $this->addColumn('ec_belongings', 'id_space', 'int(11)', 0);
        $this->addColumn('ec_belongings', 'display_order', 'int(11)', 0);
        return 1;
    }

    /**
     * Create the default empty belonging
     * 
     * @return PDOStatement
     */
    public function createDefault() {


        /*
          if (!$this->exists(1)) {
          $sql = "INSERT INTO ec_belongings (name) VALUES(?)";
          $this->runRequest($sql, array("--"));
          }
         */
    }

    public function zerosTo($idx) {
        $sql = "SELECT * FROM ec_belongings WHERE id_space=0";
        $data = $this->runRequest($sql)->fetchAll();
        foreach ($data as $d) {
            $sql = "update ec_belongings set id_space=? where id=?";
            $this->runRequest($sql, array($idx, $d['id']));
        }
    }

    /**
     * get belongings informations
     * 
     * @param string $sortentry Entry that is used to sort the belongings
     * @return multitype: array
     */
    public function getBelongings($id_space, $sortentry = 'display_order') {

        $sql = "SELECT * FROM ec_belongings WHERE id_space=? order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM ec_belongings WHERE id_space=? order by id ASC;";
        $user = $this->runRequest($sql, array($id_space));
        $data = $user->fetchAll();

        $ids = array();
        $names = array();
        $ids[] = 0;
        $names[] = "";
        foreach ($data as $d) {
            $ids[] = $d["id"];
            $names[] = $d["name"];
        }
        return array("ids" => $ids, "names" => $names);
    }

    /**
     * get the names of all the belongings
     *
     * @return multitype: array
     */
    public function getNames($id_space) {

        $sql = "SELECT name FROM ec_belongings WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        $inter = $req->fetchAll();
        $names = array();
        foreach ($inter as $name) {
            $names[] = $name['name'];
        }
        return $names;
    }

    /**
     * Get the belongings ids and names
     *
     * @return array
     */
    public function getAll($id_space) {

        $sql = "select id, name, color, type, display_order from ec_belongings WHERE id_space";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getIds($id_space) {

        $sql = "select id from ec_belongings WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        $inter = $req->fetchAll();
        $ids = array();
        foreach ($inter as $id) {
            $ids[] = $id['id'];
        }
        return $ids;
    }

    /**
     * add a belongong to the table
     *
     * @param string $name name of the belonging
     */
    public function add($id_space, $name, $color, $type, $display_order) {

        $sql = "insert into ec_belongings(id_space, name, color, type, display_order)"
                . " values(?,?,?,?,?)";
        $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * update the information of a belonging
     *
     * @param int $id Id of the belonging to update
     * @param string $name New name of the belonging
     */
    public function edit($id, $id_space, $name, $color, $type, $display_order) {

        $sql = "update ec_belongings set id_space=?, name=?, color=?, type=?, display_order=? where id=?";
        $this->runRequest($sql, array($id_space, $name, $color, $type, $display_order, $id));
    }

    /**
     * Check if a Belonging exists
     * @param string $id Belonging id
     * @return boolean
     */
    public function exists($id) {
        $sql = "select * from ec_belongings where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set a Belonging (add if not exists)
     * @param string $name Belonging name
     */
    public function set($id, $id_space, $name, $color, $type, $display_order) {
        if (!$this->exists($id)) {
            $this->add($id_space, $name, $color, $type, $display_order);
        } else {
            $this->edit($id, $id_space, $name, $color, $type, $display_order);
        }
    }

    /**
     * get the informations of a ec_belongings
     *
     * @param int $id Id of the belonging to query
     * @throws Exception id the belonging is not found
     * @return mixed array
     */
    public function getInfo($id) {

        if ($id == 0) {
            return array('id' => 0, "name" => '', 'color' => '#ffffff', 'type' => 1, 'display_order' => 0);
        } else {
            $sql = "select * from ec_belongings where id=?";
            $req = $this->runRequest($sql, array($id));
            return $req->fetch();  // get the first line of the result
        }
    }

    /**
     * get the name of a belonging
     *
     * @param int $id Id of the belonging to query
     * @throws Exception if the belonging is not found
     * @return mixed array
     */
    public function getName($id) {
        $sql = "select name from ec_belongings where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    public function getIdByNameSpace($name, $id_space) {
        $sql = "select id from ec_belongings where name=? AND id_space=?";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    /**
     * get the id of a belonging from it's name
     * 
     * @param string $name Name of the belonging
     * @throws Exception if the belonging connot be found
     * @return mixed array
     */
    public function getId($name) {
        $sql = "select id from ec_belongings where name=?";
        $req = $this->runRequest($sql, array($name));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            throw new Exception("Cannot find the belonging using the given name:" . $name);
        }
    }

    /**
     * Delete a belonging
     * @param number $id belonging ID
     */
    public function delete($id) {
        $sql = "DELETE FROM ec_belongings WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
