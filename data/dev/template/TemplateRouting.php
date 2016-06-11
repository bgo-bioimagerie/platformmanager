<?php

require_once 'Framework/Routing.php';

class TemplateRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("templateconfig", "templateconfig", "templateconfig", "index");
        
        // add here the module routes
        $this->addRoute("template", "template", "template", "index");
        
    }
}
