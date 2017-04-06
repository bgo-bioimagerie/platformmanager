<?php

require_once 'Framework/Routing.php';

class SeekRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("seekconfig", "seekconfig", "seekconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("seek", "seek", "seek", "index", array("id_space"), array(""));
        
    }
}
