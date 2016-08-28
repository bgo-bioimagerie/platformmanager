<?php

require_once 'Framework/Routing.php';

class TemplateRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("templateconfigadmin", "templateconfigadmin", "templateconfigadmin", "index");
        $this->addRoute("templateconfig", "templateconfig", "templateconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("template", "template", "template", "index", array("id_space"), array(""));
        
    }
}
