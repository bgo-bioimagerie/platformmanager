<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbDatabase extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_database";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_database WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getBySpace($id_space, $lang){
        $sql = "SELECT * FROM db_database WHERE id_space=?";
        $dbs = $this->runRequest($sql, array($id_space))->fetchAll();
        
        for($i = 0 ; $i < count($dbs) ; $i++){
            $sql = "SELECT name FROM db_database_translate WHERE id_database=? AND lang=?";
            $name = $this->runRequest($sql, array($dbs[$i]["id"], $lang))->fetch();
            $dbs[$i]["print_name"] = $name[0];
        }
        return $dbs;
    }
    
    public function set($id, $id_space, $name) {
        if ($id > 0) {
            $sql = "UPDATE db_database SET name=?, id_space=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_space, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_database (id_space, name) VALUES (?,?)";
            $this->runRequest($sql, array($id_space, $name));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT db_database from db_database WHERE id=?";
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
        $sql = "DELETE FROM db_database WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
