<?php

require_once 'Framework/Routing.php';

class ServicesInvoices extends Routing{
    
    public function listRouts(){
        
        // statistics
        $this->addRoute("servicesprices", "servicesprices", "servicesprices", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceorder", "servicesinvoiceorder", "servicesinvoiceorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesinvoiceproject", "servicesinvoiceproject", "servicesinvoiceproject", "index", array("id_space"), array(""));
        
    }
}
