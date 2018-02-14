<?php

require_once 'Framework/Model.php';

class EcConvention extends Model {

    public function __construct() {
        $this->tableName = "ec_convention";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_user", "int(11)", 0);
        $this->setColumnsInfo("url", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getUrl($id_space, $id_user) {
        $sql = "SELECT url FROM ec_convention WHERE id_space=? AND id_user=?" ;
        $req = $this->runRequest($sql, array($id_space, $id_user));
        if( $req->rowCount() > 0 ){
            $url = $req->fetch();
            return $url[0];
        }
        return "";
    }

    public function set($id_space, $id_user, $url) {
        $id = $this->exists($id_space, $id_user);
        if ($id == 0) {
            $sql = 'INSERT INTO ec_convention (id_space, id_user, url) VALUES (?,?,?)';
            $this->runRequest($sql, array($id_space, $id_user, $url));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE ec_convention SET id_space=?, id_user=?, url=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $id_user, $url, $id));
            return $id;
        }
    }

    public function exists($id_space, $id_user){
        $sql = "SELECT id FROM ec_convention WHERE id_space=? AND id_user=?";
        $req = $this->runRequest($sql, array($id_space, $id_user));
        if( $req->rowCount() > 0 ){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM ec_convention WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
