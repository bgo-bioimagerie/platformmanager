<?php

require_once 'Framework/Routing.php';

class TransferRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        $this->addRoute("transferconfig", "transferconfig", "transferconfig", "index", array("id_space"), array(""));

        // providers example routes
        $this->addRoute("transfersimplefiledownload", "transfersimplefiledownload", "transfersimplefile", "download");
      
    }
}
