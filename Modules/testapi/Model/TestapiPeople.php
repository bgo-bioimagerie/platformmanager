<?php

require_once 'Framework/Model.php';


/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class TestapiPeople extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "tapi_people";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("firstname", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM tapi_people WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function set($id, $name, $firstname) {
        if ($id > 0) {
            $sql = "UPDATE tapi_people SET name=?, firstname=? WHERE id=?";
            $this->runRequest($sql, array($name, $firstname, $id));
            return $id;
        } else {
            $sql = "INSERT INTO tapi_people (name, firstname) VALUES (?,?)";
            $this->runRequest($sql, array($name, $firstname));
            return $this->getDatabase()->lastInsertId();
        }
    }

    /**
     * Delete a database
     * @param number $id ID
     */
    public function delete($id) {
        $sql = "DELETE FROM tapi_people WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
