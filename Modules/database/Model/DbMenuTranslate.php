<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbMenuTranslate extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_menus_translate";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_menu_item", "int(11)", 0);
        $this->setColumnsInfo("lang", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function getName($id_menu_item, $lang) {
        $sql = "SELECT name FROM db_menus_translate WHERE id_menu_item=? AND lang=?";
        return $this->runRequest($sql, array($id_menu_item, $lang))->fetch();
    }

    public function add($id_menu_item, $lang, $name) {
        $sql = "INSERT INTO db_menus_translate (id_menu_item, lang, name) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_menu_item, $lang, $name));
    }

    public function setAll($id_menu_item, $lang, $name) {

        for ($i = 0; $i < count($id_menu_item); $i++) {
            $sql = "DELETE FROM db_menus_translate WHERE id_menu_item=? AND lang=?";
            $this->runRequest($sql, array($id_menu_item[$i], $lang));

            $sql2 = "INSERT INTO db_menus_translate (id_menu_item, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql2, array($id_menu_item[$i], $lang, $name[$i]));
        }
    }

    public function set($id_menu_item, $lang, $name) {

        if ($this->exists($id_menu_item, $lang)) {
            $sql = "UPDATE db_menus_translate SET lang=?, name=? WHERE id_menu_item=?";
            $this->runRequest($sql, array($lang, $name, $id_menu_item));
        } else {
            $sql = "INSERT INTO db_menus_translate (id_menu_item, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_menu_item, $lang, $name));
        }
    }

    public function get($id_menu_item, $name) {
        $sql = "SELECT name FROM db_menus_translate WHERE id_menu_item=? AND name=?";
        return $this->runRequest($sql, array($id_menu_item, $name))->fetch();
    }

    public function getAllForForm($id_menu_item) {
        $sql = "SELECT * FROM db_menus_translate WHERE id_menu_item =? ORDER BY lang";
        $data = $this->runRequest($sql, array($id_menu_item))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["lang"];
        }

        //echo "langs query <br/>";
        //print_r($names); echo "<br/>";
        //print_r($ids); echo "<br/>";
        return array("names" => $names, "ids" => $ids);
    }

    public function exists($id_menu_item, $lang) {
        $sql = "SELECT * from db_menus_translate WHERE id_menu_item=? AND lang=?";
        $req = $this->runRequest($sql, array($id_menu_item, $lang));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getTranslation($id_menu, $lang){
        $sql = "SELECT name FROM db_menus_translate WHERE id_menu_item=? AND lang=?";
        $name = $this->runRequest($sql, array($id_menu, $lang))->fetch();
        return $name[0];
    }
    
    public function getTranslations($id_menus, $lang) {

        $names = array();
        foreach ($id_menus as $m) {
            $sql = "SELECT name FROM db_menus_translate WHERE id_menu_item=? AND lang=?";
            $name = $this->runRequest($sql, array($m, $lang))->fetch();
            $names[] = $name[0];
        }
        return $names;
    }

    /**
     * Delete a database
     * @param number $id ID
     */
    public function delete($id) {
        $sql = "DELETE FROM db_menus_translate WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
