<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbLang extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_langs";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("short", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function setDefault() {
        if (!$this->exists("fr")){
            $this->add("fr", "Français");
        }
        if (!$this->exists("en")){
            $this->add("en", "English");
        }
    }

    public function add($short, $name) {
        $sql = "INSERT INTO db_langs (short, name) VALUES (?,?)";
        $this->runRequest($sql, array($short, $name));
    }

    public function get($short) {
        $sql = "SELECT * FROM db_langs WHERE id=?";
        return $this->runRequest($sql, array($short))->fetch();
    }
    
    public function getAll(){
        $sql = "SELECT * FROM db_langs ORDER BY name";
        return $this->runRequest($sql, array())->fetchAll();
    }

    public function getAllForForm(){
        $sql = "SELECT * FROM db_langs ORDER BY name";
        $data = $this->runRequest($sql, array())->fetchAll();
        $names = array(); $ids = array();
        foreach ($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["short"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function exists($short) {
        $sql = "SELECT * from db_langs WHERE short=?";
        $req = $this->runRequest($sql, array($short));
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
        $sql = "DELETE FROM db_langs WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
