<?php

require_once 'Framework/Routing.php';

class DatabaseRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("databaseconfigadmin", "databaseconfigadmin", "databaseconfigadmin", "index");
        $this->addRoute("databaseconfig", "databaseconfig", "databaseconfig", "index", array("id_space"), array(""));

        $this->addRoute("databaseconfiginfo", "databaseconfiginfo", "databaseconfig", "info", array("id_space", "id"), array("", ""));
        $this->addRoute("databaseconfigclasses", "databaseconfigclasses", "databaseconfig", "classes", array("id_space", "id_database", "id_class"), array("", "", ""));
        $this->addRoute("databaseconfigviews", "databaseconfigviews", "databaseconfig", "views", array("id_space", "id_database", "id_view"), array("", "", ""));
        $this->addRoute("databaseconfigmenu", "databaseconfigmenu", "databaseconfig", "menu", array("id_space", "id_database"), array("", ""));
        $this->addRoute("databaseconfigtranslate", "databaseconfigtranslate", "databaseconfig", "translate", array("id_space", "id_database"), array("", ""));
        $this->addRoute("databaseconfigpreview", "databaseconfigpreview", "databaseconfig", "preview", array("id_space", "id_database"), array("", ""));
        $this->addRoute("databaseconfiginstall", "databaseconfiginstall", "databaseconfig", "install", array("id_space", "id_database"), array("", ""));
        
        // add home
        $this->addRoute("database", "database", "database", "index", array("id_space"), array(""));
        
        // views
        $this->addRoute("databaseview", "databaseview", "databaseview", "index", array("id_space", "id_database"), array("", ""));
        $this->addRoute("databaseviewview", "databaseviewview", "databaseview", "view", array("id_space", "id_database", "id_table"), array("", "", ""));
        $this->addRoute("databaseviewform", "databaseviewform", "databaseview", "form", array("id_space", "id_database", "id_table"), array("", "", ""));
        
       
    }
}
