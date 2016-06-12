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
        $this->addRoute("resourceeditevent", "resourceeditevent", "resources", "editevent", array("id_resource", "id_event"), array("", ""));
        
        
        $this->addRoute("reareas", "reareas", "reareas", "index");
        $this->addRoute("reareasedit", "reareasedit", "reareas", "edit", array("id"), array(""));
        $this->addRoute("reareasdelete", "reareasdelete", "reareas", "delete", array("id"), array(""));
        
        $this->addRoute("recategories", "recategories", "recategories", "index");
        $this->addRoute("recategoriesedit", "recategoriesedit", "recategories", "edit", array("id"), array(""));
        $this->addRoute("recategoriesdelete", "recategoriesdelete", "recategories", "delete", array("id"), array(""));
        
        $this->addRoute("restates", "restates", "restates", "index");
        $this->addRoute("restatesedit", "restatesedit", "restates", "edit", array("id"), array(""));
        $this->addRoute("restatesdelete", "restatesdelete", "restates", "delete", array("id"), array(""));
        
        $this->addRoute("reeventtypes", "reeventtypes", "reeventtypes", "index");
        $this->addRoute("reeventtypesedit", "reeventtypesedit", "reeventtypes", "edit", array("id"), array(""));
        $this->addRoute("reeventtypesdelete", "reeventtypesdelete", "reeventtypes", "delete", array("id"), array(""));
        
    }
}
