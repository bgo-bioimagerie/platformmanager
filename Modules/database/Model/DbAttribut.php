<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbAttribut extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
        $this->tableName = "db_attributs";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_class", "int(11)", 0);
        $this->setColumnsInfo("type", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->setColumnsInfo("foreign_class_id", "int(11)", 0);
        $this->setColumnsInfo("foreign_class_att", "varchar(250)", "");
        $this->setColumnsInfo("mandatory", "int(1)", 0);
        $this->primaryKey = "id";
    }

    public function get($id) {
        $sql = "SELECT * FROM db_attributs WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForClassSelect($id_class){
        $sql = "SELECT * FROM db_attributs WHERE id_class=?";
        $atts = $this->runRequest($sql, array($id_class))->fetchAll();
        
        $id = array(); $name = array();
        foreach($atts as $att){
            $id[] = $att["id"];
            $name[] = $att["name"];
        }
        return array("ids" => $id, "names" => $name);
    }
    
    public function getByClass($id_class){
        $sql = "SELECT * FROM db_attributs WHERE id_class=?";
        $atts = $this->runRequest($sql, array($id_class))->fetchAll();
        
        for($i = 0 ; $i < count($atts) ; $i++){
            if($atts[$i]["type"] == "1"){
                $atts[$i]["type"] = "FLOAT";
            }
            else if($atts[$i]["type"] == "2"){
                $atts[$i]["type"] = "VARCHAR(255)";
            }
            else if($atts[$i]["type"] == "3"){
                $atts[$i]["type"] = "TEXTAREA";
            }
            else if($atts[$i]["type"] == "4"){
                $atts[$i]["type"] = "Foreign key";
            }
            else if($atts[$i]["type"] == "5"){
                $atts[$i]["type"] = "VARCHAR(255)";
            }
        }
        return $atts;
    }
    
    public function getForClass($id_class){
        $sql = "SELECT * FROM db_attributs WHERE id_class=?";
        $atts = $this->runRequest($sql, array($id_class))->fetchAll();
        
        $id = array(); $type = array(); $name = array();
        $foreign_class_id = array(); $foreign_class_att = array();
        $mandatory = array();
        foreach($atts as $att){
            $id[] = $att["id"];
            $type[] = $att["type"];
            $name[] = $att["name"];
            $foreign_class_id[] = $att["foreign_class_id"];
            $foreign_class_att[] = $att["foreign_class_att"];
            $mandatory[] = $att["mandatory"];
        }
        return array("id" => $id, "type" => $type, "name" => $name, "foreign_class_id" => $foreign_class_id,
                    "foreign_class_att" => $foreign_class_att, "mandatory" => $mandatory);
    }
    
    public function setAll($id, $id_class, $type, $name, $mandatory, $foreign_class_id, $foreign_class_att){
        
        // remove the not existing anymore attributs
        $sql = "SELECT id FROM db_attributs WHERE id_class=?";
        $previousAtts = $this->runRequest($sql, array($id_class))->fetchAll();
        foreach($previousAtts as $pAtt){
            if(!in_array($pAtt["id"], $id)){
                $this->delete($pAtt["id"]);
            }
        }
        
        // add or update the others
        for($i = 0 ; $i < count($id) ; $i++){
            $this->set($id[$i], $id_class, $type[$i], $name[$i], $mandatory[$i], $foreign_class_id[$i], $foreign_class_att[$i]);
        }
    }
    
    public function set($id, $id_class, $type, $name, $mandatory = 0, $foreign_class_id = 0, $foreign_class_att = 0) {
        if ($id > 0) {
            $sql = "UPDATE db_attributs SET id_class=?, type=?, name=?, mandatory=?, foreign_class_id=?, foreign_class_att=? WHERE id=?";
            $this->runRequest($sql, array($id_class, $type, $name, $mandatory, $foreign_class_id, $foreign_class_att, $id));
            return $id;
        } else {
            $sql = "INSERT INTO db_attributs (id_class, type, name, mandatory, foreign_class_id, foreign_class_att) VALUES (?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_class, $type, $name, $mandatory, $foreign_class_id, $foreign_class_att));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function exists($id) {
        $sql = "SELECT * from db_attributs WHERE id=?";
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
        $sql = "DELETE FROM db_attributs WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
