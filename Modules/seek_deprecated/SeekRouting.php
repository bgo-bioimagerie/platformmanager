<?php

require_once 'Framework/Routing.php';

class SeekRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        $this->addRoute("seekconfigadmin", "seekconfigadmin", "seekconfigadmin", "index");
        $this->addRoute("seekconfig", "seekconfig", "seekconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("seek", "seek", "seek", "index", array("id_space"), array(""));
        
        $this->addRoute("seekbookingeditreservation", "seekbookingeditreservation", "seekbooking", "editreservation", array("id_space", "param"), array("", ""));
        $this->addRoute("seekbookingeditreservationquery", "seekbookingeditreservationquery", "seekbooking", "editreservationquery", array("id_space"), array(""));
        $this->addRoute("seekbookingeditreservationdefaultdelete", "seekbookingeditreservationdefaultdelete", "seekbooking", "delete", array("id_space", "id"), array("", ""));
        
    }
}
