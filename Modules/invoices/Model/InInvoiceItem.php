<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class InInvoiceItem extends Model
{
    /**
     * Create the site table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "in_invoice_item";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_invoice", "int(11)", 0);
        $this->setColumnsInfo("module", "varchar(200)", 0);
        $this->setColumnsInfo("controller", "varchar(200)", 0);
        $this->setColumnsInfo("content", "text", "");
        $this->setColumnsInfo("details", "text", "");
        $this->setColumnsInfo("total_ht", "varchar(50)", "0");
        $this->primaryKey = "id";
    }

    public function getInvoiceItems($idSpace, $id_invoice)
    {
        $sql = "SELECT id FROM in_invoice_item WHERE id_invoice=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_invoice, $idSpace))->fetchAll();
    }

    public function getItem($idSpace, $id)
    {
        $sql = "SELECT * FROM in_invoice_item WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getForInvoice($idSpace, $id_invoice)
    {
        $sql = "SELECT * FROM in_invoice_item WHERE id_invoice=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_invoice, $idSpace))->fetch();
    }

    public function setItem($idSpace, $id, $id_invoice, $module, $controller, $content, $details, $total_ht)
    {
        if (!$this->isItem($idSpace, $id)) {
            $sql = "INSERT INTO in_invoice_item (id_invoice, module, controller, content, details, total_ht, id_space) VALUES (?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht, $idSpace));
        } else {
            $sql = "UPDATE in_invoice_item SET id_invoice=?, module=?, controller=?, content=?, details=?, total_ht=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht, $id, $idSpace));
        }
    }

    public function setItemContent($idSpace, $id_invoice, $content)
    {
        $sql = "UPDATE in_invoice_item SET content=? WHERE id_invoice=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($content, $id_invoice, $idSpace));
    }

    public function editItemContent($idSpace, $id, $content, $total_ht)
    {
        $sql = "UPDATE in_invoice_item SET content=?, total_ht=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($content, $total_ht, $id, $idSpace));
    }

    public function isItem($idSpace, $id)
    {
        $sql = "SELECT id FROM in_invoice_item WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function deleteForInvoice($idSpace, $id_invoice)
    {
        $sql = "UPDATE in_invoice_item SET deleted=1,deleted_at=NOW() WHERE id_invoice=? AND id_space=?";
        $this->runRequest($sql, array($id_invoice, $idSpace));
    }
}
