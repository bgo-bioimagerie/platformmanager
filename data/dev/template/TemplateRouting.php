<?php

require_once 'Framework/Routing.php';

class TemplateRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("templateconfig", "templateconfig", "templateconfig", "index", array("id_space"), array(""));

        // providers example routes
        $this->addRoute("providers", "providers", "providers", "index", array("id_space"), array(""));
        $this->addRoute("provideredit", "provideredit", "providers", "edit", array("id_space", 'id'), array("", ""));
        $this->addRoute("providerdelete", "providerdelete", "providers", "delete", array("id_space", "id"), array("", ""));
        
        // add here the module routes
        $this->addRoute("template", "template", "providers", "index", array("id_space"), array(""));
        
        // ...
        
    }
}
