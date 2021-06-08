<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbClass extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_classes";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_database", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_classes WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getPrintName($id, $lang){
        $modelTr = new DbClassTranslate();
        $name = $modelTr->getName($id, $lang);
        return $name[0];
    }
    
    public function getForDatabase($id_database, $lang){
        $sql = "SELECT * FROM db_classes WHERE id_database=?";
        $classes = $this->runRequest($sql, array($id_database))->fetchAll();
        
        $modelTr = new DbClassTranslate();
        for($i = 0 ; $i < count($classes) ; $i++){
            $name = $modelTr->getName($classes[$i]["id"], $lang);
            $classes[$i]["print_name"] = $name[0];
        }
        return $classes;
    }
    
    public function getForDatabaseSelect($id_database){
        $sql = "SELECT * FROM db_classes WHERE id_database=?";
        $classes = $this->runRequest($sql, array($id_database))->fetchAll();
        
        $ids = array(); $names = array();
        foreach($classes as $c){
            $ids[] = $c["id"];
            $names[] = $c["name"];
        }
        return array("ids" => $ids, "names" => $names);
    }
    
    public function set($id, $id_database, $name) {
        if ($id > 0) {
            $sql = "UPDATE db_classes SET name=?, id_database=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_database, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_classes (id_database, name) VALUES (?,?)";
            $this->runRequest($sql, array($id_database, $name));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT db_classes from db_classes WHERE id=?";
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
        $sql = "DELETE FROM db_classes WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
