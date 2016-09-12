<?php

require_once 'Framework/Routing.php';

class DocumentsRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("documentsconfigadmin", "documentsconfigadmin", "documentsconfigadmin", "index");
        $this->addRoute("documentsconfig", "documentsconfig", "documentsconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("documents", "documents", "documents", "index", array("id_space"), array(""));
        
    }
}
