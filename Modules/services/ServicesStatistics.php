<?php

require_once 'Framework/Routing.php';

class ServicesStatistics extends Routing{
    
    public function listRouts(){
        
        // statistics
        $this->addRoute("servicesstatisticsorder", "servicesstatisticsorder", "servicesstatisticsorder", "index", array("id_space"), array(""));
        $this->addRoute("servicesstatisticsproject", "servicesstatisticsproject", "servicesstatisticsproject", "index", array("id_space"), array(""));
        
    }
}
