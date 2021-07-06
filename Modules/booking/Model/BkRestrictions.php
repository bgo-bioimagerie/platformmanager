<?php

require_once 'Framework/Model.php';

class BkRestrictions extends Model {

    public function __construct() {
        $this->tableName = "bk_restrictions";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_resource", "int(11)", 0);
        $this->setColumnsInfo("maxbookingperday", "int(11)", 0);
        $this->setColumnsInfo("bookingdelayusercanedit", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function init($id_space) {
        $sql = "SELECT id FROM re_info WHERE id_space=?";
        $resources = $this->runRequest($sql, array($id_space))->fetchAll();

        foreach ($resources as $r) {
            $this->add($id_space, $r["id"], 0, 0);
        }
    }
    
    public function getBookingDelayUserCanEdit($id_space, $id_resource){
        $sql = "SELECT bookingdelayusercanedit FROM bk_restrictions WHERE id_resource=? AND deleted=0 AND id_space=?";
        $tmp = $this->runRequest($sql, array($id_resource, $id_space))->fetch();
        return $tmp[0];
    }

    public function getMaxBookingPerDay($id_space, $id_resource){
        $sql = "SELECT maxbookingperday FROM bk_restrictions WHERE id_resource=? AND deleted=0 AND id_space=?";
        $tmp = $this->runRequest($sql, array($id_resource, $id_space))->fetch();
        return $tmp[0];
    }
    
    public function getForSpace($id_space) {
        $sql = "SELECT * FROM bk_restrictions WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM bk_restrictions WHERE id=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function add($id_space, $id_resource, $maxbookingperday, $bookingdelayusercanedit) {
        $id = $this->exists($id_space, $id_resource);
        if (!$id) {
            $sql = "INSERT INTO bk_restrictions (id_resource, maxbookingperday, bookingdelayusercanedit, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $maxbookingperday, $bookingdelayusercanedit, $id_space));
        }
    }
    
    public function set($id_space, $id, $maxbookingperday, $bookingdelayusercanedit) {
        $sql = "UPDATE bk_restrictions SET maxbookingperday=?, bookingdelayusercanedit=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($maxbookingperday, $bookingdelayusercanedit, $id, $id_space));
    }

    public function exists($id_space, $id_resource) {
        $sql = "SELECT id FROM bk_restrictions WHERE id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_space));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE bk_restrictions SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM bk_restrictions WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
