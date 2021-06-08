<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbAttributTranslate extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "db_attributs_translate";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_attribut", "int(11)", 0);
        $this->setColumnsInfo("lang", "varchar(3)", "");
        $this->setColumnsInfo("name", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function getName($id_attribut, $lang) {
        $sql = "SELECT name FROM db_attributs_translate WHERE id_attribut=? AND lang=?";
        return $this->runRequest($sql, array($id_attribut, $lang))->fetch();
    }

    public function add($id_attribut, $lang, $name) {
        $sql = "INSERT INTO db_attributs_translate (id_attribut, lang, name) VALUES (?,?,?)";
        $this->runRequest($sql, array($id_attribut, $lang, $name));
    }

    public function setAll($id_attribut, $lang, $name) {

        //echo "id_attribut = " . $id_attribut . "<br/>";

        for ($i = 0; $i < count($id_attribut); $i++) {
            $sql = "DELETE FROM db_attributs_translate WHERE id_attribut=? AND lang=?";
            $this->runRequest($sql, array($id_attribut[$i], $lang));

            $sql1 = "INSERT INTO db_attributs_translate (id_attribut, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql1, array($id_attribut[$i], $lang, $name[$i]));
        }
    }

    public function set($id_attribut, $lang, $name) {

        if ($this->exists($id_attribut, $lang)) {
            $sql = "UPDATE db_attributs_translate SET lang=?, name=? WHERE id_attribut=?";
            $this->runRequest($sql, array($lang, $name, $id_attribut));
        } else {
            $sql = "INSERT INTO db_attributs_translate (id_attribut, lang, name) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_attribut, $lang, $name));
        }
    }

    public function get($id_attribut, $name) {
        $sql = "SELECT name FROM db_attributs_translate WHERE id_attribut=? AND name=?";
        return $this->runRequest($sql, array($id_attribut, $name))->fetch();
    }

    public function getAllForForm($id_attribut) {
        $sql = "SELECT * FROM db_attributs_translate WHERE id_attribut =? ORDER BY lang";
        $data = $this->runRequest($sql, array($id_attribut))->fetchAll();
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

    public function getTranslations($id_atts, $lang) {

        $names = array();
        foreach ($id_atts as $m) {
            $sql = "SELECT name from db_attributs_translate WHERE id_attribut=? AND lang=?";
            $name = $this->runRequest($sql, array($m, $lang))->fetch();
            $names[] = $name[0];
        }
        return $names;
    }

    public function exists($id_attribut, $lang) {
        $sql = "SELECT * from db_attributs_translate WHERE id_attribut=? AND lang=?";
        $req = $this->runRequest($sql, array($id_attribut, $lang));
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
        $sql = "DELETE FROM db_attributs_translate WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
