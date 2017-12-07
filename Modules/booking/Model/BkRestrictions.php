<?php

require_once 'Framework/Model.php';

class BkRestrictions extends Model {

    public function __construct() {
        $this->tableName = "bk_restrictions";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("maxbookingperday", "int(11)", 0);
        $this->setColumnsInfo("bookingdelayusercanedit", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function init($id_space) {
        $sql = "SELECT id FROM re_info WHERE id_space=?";
        $resources = $this->runRequest($sql, array($id_space))->fetchAll();

        foreach ($resources as $r) {
            $this->add($r["id"], 0, 0);
        }
    }
    
    public function getBookingDelayUserCanEdit($id_resource){
        $sql = "SELECT bookingdelayusercanedit FROM bk_restrictions WHERE id_resource=?";
        $tmp = $this->runRequest($sql, array($id_resource))->fetch();
        return $tmp[0];
    }

    public function getMaxBookingPerDay($id_resource){
        $sql = "SELECT maxbookingperday FROM bk_restrictions WHERE id_resource=?";
        $tmp = $this->runRequest($sql, array($id_resource))->fetch();
        return $tmp[0];
    }
    
    public function getForSpace($id_space) {
        $sql = "SELECT * FROM bk_restrictions WHERE id_resource IN (SELECT id FROM re_info WHERE id_space=?)";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM bk_restrictions WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function add($id_resource, $maxbookingperday, $bookingdelayusercanedit) {
        $id = $this->exists($id_resource);
        if ($id == 0) {
            $sql = "INSERT INTO bk_restrictions (id_resource, maxbookingperday, bookingdelayusercanedit) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_resource, $maxbookingperday, $bookingdelayusercanedit));
        }
    }
    
    public function set($id, $maxbookingperday, $bookingdelayusercanedit) {
        $sql = "UPDATE bk_restrictions SET maxbookingperday=?, bookingdelayusercanedit=? WHERE id=?";
        $this->runRequest($sql, array($maxbookingperday, $bookingdelayusercanedit, $id));
    }

    public function exists($id_resource) {
        $sql = "SELECT id FROM bk_restrictions WHERE id_resource=?";
        $req = $this->runRequest($sql, array($id_resource));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM bk_restrictions WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
