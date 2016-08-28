<?php

require_once 'Framework/Routing.php';

class CoreRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("coreconfigadmin", "coreconfigadmin", "coreconfigadmin", "index");
        $this->addRoute("coreldapconfig", "coreldapconfig", "coreldapconfig", "index");
        
        // connection
        $this->addRoute("coreconnection", "coreconnection", "coreconnection", "index");
        $this->addRoute("corelogin", "corelogin", "coreconnection", "login");
        $this->addRoute("corelogout", "corelogout", "coreconnection", "logout");
        // tiles
        $this->addRoute("coretiles", "coretiles", "coretiles", "index");
        // Modules manager
        $this->addRoute("coremodulesmanager", "coremodulesmanager", "coremodulesmanager", "index");
        $this->addRoute("coremodulesmanagerconfig", "coremodulesmanagerconfig", "coremodulesmanager", "config", array("id"), array(""));
        
        // Users
        $this->addRoute("coreusers", "coreusers", "coreusers", "index");
        $this->addRoute("coreusersedit", "coreusersedit", "coreusers", "edit", array("id"), array(""));
        $this->addRoute("coreusersdelete", "coreusersdelete", "coreusers", "delete", array("id"), array(""));
        // settings
        $this->addRoute("coremyaccount", "coremyaccount", "coreusers", "myaccount");
        $this->addRoute("coresettings", "coresettings", "coresettings", "index");
        
        // spaces
        $this->addRoute("corespace", "corespace", "corespace", "view", array("id_space"), array(""));
        $this->addRoute("spaceconfig", "spaceconfig", "corespace", "config", array("id_space"), array(""));
        $this->addRoute("spaceconfiguser", "spaceconfiguser", "corespace", "configusers", array("id_space"), array(""));
        $this->addRoute("spaceconfigmodule", "spaceconfigmodule", "corespace", "configmodule", array("id_space", "name_module"), array("", ""));
        
        
        
        $this->addRoute("spaceconfigdeleteuser", "spaceconfigdeleteuser", "corespace", "configdeleteuser", array("id_space", "id_user"), array("", ""));
        
        
        
        // spaces admin
        $this->addRoute("spaceadmin", "spaceadmin", "corespaceadmin", "index");
        $this->addRoute("spaceadminedit", "spaceadminedit", "corespaceadmin", "edit", array("id"), array(""));
        $this->addRoute("spaceadmindelete", "spaceadmindelete", "corespaceadmin", "delete", array("id"), array(""));
        
        
        // menus
        $this->addRoute("coremenus", "coremenus", "coremenus", "index");
        $this->addRoute("coremenusitems", "coremenusitems", "coremenus", "items");
        $this->addRoute("coremenusitemedit", "coremenusitemedit", "coremenus", "itemedit", array("id"), array(""));
        $this->addRoute("coremenusitemdelete", "coremenusitemdelete", "coremenus", "itemdelete", array("id"), array(""));
        
        
    }
}
