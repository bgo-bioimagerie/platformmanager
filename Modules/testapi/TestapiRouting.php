<?php

require_once 'Framework/Routing.php';

class TestapiRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        $this->addRoute("testapiconfigadmin", "testapiconfigadmin", "testapiconfigadmin", "index");
        $this->addRoute("testapiconfig", "testapiconfig", "testapiconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("testapi", "testapi", "testapi", "index", array("id_space", "id"), array("", ""));
        $this->addRoute("testquery", "testquery", "testapi", "testquery", array(), array());
        
        // API
        $this->addRoute("apitestquery", "apitestquery", "testapi", "test", array(), array(), true);
    }
}
