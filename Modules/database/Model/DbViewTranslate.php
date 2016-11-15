<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbViewTranslate extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_views_translate";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_view", "int(11)", 0);
        $this->setColumnsInfo("lang", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function getName($id_view, $lang){
        $sql = "SELECT name FROM db_views_translate WHERE id_view=? AND lang=?";
        return $this->runRequest($sql, array($id_view, $lang))->fetch();
    }
    
    public function add($id_view, $lang, $name) {
        $sql = "INSERT INTO db_views_translate (id_view, lang, name) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_view, $lang, $name));
    }
    
    public function setAll($id_view, $lang, $name){
        
        $sql = "DELETE FROM db_views_translate WHERE id_view=?";
        $this->runRequest($sql, array($id_view));
       
        for($i = 0 ; $i < count($lang) ; $i++){
            $sql = "INSERT INTO db_views_translate (id_view, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_view, $lang[$i], $name[$i]));
        }
    }
    
    public function set($id_view, $lang, $name){
        
        if($this->exists($id_view, $lang)){
            $sql = "UPDATE db_views_translate SET lang=?, name=? WHERE id_view=?";
            $this->runRequest($sql, array($lang, $name, $id_view));
        }
        else{
            $sql = "INSERT INTO db_views_translate (id_view, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_view, $lang, $name));
        }
    }

    public function get($id_view, $name) {
        $sql = "SELECT name FROM db_views_translate WHERE id_view=? AND name=?";
        return $this->runRequest($sql, array($id_view, $name))->fetch();
    }

    public function getAllForForm($id_view){
        $sql = "SELECT * FROM db_views_translate WHERE id_view =? ORDER BY lang";
        $data = $this->runRequest($sql, array($id_view))->fetchAll();
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

    public function exists($id_view, $lang) {
        $sql = "SELECT * from db_views_translate WHERE id_view=? AND lang=?";
        $req = $this->runRequest($sql, array($id_view, $lang));
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
        $sql = "DELETE FROM db_views_translate WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
