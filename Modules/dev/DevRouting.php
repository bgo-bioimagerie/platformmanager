<?php

require_once 'Framework/Routing.php';

class DevRouting extends Routing{
    
    public function listRouts(){
           
        // config
        $this->addRoute("devconfigadmin", "devconfigadmin", "devconfigadmin", "index");
        
        // add here the module routes
        $this->addRoute("dev", "dev", "dev", "index");
        
    }
}
