<?php

require_once 'Framework/Routing.php';

class StatisticsRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("statisticsconfig", "statisticsconfig", "statisticsconfig", "index", array("id_space"), array(""));
        $this->addRoute("statisticsconfigadmin", "statisticsconfigadmin", "statisticsconfigadmin", "index");
        
        // add here the module routes
        $this->addRoute("statistics", "statistics", "statisticslist", "index", array("id_space"), array(""));
        
        // balance
        
        
    }
}
