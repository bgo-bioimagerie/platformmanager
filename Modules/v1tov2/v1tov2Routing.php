<?php

require_once 'Framework/Routing.php';

class v1tov2Routing extends Routing{
    
    public function listRouts(){
        
        $this->addRoute("v1tov2", "v1tov2", "v1tov2", "index", array(), array());
        
    }
}
