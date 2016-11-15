<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbType extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_types";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function setDefault() {
        if (!$this->exists("1")){
            $this->add("Number");
        }
        if (!$this->exists("2")){
            $this->add("Text");
        }
        if (!$this->exists("3")){
            $this->add("Text area");
        }
        if (!$this->exists("4")){
            $this->add("Foreign key");
        }
        if (!$this->exists("5")){
            $this->add("File");
        }
    }
    
    public function getAllForForm(){
        $sql = "SELECT * FROM db_types";
        $types = $this->runRequest($sql)->fetchAll();
        $names = array(); $ids = array();
        foreach($types as $t){
            $names[] = $t["name"];
            $ids[] = $t["id"];
        }
        return array("names" => $names, "ids" => $ids );
    }

    public function add($name) {
        $sql = "INSERT INTO db_types (name) VALUES (?)";
        $this->runRequest($sql, array($name));
    }

   

    public function exists($id) {
        $sql = "SELECT * FROM db_types WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Delete a database
     * @param number $id ID
     */
    public function delete($id) {
        $sql = "DELETE FROM db_types WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
