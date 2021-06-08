<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbViewAttribut extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_view_attributs";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_view", "int(11)", 0);
        $this->setColumnsInfo("id_attribut", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(5)", "");
        $this->setColumnsInfo("foreign_att_print", "varchar(250)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_view_attributs WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForViewTable($id_view, $lang){
        $sql = "SELECT * FROM db_view_attributs WHERE id_view=?";
        $atts = $this->runRequest($sql, array($id_view))->fetchAll();
        
        for($i = 0 ; $i < count($atts) ; $i++){
            $sql = "SELECT name FROM db_attributs_translate WHERE id_attribut=? AND lang=?";
            $req = $this->runRequest($sql, array($atts[$i]["id_attribut"], $lang))->fetch();
            $atts[$i]["print_name"] = $req[0];
            
            $sql2 = "SELECT name FROM db_attributs WHERE id=?";
            $req2 = $this->runRequest($sql2, array($atts[$i]["id_attribut"]))->fetch();
            $atts[$i]["name"] = $req2[0];
        }
        return $atts;
    }
    
    public function getForView($id_view){
        $sql = "SELECT * FROM db_view_attributs WHERE id_view=?";
        $atts = $this->runRequest($sql, array($id_view))->fetchAll();
        
        $id = array(); $id_attribut = array(); $display_order = array();
        $foreign_att_print = array();
        foreach($atts as $att){
            $id[] = $att["id"];
            $id_attribut[] = $att["id_attribut"];
            $display_order[] = $att["display_order"];
            $foreign_att_print[] = $att["foreign_att_print"];
        }
        return array("id" => $id, "id_attribut" => $id_attribut, 
                     "display_order" => $display_order, "foreign_att_print" => $foreign_att_print );
    }
    
    public function setAll($id, $id_view, $id_attribut, $display_order, $foreign_att_print){
        
        // remove the not existing anymore attributs
        $sql = "SELECT id FROM db_view_attributs WHERE id_view=?";
        $previousAtts = $this->runRequest($sql, array($id_view))->fetchAll();
        foreach($previousAtts as $pAtt){
            if(!in_array($pAtt["id"], $id)){
                $this->delete($pAtt["id"]);
            }
        }
        
        // add or update the others
        for($i = 0 ; $i < count($id) ; $i++){
            $this->set($id[$i], $id_view, $id_attribut[$i], $display_order[$i], $foreign_att_print[$i]);
        }
    }
    
    public function set($id, $id_view, $id_attribut, $display_order, $foreign_att_print) {
        if ($id > 0) {
            $sql = "UPDATE db_view_attributs SET id_view=?, id_attribut=?, display_order=?, foreign_att_print=? WHERE id=?";
            $this->runRequest($sql, array($id_view, $id_attribut, $display_order, $foreign_att_print, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_view_attributs (id_view, id_attribut, display_order, foreign_att_print) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_view, $id_attribut, $display_order, $foreign_att_print));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT * from db_view_attributs WHERE id=?";
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
        $sql = "DELETE FROM db_view_attributs WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
