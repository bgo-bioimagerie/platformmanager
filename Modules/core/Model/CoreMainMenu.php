<?php

require_once 'Framework/Model.php';

class CoreMainMenu extends Model {

    public function __construct() {
        $this->tableName = "core_main_menus";
        //$this->setColumnsInfo("id", "int(11)", "");
        //$this->setColumnsInfo("name", "varchar(100)", "");
        //$this->setColumnsInfo("display_order", "int(11)", 0);
        //$this->primaryKey = "id";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_main_menus` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            `display_order` int NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    } 

    public function getFirstIdx(){
        $sql = "SELECT id FROM core_main_menus ORDER BY id";
        $req = $this->runRequest($sql);
        if ( $req->rowCount() > 0 ){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }
    
    public function getFirstSubMenu($id){
        $sql = "SELECT id FROM core_main_sub_menus WHERE id_main_menu=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function get($id){
        $sql = "SELECT * FROM core_main_menus WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForList(){
        $ids = array();
        $names = array();
        $sql = "SELECT * FROM core_main_menus ORDER BY name";
        $data = $this->runRequest($sql)->fetchAll();
        foreach ($data as $d){
            $ids[] = $d["id"];
            $names[] = $d["name"];
        }
        return array( "names" => $names, "ids" => $ids);
    }
    
    public function getAll(): array{
        $sql = "SELECT * FROM core_main_menus ORDER BY display_order ASC;";
        return $this->runRequest($sql)->fetchAll();
    }
    
    public function set($id, $name, $display_order){
        if ( $id > 0 ){
            $sql = "UPDATE core_main_menus SET name=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $display_order, $id));
            return $id;
        }
        else{
            $sql = "INSERT INTO core_main_menus (name, display_order) VALUES (?,?)";
            $this->runRequest($sql, array($name, $display_order));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function delete($id){
        $sql = "DELETE FROM core_main_menus WHERE id=?";
        $this->runRequest($sql, array($id));
    }  

}
