<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbClassTranslate extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_classes_translate";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_class", "int(11)", 0);
        $this->setColumnsInfo("lang", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function getName($id_class, $lang){
        $sql = "SELECT name FROM db_classes_translate WHERE id_class=? AND lang=?";
        return $this->runRequest($sql, array($id_class, $lang))->fetch();
    }
    
    public function add($id_class, $lang, $name) {
        $sql = "INSERT INTO db_classes_translate (id_class, lang, name) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_class, $lang, $name));
    }
    
    public function setAll($id_class, $lang, $name){
        
        $sql = "DELETE FROM db_classes_translate WHERE id_class=?";
        $this->runRequest($sql, array($id_class));
       
        for($i = 0 ; $i < count($lang) ; $i++){
            $sql = "INSERT INTO db_classes_translate (id_class, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_class, $lang[$i], $name[$i]));
        }
    }
    
    public function set($id_class, $lang, $name){
        
        if($this->exists($id_class, $lang)){
            $sql = "UPDATE db_classes_translate SET lang=?, name=? WHERE id_class=?";
            $this->runRequest($sql, array($lang, $name, $id_class));
        }
        else{
            $sql = "INSERT INTO db_classes_translate (id_class, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_class, $lang, $name));
        }
    }

    public function get($id_class, $name) {
        $sql = "SELECT name FROM db_classes_translate WHERE id_class=? AND name=?";
        return $this->runRequest($sql, array($id_class, $name))->fetch();
    }

    public function getAllForForm($id_class){
        $sql = "SELECT * FROM db_classes_translate WHERE id_class =? ORDER BY lang";
        $data = $this->runRequest($sql, array($id_class))->fetchAll();
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

    public function exists($id_class, $lang) {
        $sql = "SELECT * from db_classes_translate WHERE id_class=? AND lang=?";
        $req = $this->runRequest($sql, array($id_class, $lang));
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
        $sql = "DELETE FROM db_classes_translate WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
