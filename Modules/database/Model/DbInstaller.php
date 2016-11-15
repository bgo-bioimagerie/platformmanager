<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbInstaller extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
    }
    
    public function install($id_database){
        
        // install the classes
        $modelClass = new DbClass();
        $classes = $modelClass->getForDatabase($id_database, "en");
        
        $modelAtt = new DbAttribut();
        
        foreach($classes as $cl){
            
            $sql = "CREATE TABLE IF NOT EXISTS dbc_".$cl["name"]." (
                    id INT(11) PRIMARY KEY AUTO_INCREMENT";
            
            $atts = $modelAtt->getByClass($cl["id"]);
            
            foreach($atts as $att){
                if($att["type"] == "Foreign key"){
                    $sql .= ", " . $att["name"] . " INT(11)";
                }
                else{
                    $sql .= ", " . $att["name"] . " " . $att["type"];
                }
            }
            foreach($atts as $att){
                if($att["type"] == "Foreign key"){
                    $foreign_class = $modelClass->get($att["foreign_class_id"]);
                    $sql .= ", CONSTRAINT fk_" . $att["name"] . " FOREIGN KEY (" . $att["name"] . ")"
                         . " REFERENCES " . $foreign_class["name"] . "(" . $att["foreign_class_att"] . ")";
                }
            }
            $sql .= ")";
            
            $this->runRequest($sql);
        }
        
    }
    
}
