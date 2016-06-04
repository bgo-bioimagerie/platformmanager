<?php

require_once 'Framework/Routing.php';

class EcosystemRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("ecosystemconfig", "ecosystemconfig", "ecosystemconfig", "index");
        
        // ecosystem
        $this->addRoute("ecsites", "ecsites", "ecsites", "index");
        $this->addRoute("ecsitesedit", "ecsitesedit", "ecsites", "edit",  array("id"), array(""));
        $this->addRoute("ecsitesdelete", "ecsitesdelete", "ecsites", "delete",  array("id"), array(""));
        $this->addRoute("ecsitesusers", "ecsitesusers", "ecsites", "users",  array("id"), array(""));
        
    }
}
