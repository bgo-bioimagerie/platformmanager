<?php

require_once 'Framework/Routing.php';

class CoreRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("coreconfig", "coreconfig", "coreconfig", "index");
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
        $this->addRoute("Coresettings", "Coresettings", "Coresettings", "index");
        
    }
}
