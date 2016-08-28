<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SePurchase extends Model {

        public function __construct() {

        $this->tableName = "se_purchase";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("comment", "varchar(255)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "");
        $this->setColumnsInfo("doc_url", "varchar(250)", "");
        $this->primaryKey = "id";
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM se_purchase WHERE id_space=? ORDER BY date DESC;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function getItem($id) {
        $sql = "SELECT * FROM se_purchase WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        return $req->fetch();
    }
    
    public function set($id, $comment, $id_space, $date){
        if ($this->ispurchase($id)){
            $sql = "UPDATE se_purchase SET comment=?, id_space=?, date=? WHERE id=?";
            $this->runRequest($sql, array($comment, $id_space, $date, $id));
            return $id;
        }
        else{
            $sql = "INSERT INTO se_purchase (comment, id_space, date) VALUES (?,?,?)";
            $this->runRequest($sql, array($comment, $id_space, $date));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function ispurchase($id){
        $sql = "SELECT * FROM se_purchase WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }

    public function delete($id){
        $sql = "DELETE FROM se_purchase WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
