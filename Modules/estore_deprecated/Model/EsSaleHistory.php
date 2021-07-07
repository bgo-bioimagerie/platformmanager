<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreUser.php';

class EsSaleHistory extends Model {

    public function __construct() {
        $this->tableName = "es_sale_history";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_sale", "int(11)", 0);
        $this->setColumnsInfo("id_status", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->primaryKey = "id";
    }

    public function getHistory($id_space, $id_sale) {
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_space=? AND deleted=0 ORDER BY date ASC";
        return $this->runRequest($sql, array($id_sale, $id_space))->fetchAll();
    }
    
    public function getHistoryStatus($id_space, $id_sale, $id_status){
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_status=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_sale, $id_status, $id_space))->fetch();
    }
    
    public function set($id_space, $id_sale, $id_status, $id_user, $date) {
        if (!$this->exists($id_space, $id_sale, $id_status)) {
            $sql = 'INSERT INTO es_sale_history (id_sale, id_status, id_user, date, id_space) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_sale, $id_status, $id_user, $date, $id_space));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE es_sale_history SET id_user=?, date=? WHERE id_sale=? AND id_status=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($id_user, $date, $id_sale, $id_status, $id_space));
        }
    }
    
    public function exists($id_space, $id_sale, $id_status){
        $sql = "SELECT id FROM es_sale_history WHERE id_sale=? AND id_status=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_sale, $id_status, $id_space));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }
    
    public function getEntered($id_space, $id_sale){
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_status=1 AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_sale, $id_space));
        if ( $req->rowCount() > 0){
            $data = $req->fetch();
            $modelUser = new CoreUser();
            $data["username"] = $modelUser->getUserFUllName($data["id_user"]);
            return $data;
        }
        return array("username" => "unknown", "date" => "unknown");
    }
    
    public function getDelivery($id_space, $id_sale){
        $sql = "SELECT * FROM es_sale_history WHERE id_sale=? AND id_status=4 AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_sale, $id_space));
        if ( $req->rowCount() > 0){
            $data = $req->fetch();
            $modelUser = new CoreUser();
            $data["username"] = $modelUser->getUserFUllName($data["id_user"]);
            return $data;
        }
        return array("username" => "unknown", "date" => "unknown");
    }
   

    public function delete($id_space, $id) {
        $sql  = "UPDATE es_sale_history SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM es_sale_history WHERE id=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
