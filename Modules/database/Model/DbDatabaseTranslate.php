<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbDatabaseTranslate extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_database_translate";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_database", "int(11)", 0);
        $this->setColumnsInfo("lang", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }


    public function add($id_database, $lang, $name) {
        $sql = "INSERT INTO db_database_translate (id_database, lang, name) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_database, $lang, $name));
    }
    
    public function setAll($id_database, $lang, $name){
        
        $sql = "DELETE FROM db_database_translate WHERE id_database=?";
        $this->runRequest($sql, array($id_database));
       
        for($i = 0 ; $i < count($lang) ; $i++){
            $sql = "INSERT INTO db_database_translate (id_database, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_database, $lang[$i], $name[$i]));
        }
    }
    
    public function set($id_database, $lang, $name){
        
        echo "set lang " . $id_database . ", " . $lang . ", " . $name ."<br/>";
        
        if($this->exists($id_database, $lang)){
            $sql = "UPDATE db_database_translate SET lang=?, name=? WHERE id_database=?";
            $this->runRequest($sql, array($lang, $name, $id_database));
        }
        else{
            $sql = "INSERT INTO db_database_translate (id_database, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_database, $lang, $name));
        }
    }

    public function get($id_database, $name) {
        $sql = "SELECT name FROM db_database_translate WHERE id_database=? AND name=?";
        return $this->runRequest($sql, array($id_database, $name))->fetch();
    }

    public function getAllForForm($id_database){
        $sql = "SELECT * FROM db_database_translate WHERE id_database =? ORDER BY lang";
        $data = $this->runRequest($sql, array($id_database))->fetchAll();
        $names = array(); $ids = array();
        foreach ($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["lang"];
        }
        
        //echo "langs query <br/>";
        //print_r($names); echo "<br/>";
        //print_r($ids); echo "<br/>";
        return array("names" => $names, "ids" => $ids);
    }

    public function exists($id_database, $lang) {
        $sql = "SELECT * from db_database_translate WHERE id_database=? AND lang=?";
        $req = $this->runRequest($sql, array($id_database, $lang));
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
        $sql = "DELETE FROM db_database_translate WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
