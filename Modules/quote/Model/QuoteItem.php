<?php

require_once 'Framework/Model.php';

class QuoteItem extends Model {

    public function __construct() {
        $this->tableName = "qo_quoteitems";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_quote", "int(11)", 0);
        $this->setColumnsInfo("id_content", "int(11)", 0);
        $this->setColumnsInfo("module", "varchar(255)", "");
        $this->setColumnsInfo("quantity", "varchar(255)", "");
        $this->setColumnsInfo("comment", "TEXT", "");
        $this->primaryKey = "id";
    }

    public function getList($id_space) {

        $names = array();
        $ids = array();

        $modelBooking = new ResourceInfo();
        $resources = $modelBooking->getBySpace($id_space);
        foreach ($resources as $r) {
            $names[] = $r["name"];
            $ids[] = 'booking_' . $r["id"];
        }

        $modelServies = new SeService();
        $services = $modelServies->getBySpace($id_space);
        foreach ($services as $s) {
            $names[] = $s["name"];
            $ids[] = 'services_' . $s["id"];
        }

        return array("names" => $names, "ids" => $ids);
    }

    public function get($id) {
        $sql = "SELECT * FROM qo_quoteitems WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAll($id_quote) {
        $sql = "SELECT * FROM qo_quoteitems WHERE id_quote=?";
        return $this->runRequest($sql, array($id_quote))->fetchAll();
    }

    public function setItem($id, $id_quote, $id_content, $module, $quantity, $comment) {
        if ($id == 0) {
            $sql = 'INSERT INTO qo_quoteitems (id_quote, id_content, module, quantity, comment) VALUES (?,?,?,?,?)';
            $this->runRequest($sql, array($id_quote, $id_content, $module, $quantity, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE qo_quoteitems SET id_quote=?, id_content=?, module=?, quantity=?, comment=? WHERE id=?";
            $this->runRequest($sql, array($id_quote, $id_content, $module, $quantity, $comment, $id));
            return $id;
        }
    }

    public function delete($id){
        $sql = "DELETE FROM qo_quoteitems WHERE id=?";
        $this->runRequest($sql, array($id));
    }
}
