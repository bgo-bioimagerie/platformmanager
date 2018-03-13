<?php

require_once 'Framework/Model.php';

class CoreMainSubMenu extends Model {

    public function __construct() {
        $this->tableName = "core_main_sub_menus";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("id_main_menu", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->primaryKey = "id";
    }
    
    public function getName($id){
        $sql = "SELECT name FROM core_main_sub_menus WHERE id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req[0];
    }
    
    public function getMainMenu($id){
        $sql = "SELECT id_main_menu FROM core_main_sub_menus WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }      
        return 0;
    }
    
    public function getMainMenuName($id){
        $sql = "SELECT name FROM core_main_menus WHERE id=(SELECT id_main_menu FROM core_main_sub_menus WHERE id=?)";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }      
        return "";
    }
    
    public function getFirstIdx(){
        $sql = "SELECT id FROM core_main_sub_menus ORDER BY id";
        $req = $this->runRequest($sql);
        if ( $req->rowCount() > 0 ){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }
    
    public function getForList(){
        $ids = array();
        $names = array();
        $sql = "SELECT * FROM core_main_sub_menus ORDER BY name";
        $data = $this->runRequest($sql)->fetchAll();
        foreach ($data as $d){
            $ids[] = $d["id"];
            $names[] = $d["name"];
        }
        return array( "names" => $names, "ids" => $ids);
    }

    public function get($id) {
        $sql = "SELECT * FROM core_main_sub_menus WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAll() {
        $sql = "SELECT core_main_sub_menus.* , core_main_menus.name as main_menu "
                . "FROM core_main_sub_menus "
                . "INNER JOIN core_main_menus ON core_main_sub_menus.id_main_menu = core_main_menus.id "
                . "ORDER BY core_main_sub_menus.name ASC";
        return $this->runRequest($sql)->fetchAll();
    }

    public function getForMenu($id_main_menu) {
        $sql = "SELECT * FROM core_main_sub_menus WHERE id_main_menu=? ORDER BY display_order ASC;";
        $data = $this->runRequest($sql, array($id_main_menu))->fetchAll();
        for($i = 0 ; $i < count($data) ; $i++){
            $sql = "SELECT id_space FROM core_main_menu_items WHERE id_sub_menu=?";
            $req = $this->runRequest($sql, array($data[$i]["id"]));
            if ( $req->rowCount() == 1 ){
                $d = $req->fetch();
                $data[$i]["link"] = "corespace/" . $d[0]; 
            }
            else if ( $req->rowCount() > 0 ){
                $data[$i]["link"] = "coretile/" . $data[$i]["id"];
            }
            else{
                $data[$i]["link"] = "";
            }
        }
        return $data;
    }

    public function set($id, $name, $id_main_menu, $display_order) {
        if ($id > 0) {
            $sql = "UPDATE core_main_sub_menus SET name=?, id_main_menu=?, display_order=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_main_menu, $display_order, $id));
        } else {
            $sql = "INSERT INTO core_main_sub_menus (name, id_main_menu, display_order) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $id_main_menu, $display_order));
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM core_main_sub_menus WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
