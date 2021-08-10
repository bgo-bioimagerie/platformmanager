<?php

require_once 'Framework/Model.php';

class CoreMainMenuItem extends Model {

    public function __construct() {
        $this->tableName = "core_main_menu_items";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("id_sub_menu", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->primaryKey = "id";
    }
    
    public function getAll(){
        $sql = "SELECT core_main_menu_items.* , core_main_sub_menus.name as sub_menu "
                . "FROM core_main_menu_items "
                . "INNER JOIN core_main_sub_menus ON core_main_sub_menus.id = core_main_menu_items.id_sub_menu "
                . "ORDER BY core_main_sub_menus.name ASC";
        $data =  $this->runRequest($sql)->fetchAll();
        for ($i = 0 ; $i < count($data) ; $i++){
            $sql = "SELECT name FROM core_main_menus WHERE id=(SELECT id_main_menu FROM core_main_sub_menus WHERE id=?)";
            $tmp = $this->runRequest($sql, array($data[$i]["id_sub_menu"]))->fetch();
            $data[$i]["main_menu"] = $tmp[0];
        }
        return $data;
    }
    
    public function get($id){
        $sql = "SELECT * FROM core_main_menu_items WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getForSubMenu($id_sub_menu){
        $sql = "SELECT * FROM core_main_menu_items WHERE id_sub_menu=? ORDER BY display_order ASC;";
        return $this->runRequest($sql, array($id_sub_menu))->fetchAll();
    }
    
    public function set($id, $name, $id_sub_menu, $id_space, $display_order){
        if ( $id > 0 ){
            $sql = "UPDATE core_main_menu_items SET name=?, id_sub_menu=?, id_space=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_sub_menu, $id_space, $display_order, $id));
            return $id;
        }
        else{
            $sql = "INSERT INTO core_main_menu_items (name, id_sub_menu, id_space, display_order) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($name, $id_sub_menu, $id_space, $display_order));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function haveAllSingleItem($list){
        
        foreach($list as $submenu){
            $sql = "SELECT id FROM core_main_menu_items WHERE id_sub_menu=?";
            $req = $this->runRequest($sql, array($submenu["id"]));
            if ($req->rowCount() > 1){
                return false;
            }
        }
        return true;
    }
    
    public function getSpacesFromSingleItemList($list){
        $items = array();
        foreach( $list as $submenu ){
            $sql = "SELECT * FROM core_spaces WHERE id=(SELECT id_space FROM core_main_menu_items WHERE id_sub_menu=?)";
            $data = $this->runRequest($sql, array($submenu["id"]))->fetch();
            $items[] = $data;
            
        }
        return $items;
    }
    
    public function getSpacesFromSubMenu($id_submenu){
        $sql = "SELECT id_space FROM core_main_menu_items WHERE id_sub_menu=? ORDER BY display_order ASC";
        $list = $this->runRequest($sql, array($id_submenu))->fetchAll();
        
        $items = array();
        foreach( $list as $id_space ){
            $sql = "SELECT * FROM core_spaces WHERE id=?";
            $data = $this->runRequest($sql, array($id_space[0]))->fetch();
            $items[] = $data;
        }

        return $items;
        
        //$sql = "SELECT * FROM core_spaces WHERE id IN (SELECT id_space FROM core_main_menu_items WHERE id_sub_menu=? ORDER BY display_order ASC)";
        //return $this->runRequest($sql, array($id_submenu))->fetchAll();
    }
    
    public function delete($id){
        $sql = "DELETE FROM core_main_menu_items WHERE id=?";
        $this->runRequest($sql, array($id));
    }  

}
