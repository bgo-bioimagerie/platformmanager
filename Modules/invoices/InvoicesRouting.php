<?php

require_once 'Framework/Routing.php';

class InvoicesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("invoicesconfigadmin", "invoicesconfigadmin", "invoicesconfigadmin", "index");
        $this->addRoute("invoicesconfig", "invoicesconfig", "invoicesconfig", "index", array("id_space"), array(""));

        // add here the module routes
        $this->addRoute("invoices", "invoices", "invoiceslist", "index", array("id_space", "year"), array("", ""));
        $this->addRoute("invoiceedit", "invoiceedit", "invoiceslist", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("invoiceinfo", "invoiceinfo", "invoiceslist", "info", array("id_space", "id"), array("", ""));
        $this->addRoute("invoicedelete", "invoicedelete", "invoiceslist", "delete", array("id_space", "id"), array("", ""));
        
        
    }
}
