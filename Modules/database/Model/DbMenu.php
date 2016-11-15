<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbMenu extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_menu";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_database", "int(11)", 0);
        $this->setColumnsInfo("edit_or_view", "varchar(1)", "");
        $this->setColumnsInfo("table_id", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(5)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_menu WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForDatabase($id_database){
        $sql = "SELECT * FROM db_menu WHERE id_database=?";
        return $this->runRequest($sql, array($id_database))->fetchAll();
    }
    
    public function getForDatabaseSelect($id_database){
        $sql = "SELECT * FROM db_menu WHERE id_database=?";
        $data = $this->runRequest($sql, array($id_database))->fetchAll();
        
        $id = array(); $class_view = array(); $display_order = array();
        foreach($data as $d){
            $id[] = $d["id"];
            $class_view[] = $d["edit_or_view"] . "_" . $d["table_id"];
            $display_order[] = $d["display_order"];
        }
        return array("id" => $id, "class_view" => $class_view, 
                     "display_order" => $display_order);
    }
    
    public function setAll($id, $id_database, $class_view, $display_order){
        
        // remove the not existing anymore attributs
        $sql = "SELECT id FROM db_menu WHERE id_database=?";
        $previousAtts = $this->runRequest($sql, array($id_database))->fetchAll();
        foreach($previousAtts as $pAtt){
            if(!in_array($pAtt["id"], $id)){
                $this->delete($pAtt["id"]);
            }
        }
        
        // add or update the others
        $ids = array();
        for($i = 0 ; $i < count($id) ; $i++){
            $table_id = explode("_", $class_view[$i]);
            $ids[] = $this->set($id[$i], $id_database, $table_id[0], $table_id[1], $display_order[$i]);
        }
        return $ids;
    }
    
    public function set($id, $id_database, $edit_or_view, $table_id, $display_order) {
        if ($id > 0) {
            $sql = "UPDATE db_menu SET id_database=?, edit_or_view=?, table_id=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($id_database, $edit_or_view, $table_id, $display_order, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_menu (id_database, edit_or_view, table_id, display_order) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_database, $edit_or_view, $table_id, $display_order));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT * from db_menu WHERE id=?";
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
        $sql = "DELETE FROM db_menu WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
