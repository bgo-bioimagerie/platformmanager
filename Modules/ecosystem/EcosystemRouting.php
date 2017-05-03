<?php

require_once 'Framework/Routing.php';

class EcosystemRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("ecosystemconfig", "ecosystemconfig", "ecosystemconfig", "index", array("id"), array(""));
        $this->addRoute("ecosystemconfigadmin", "ecosystemconfigadmin", "ecosystemconfigadmin", "index");
       
        // belonging
        $this->addRoute("ecbelongings", "ecbelongings", "ecbelongings", "index", array("id_space"), array(""));
        $this->addRoute("ecbelongingsedit", "ecbelongingsedit", "ecbelongings", "edit",  array("id_space", "id"), array("", ""));
        $this->addRoute("ecbelongingsdelete", "ecbelongingsdelete", "ecbelongings", "delete",  array("id_space", "id"), array("", ""));
        
        // units
        $this->addRoute("ecunits", "ecunits", "ecunits", "index", array("id_space"), array(""));
        $this->addRoute("ecunitsedit", "ecunitsedit", "ecunits", "edit",  array("id_space","id"), array("", ""));
        $this->addRoute("ecunitsdelete", "ecunitsdelete", "ecunits", "delete",  array("id_space", "id"), array("",""));
        
        // users
        $this->addRoute("ecusers", "ecusers", "ecusers", "index", array("id_space", "letter", "active"), array("", "", ""));
        $this->addRoute("ecactiveusers", "ecactiveusers", "ecusers", "active", array("id_space", "letter"), array("", ""));
        $this->addRoute("ecunactiveusers", "ecunactiveusers", "ecusers", "unactive", array("id_space", "letter"), array("", ""));
        $this->addRoute("ecusersedit", "ecusersedit", "ecusers", "edit",  array("id_space", "id"), array("", ""));
        $this->addRoute("ecusersdelete", "ecusersdelete", "ecusers", "delete",  array("id_space","id"), array("", ""));
        $this->addRoute("ecuserschangepwdp", "ecuserschangepwd", "ecusers", "changepwd", array("id_space", "id"), array("", ""));
        $this->addRoute("ecuserschangepwdq", "ecuserschangepwdq", "ecusers", "changepwdq", array("id_space"), array(""));
        
        // export
        $this->addRoute("ecexportresponsible", "ecexportresponsible", "ecusers", "exportresp", array("id_space"), array(""));
        $this->addRoute("ecexportall", "ecexportall", "ecusers", "exportall", array("id_space"), array(""));
        
    }
}
