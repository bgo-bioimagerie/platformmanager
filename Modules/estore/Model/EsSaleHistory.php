<?php

require_once 'Framework/Model.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

class EsSaleHistory extends Model {

    public function __construct() {
        $this->tableName = "es_sale_history";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_sale", "int(11)", 0);
        $this->setColumnsInfo("id_status", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->primaryKey = "id";
    }

    public function getHistory($id_sale) {
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? ORDER BY date ASC";
        return $this->runRequest($sql, array($id_sale))->fetchAll();
    }
    
    public function set($id_sale, $id_status, $id_user, $date) {
        if (!$this->exists($id_sale, $id_status)) {
            $sql = 'INSERT INTO es_sale_history (id_sale, id_status, id_user, date) VALUES (?,?,?,?)';
            $this->runRequest($sql, array($id_sale, $id_status, $id_user, $date));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_sale_history SET id_user=?, date=? WHERE id_sale=? AND id_status=?';
            $this->runRequest($sql, array($id_user, $date, $id_sale, $id_status));
        }
    }
    
    public function exists($id_sale, $id_status){
        $sql = "SELECT id FROM es_sale_history WHERE id_sale=? AND id_status=?";
        $req = $this->runRequest($sql, array($id_sale, $id_status));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }
    
    public function getEntered($id_sale){
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_status=1";
        $req = $this->runRequest($sql, array($id_sale));
        if ( $req->rowCount() > 0){
            $data = $req->fetch();
            $modelUser = new EcUser();
            $data["username"] = $modelUser->getUserFUllName($data["id_user"]);
            return $data;
        }
        return array("username" => "unknown", "date" => "unknown");
    }
    
    public function getDelivery($id_sale){
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_status=4";
        $req = $this->runRequest($sql, array($id_sale));
        if ( $req->rowCount() > 0){
            $data = $req->fetch();
            $modelUser = new EcUser();
            $data["username"] = $modelUser->getUserFUllName($data["id_user"]);
            return $data;
        }
        return array("username" => "unknown", "date" => "unknown");
    }
   

    public function delete($id) {
        $sql = "DELETE FROM es_sale_history WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
