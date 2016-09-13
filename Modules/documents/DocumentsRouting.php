<?php

require_once 'Framework/Routing.php';

class DocumentsRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("documentsconfigadmin", "documentsconfigadmin", "documentsconfigadmin", "index");
        $this->addRoute("documentsconfig", "documentsconfig", "documentsconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("documents", "documents", "documents", "index", array("id_space"), array(""));
        
        $this->addRoute("documentsedit", "documentsedit", "documents", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("documentsopen", "documentsopen", "documents", "open", array("id_space", "id"), array("", ""));
        $this->addRoute("documentsdelete", "documentsdelete", "documents", "delete", array("id_space", "id"), array("", ""));
        
    }
}
