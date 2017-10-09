<?php

require_once 'Framework/Routing.php';

class ComRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("comconfigadmin", "comconfigadmin", "comconfigadmin", "index");
        $this->addRoute("comconfig", "comconfig", "comconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("com", "com", "com", "index", array("id_space"), array(""));
        $this->addRoute("comtile", "comtile", "comtile", "index", array("id_space"), array(""));
        $this->addRoute("comtileedit", "comtileedit", "comtile", "edit", array("id_space"), array(""));
        
        // news
        $this->addRoute("comnews", "comnews", "comnews", "index", array("id_space"), array(""));
        $this->addRoute("comnewsedit", "comnewsedit", "comnews", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("comnewsdelete", "comnewsdelete", "comnews", "delete", array("id_space", "id"), array("", ""));
        
    }
}
