<?php

require_once 'Framework/Model.php';

class QuoteItem extends Model
{
    public function __construct()
    {
        $this->tableName = "qo_quoteitems";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_quote", "int(11)", 0);
        $this->setColumnsInfo("id_content", "int(11)", 0);
        $this->setColumnsInfo("module", "varchar(255)", "");
        $this->setColumnsInfo("quantity", "varchar(255)", "");
        $this->setColumnsInfo("comment", "TEXT", "");
        $this->primaryKey = "id";
    }

    public function getList($idSpace)
    {
        $names = array();
        $ids = array();

        $modelBooking = new ResourceInfo();
        $resources = $modelBooking->getBySpace($idSpace);
        foreach ($resources as $r) {
            $names[] = $r["name"];
            $ids[] = 'booking_' . $r["id"];
        }

        $modelServies = new SeService();
        $services = $modelServies->getBySpace($idSpace);
        foreach ($services as $s) {
            $names[] = $s["name"];
            $ids[] = 'services_' . $s["id"];
        }

        return array("names" => $names, "ids" => $ids);
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM qo_quoteitems WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getAll($idSpace, $id_quote)
    {
        $sql = "SELECT * FROM qo_quoteitems WHERE id_quote=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_quote, $idSpace))->fetchAll();
    }

    public function setItem($idSpace, $id, $id_quote, $id_content, $module, $quantity, $comment)
    {
        if (!$id) {
            $sql = 'INSERT INTO qo_quoteitems (id_quote, id_content, module, quantity, comment, id_space) VALUES (?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_quote, $id_content, $module, $quantity, $comment, $idSpace));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE qo_quoteitems SET id_quote=?, id_content=?, module=?, quantity=?, comment=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_quote, $id_content, $module, $quantity, $comment, $id, $idSpace));
            return $id;
        }
    }

    public function delete($idSpace, $id)
    {
        $sql = "DELETE FROM qo_quoteitems WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
