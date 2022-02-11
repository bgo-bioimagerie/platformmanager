<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Site model
 *
 * @author Sylvain Prigent
 */
class ReCategory extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "re_category";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM re_category WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * FROM re_category WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getIdFromNameSpace($name, $id_space){
        $sql = "SELECT id FROM re_category WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $name, $id_space) {
        if ($this->exists($id_space, $id)) {
            $sql = "UPDATE re_category SET name=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $id, $id_space));
            return $id;
        } else {
            $sql = "INSERT INTO re_category (name, id_space) VALUES (?, ?)";
            $this->runRequest($sql, array($name, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
        
    }

    public function exists($id_space, $id) {
        $sql = "SELECT id from re_category WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM re_category WHERE id_space=? AND deleted=0 ORDER BY name ASC;";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get the name of a resources category
     *
     * @param int $id Id of the resources category to query
     * @throws Exception if the resources category is not found
     * @return mixed array
     */
    public function getName($id_space, $id) {
        $sql = "select name from re_category where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        }
        return "";
    }
    
    public function getIdFromName($id_space, $name){
        $sql = "SELECT id from re_category where name=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $id_space));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        }
        return "";
    }

    /**
     * Delete a unit
     * @param number $id ID
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE re_category SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM re_category WHERE id = ? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id, $id_space));
    }

}
