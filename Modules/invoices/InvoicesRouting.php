<?php

require_once 'Framework/Routing.php';

class InvoicesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("invoicesconfigadmin", "invoicesconfigadmin", "invoicesconfigadmin", "index");
        $this->addRoute("invoicesconfig", "invoicesconfig", "invoicesconfig", "index", array("id_space"), array(""));

        $this->addRoute("invoicepdftemplate", "invoicepdftemplate", "invoicesconfig", "pdftemplate", array("id_space"), array(""));
        $this->addRoute("invoicepdftemplatedelete", "invoicepdftemplatedelete", "invoicesconfig", "pdftemplatedelete", array("id_space", "name"), array("", ""));

        $this->addRoute("invoicesvisas", "invoicesvisas", "invoicesvisa", "index", array("id_space"), array(""));
        $this->addRoute("invoicesvisaedit", "invoicesvisaedit", "invoicesvisa", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("invoicesvisadelete", "invoicesvisadelete", "invoicesvisa", "delete", array("id_space", "id"), array("", ""));
       
        // add here the module routes
        $this->addRoute("invoices", "invoices", "invoiceslist", "index", array("id_space", "sent", "year"), array("", "", ""));
        $this->addRoute("invoicestosend", "invoicestosend", "invoiceslist", "tosend", array("id_space", "year"), array("", ""));
        $this->addRoute("invoicessent", "invoicessent", "invoiceslist", "sent", array("id_space", "year"), array("", ""));
        
        $this->addRoute("invoiceedit", "invoiceedit", "invoiceslist", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("invoiceinfo", "invoiceinfo", "invoiceslist", "info", array("id_space", "id"), array("", ""));
        $this->addRoute("invoicedelete", "invoicedelete", "invoiceslist", "delete", array("id_space", "id"), array("", ""));
        
        // global invoice
        $this->addRoute("invoiceglobal", "invoiceglobal", "invoiceglobal", "index", array("id_space"), array(""));
        $this->addRoute("invoiceglobaledit", "invoiceglobaledit", "invoiceglobal", "editquery", array("id_space", "id"), array("", ""));
        $this->addRoute("invoiceglobalpdf", "invoiceglobalpdf", "invoiceglobal", "pdf", array("id_space", "id", "pdf"), array("", "", ""));
    }
}
