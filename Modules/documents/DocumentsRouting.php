<?php

require_once 'Framework/Routing.php';

class DocumentsRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        //$this->addRoute("documentsconfigadmin", "documentsconfigadmin", "documentsconfigadmin", "index");
        $this->addRoute("documentsconfig", "documentsconfig", "documentsconfig", "index", array("id_space"), array(""));

        // add here the module routes
        $this->addRoute("documents", "documents", "documentslist", "index", array("id_space"), array(""));
        
        $this->addRoute("documentsedit", "documentsedit", "documentslist", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("documentsopen", "documentsopen", "documentslist", "open", array("id_space", "id"), array("", ""));
        $this->addRoute("documentsdelete", "documentsdelete", "documentslist", "delete", array("id_space", "id"), array("", ""));
        
    }
}
