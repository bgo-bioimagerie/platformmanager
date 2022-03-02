<?php

require_once 'Framework/Routing.php';

class StatisticsRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        $this->addRoute("statisticsconfig", "statisticsconfig", "statisticsconfig", "index", array("id_space"), array(""));
        
        // add here the module routes
        $this->addRoute("statistics", "statistics", "statisticslist", "index", array("id_space"), array(""));
        
        // balance
        $this->addRoute("statisticsglobal", "statisticsglobal", "statisticsglobal", "index", array("id_space"), array(""));
        
        
        
    }
}
