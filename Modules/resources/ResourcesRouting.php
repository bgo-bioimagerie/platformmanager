<?php

require_once 'Framework/Routing.php';

class ResourcesRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("resourcesconfig", "resourcesconfig", "resourcesconfig", "index");
        
        // add here the module routes
        $this->addRoute("resources", "resources", "resources", "index");
        $this->addRoute("resourcesedit", "resourcesedit", "resources", "edit", array("id"), array(""));
        $this->addRoute("resourcesevents", "resourcesevents", "resources", "events", array("id"), array(""));
        
        $this->addRoute("reareas", "reareas", "reareas", "index");
        $this->addRoute("reareasedit", "reareasedit", "reareas", "edit", array("id"), array(""));
        $this->addRoute("reareasdelete", "reareasdelete", "reareas", "delete", array("id"), array(""));
        
        $this->addRoute("recategories", "recategories", "recategories", "index");
        $this->addRoute("recategoriesedit", "recategoriesedit", "recategories", "edit", array("id"), array(""));
        $this->addRoute("recategoriesdelete", "recategoriesdelete", "recategories", "delete", array("id"), array(""));
        
    }
}
