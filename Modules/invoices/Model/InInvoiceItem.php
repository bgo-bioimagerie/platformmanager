<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class InInvoiceItem extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "in_invoice_item";

        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_invoice", "int(11)", 0);
        $this->setColumnsInfo("module", "varchar(200)", 0);
        $this->setColumnsInfo("controller", "varchar(200)", 0);
        $this->setColumnsInfo("content", "text", "");
        $this->setColumnsInfo("details", "text", "");
        $this->setColumnsInfo("total_ht", "varchar(50)", "0");
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `in_invoice_item` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_invoice` int NOT NULL DEFAULT 0,
            `module` varchar(200) NOT NULL DEFAULT 0,
            `controller` varchar(200) NOT NULL DEFAULT 0,
            `content` text,
            `details` text,
            `total_ht` varchar(50) NOT NULL DEFAULT 0,
            `id_space` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        )';
        $this->runRequest($sql);
    }

    public function getInvoiceItems($id_space, $id_invoice) {
        $sql = "SELECT id FROM in_invoice_item WHERE id_invoice=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_invoice, $id_space))->fetchAll();
    }

    public function getItem($id_space, $id) {
        $sql = "SELECT * FROM in_invoice_item WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getForInvoice($id_space, $id_invoice){
        $sql = "SELECT * FROM in_invoice_item WHERE id_invoice=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_invoice, $id_space))->fetch();
    }

    public function setItem($id_space ,$id, $id_invoice, $module, $controller, $content, $details, $total_ht) {
        if (!$this->isItem($id_space ,$id)) {
            $sql = "INSERT INTO in_invoice_item (id_invoice, module, controller, content, details, total_ht, id_space) VALUES (?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht, $id_space));
        } else {
            $sql = "UPDATE in_invoice_item SET id_invoice=?, module=?, controller=?, content=?, details=?, total_ht=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht, $id, $id_space));
        }
    }
    
    public function setItemContent($id_space, $id_invoice, $content){
        $sql = "UPDATE in_invoice_item SET content=? WHERE id_invoice=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($content, $id_invoice, $id_space));
    }

    public function editItemContent($id_space, $id, $content, $total_ht) {
        $sql = "UPDATE in_invoice_item SET content=?, total_ht=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($content, $total_ht, $id, $id_space));
    }

    public function isItem($id_space, $id) {
        $sql = "SELECT id FROM in_invoice_item WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteForInvoice($id_space, $id_invoice){
        $sql = "UPDATE in_invoice_item SET deleted=1,deleted_at=NOW() WHERE id_invoice=? AND id_space=?";
        $this->runRequest($sql, array($id_invoice, $id_space));
    }

}
