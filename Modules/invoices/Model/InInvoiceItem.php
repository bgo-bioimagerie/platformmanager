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
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_invoice", "int(11)", 0);
        $this->setColumnsInfo("module", "varchar(200)", 0);
        $this->setColumnsInfo("controller", "varchar(200)", 0);
        $this->setColumnsInfo("content", "text", "");
        $this->setColumnsInfo("details", "text", "");
        $this->setColumnsInfo("total_ht", "varchar(50)", "0");
        $this->primaryKey = "id";
    }

    public function getInvoiceItems($id_invoice) {
        $sql = "SELECT id FROM in_invoice_item WHERE id_invoice=?";
        return $this->runRequest($sql, array($id_invoice))->fetchAll();
    }

    public function getItem($id) {
        $sql = "SELECT * FROM in_invoice_item WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForInvoice($id_invoice){
        $sql = "SELECT * FROM in_invoice_item WHERE id_invoice=?";
        return $this->runRequest($sql, array($id_invoice))->fetch();
    }

    public function setItem($id, $id_invoice, $module, $controller, $content, $details, $total_ht) {
        if (!$this->isItem($id)) {
            $sql = "INSERT INTO in_invoice_item (id_invoice, module, controller, content, details, total_ht) VALUES (?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht));
        } else {
            $sql = "UPDATE in_invoice_item SET id_invoice=?, module=?, controller=?, content=?, details=?, total_ht=? WHERE id=?";
            $this->runRequest($sql, array($id_invoice, $module, $controller, $content, $details, $total_ht, $id));
        }
    }
    
    public function setItemContent($id_invoice, $content){
        $sql = "UPDATE in_invoice_item SET content=? WHERE id_invoice=?";
        $this->runRequest($sql, array($content, $id_invoice));
    }

    public function editItemContent($id, $content, $total_ht) {
        $sql = "UPDATE in_invoice_item SET content=?, total_ht=? WHERE id=?";
        $this->runRequest($sql, array($content, $total_ht, $id));
    }

    public function isItem($id) {
        $sql = "SELECT id FROM in_invoice_item WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteForInvoice($id_invoice){
        $sql = "DELETE FROM in_invoice_item WHERE id_invoice=?";
        $this->runRequest($sql, array($id_invoice));
    }

}
