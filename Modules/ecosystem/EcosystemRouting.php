<?php

require_once 'Framework/Routing.php';

class EcosystemRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("ecosystemconfig", "ecosystemconfig", "ecosystemconfig", "index");
        
        // sites
        $this->addRoute("ecsites", "ecsites", "ecsites", "index");
        $this->addRoute("ecsitesedit", "ecsitesedit", "ecsites", "edit",  array("id"), array(""));
        $this->addRoute("ecsitesdelete", "ecsitesdelete", "ecsites", "delete",  array("id"), array(""));
        $this->addRoute("ecsitesusers", "ecsitesusers", "ecsites", "users",  array("id"), array(""));
        $this->addRoute("ecsitesuserquery", "ecsitesuserquery", "ecsites", "usersquery");

        // belonging
        $this->addRoute("ecbelongings", "ecbelongings", "ecbelongings", "index");
        $this->addRoute("ecbelongingsedit", "ecbelongingsedit", "ecbelongings", "edit",  array("id"), array(""));
        $this->addRoute("ecbelongingsdelete", "ecbelongingsdelete", "ecbelongings", "delete",  array("id"), array(""));
        
        // units
        $this->addRoute("ecunits", "ecunits", "ecunits", "index");
        $this->addRoute("ecunitsedit", "ecunitsedit", "ecunits", "edit",  array("id"), array(""));
        $this->addRoute("ecunitsdelete", "ecunitsdelete", "ecunits", "delete",  array("id"), array(""));
        
        // users
        $this->addRoute("ecactiveusers", "ecactiveusers", "ecusers", "active");
        $this->addRoute("ecunactiveusers", "ecunactiveusers", "ecusers", "unactive");
        $this->addRoute("ecusersedit", "ecusersedit", "ecusers", "edit",  array("id"), array(""));
        $this->addRoute("ecusersdelete", "ecusersdelete", "ecusers", "delete",  array("id"), array(""));
        
        // export
        $this->addRoute("ecexportresponsible", "ecexportresponsible", "ecusers", "exportresp");
        
    }
}
