<?php

require_once 'Framework/Model.php';

class CoreMainMenuPatch extends Model {

    public function __construct() {

    }

    public function patch(){
        
        if ( $this->needPatch()){
            
            $this->importMenus();
            $this->importItems();
            $this->importSpacesInfo();
            $this->deleteOldTable();
        }
    }
    
    protected function needPatch(){
        
        $sqlOldMenuExists = "SHOW TABLES LIKE 'core_menu';";
        $req1 = $this->runRequest($sqlOldMenuExists);
        if ( $req1->rowCount() > 0 ){
            $sql = "SELECT * FROM core_main_menus";
            $req = $this->runRequest($sql);
            if ($req->rowCount() > 0){
                return false;
            }
            return true;
        }
        return false;
        
    }
    
    protected function importMenus(){
        // import previous menus
        $sqlom = "SELECT * FROM core_menu";
        $oldmenus = $this->runRequest($sqlom)->fetchAll();
        
        foreach ($oldmenus as $om){
            $sql = "INSERT INTO core_main_menus (name, display_order) VALUES (?,?)";
            $this->runRequest($sql, array($om["name"], $om["display_order"]));
            $mainMenuID = $this->getDatabase()->lastInsertId();
            
            $sql2 = "INSERT INTO core_main_sub_menus (name, id_main_menu, display_order) VALUES (?,?,?)";
            $this->runRequest($sql2, array($om["name"], $mainMenuID, $om["display_order"]));
            
        }
        
    }
    
    protected function importItems(){
        
        $sql = "SELECT * FROM core_datamenu";
        $datamenus = $this->runRequest($sql)->fetchAll();
        foreach( $datamenus as $dm ){
            
            $spacelink = explode("/", $dm["link"]);
            $id_space = $spacelink[count($spacelink)-1];
            $id_sub_menu = $this->getNewSubMenuId($dm["id_menu"]);
            
            $sql = "INSERT INTO core_main_menu_items (name, id_sub_menu, id_space, display_order) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($dm["name"], $id_sub_menu, $id_space, $dm["display_order"]));
            
        }
    }
            
    protected function getNewSubMenuId($id_old){
        
        $sql = "SELECT name FROM core_menu WHERE id=?";
        $oldName = $this->runRequest($sql, array($id_old))->fetch();
        
        $sql2 = "SELECT id FROM core_main_sub_menus WHERE name=?";
        $newID = $this->runRequest($sql2, array($oldName[0]))->fetch();
        return $newID[0];
    }
    
    protected function importSpacesInfo(){
        $sql = "SELECT * FROM core_datamenu";
        $datamenus = $this->runRequest($sql)->fetchAll();
        foreach( $datamenus as $dm ){
            
            $spacelink = explode("/", $dm["link"]);
            $id_space = $spacelink[count($spacelink)-1];
            
            $sql = "UPDATE core_spaces SET description=?, image=? WHERE id=?";
            $this->runRequest($sql, array($dm["description"], $dm["icon"], $id_space));
            
        }
    }
            
    protected function deleteOldTable(){
        $sql = "DROP TABLE core_menu; DROP TABLE core_datamenu";
        $this->runRequest($sql);
    }
    
    
}
