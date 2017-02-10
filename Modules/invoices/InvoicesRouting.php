<?php

require_once 'Framework/Routing.php';

class InvoicesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("invoicesconfigadmin", "invoicesconfigadmin", "invoicesconfigadmin", "index");
        $this->addRoute("invoicesconfig", "invoicesconfig", "invoicesconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("invoices", "invoices", "invoices", "index", array("id_space", "year"), array("", ""));
        $this->addRoute("invoiceedit", "invoiceedit", "invoices", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("invoiceinfo", "invoiceinfo", "invoices", "info", array("id_space", "id"), array("", ""));
        $this->addRoute("invoicedelete", "invoicedelete", "invoices", "delete", array("id_space", "id"), array("", ""));
        
        
    }
}
