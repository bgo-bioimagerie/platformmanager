<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbView extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_views";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_database", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("id_class", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_views WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForDatabase($id_database, $lang){
        $sql = "SELECT * FROM db_views WHERE id_database=?";
        $classes = $this->runRequest($sql, array($id_database))->fetchAll();
        
        $modelTr = new DbViewTranslate();
        for($i = 0 ; $i < count($classes) ; $i++){
            $name = $modelTr->getName($classes[$i]["id"], $lang);
            $classes[$i]["print_name"] = $name[0];
        }
        return $classes;
    }
    
    public function set($id, $id_database, $name, $id_class) {
        if ($id > 0) {
            $sql = "UPDATE db_views SET name=?, id_database=?, id_class=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_database, $id_class, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_views (id_database, name, id_class) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_database, $name, $id_class));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT id from db_views WHERE id=?";
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
        $sql = "DELETE FROM db_views WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
