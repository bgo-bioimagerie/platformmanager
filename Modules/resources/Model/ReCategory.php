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
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM re_category WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getBySpace($id_space) {
        $sql = "SELECT * FROM re_category WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getIdFromNameSpace($name, $id_space){
        $sql = "SELECT id FROM re_category WHERE name=? AND id_space=?";
        $req = $this->runRequest($sql, array($name, $id_space));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function set($id, $name, $id_space) {
        if ($this->exists($id)) {
            $sql = "UPDATE re_category SET name=?, id_space=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_space, $id));
            return $id;
        } else {
            $sql = "INSERT INTO re_category (name, id_space) VALUES (?, ?)";
            $this->runRequest($sql, array($name, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
        
    }

    public function exists($id) {
        $sql = "SELECT id from re_category WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * get the name of a resources category
     *
     * @param int $id Id of the resources category to query
     * @throws Exception if the resources category is not found
     * @return mixed array
     */
    public function getName($id) {
        $sql = "select name from re_category where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        }
        return "";
    }
    
    public function getIdFromName($name){
        $sql = "select id from re_category where name=?";
        $unit = $this->runRequest($sql, array($name));
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
    public function delete($id) {
        $sql = "DELETE FROM re_category WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
