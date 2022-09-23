<?php

require_once 'Framework/Routing.php';

class BulletjournalRouting extends Routing{

    public function routes($router) {
        $router->map('DELETE', '/bjnotes/[i:id_space]/[i:id]', 'bulletjournal/bjnotes/deletenote', 'bulletjournal_delete_note');
    }

    
    public function listRoutes(){
        
        // config
        $this->addRoute("bulletjournalconfig", "bulletjournalconfig", "bulletjournalconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("bulletjournal", "bulletjournal", "bulletjournal", "index", array("id_space"), array(""));
        
        $this->addRoute("bjnotes", "bjnotes", "bjnotes", "index", array("id_space", "year", "month"), array("", "", ""));
        $this->addRoute("bjnotesmonthbefore", "bjnotesmonthbefore", "bjnotes", "monthbefore", array("id_space", "year", "month"), array("", "", ""));
        $this->addRoute("bjnotesmonthafter", "bjnotesmonthafter", "bjnotes", "monthafter", array("id_space", "year", "month"), array("", "", ""));
        
        $this->addRoute("bjmigrations", "bjmigrations", "bjmigrations", "index", array("id_space", "year", "month"), array("", "", ""));
        $this->addRoute("bjmigrationsmonthbefore", "bjmigrationsmonthbefore", "bjmigrations", "monthbefore", array("id_space", "year", "month"), array("", "", ""));
        $this->addRoute("bjmigrationsmonthafter", "bjmigrationsmonthafter", "bjmigrations", "monthafter", array("id_space", "year", "month"), array("", "", ""));
        
        $this->addRoute("bjcollections", "bjcollections", "bjcollections", "index", array("id_space", "id_collection"), array("", ""));
        $this->addRoute("bjcollectionsedit", "bjcollectionsedit", "bjcollections", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("bjcollectionsview", "bjcollectionsview", "bjcollections", "view", array("id_space", "id"), array("", ""));
        
        
        // API
        $this->addRoute("bjeditnotequery", "bjeditnotequery", "bjnotes", "editnotequery", array("id_space"), array(""), true);
        $this->addRoute("bjgetnote", "bjgetnote", "bjnotes", "getnote", array("id_space", "id"), array("", ""), true);
 
        $this->addRoute("bjedittask", "bjedittask", "bjnotes", "edittask", array("id_space"), array(""), true);
        $this->addRoute("bjgettask", "bjgettask", "bjnotes", "gettask", array("id_space", "id"), array("", ""), true);
        $this->addRoute("bjclosetask", "bjclosetask", "bjnotes", "closetask", array("id_space", "id"), array("", ""), true);
        $this->addRoute("bjcanceltask", "bjcanceltask", "bjnotes", "canceltask", array("id_space", "id"), array("", ""), true);
        
        $this->addRoute("bjgetevent", "bjgetevent", "bjnotes", "getevent", array("id_space", "id"), array("", ""), true);
        $this->addRoute("bjeditevent", "bjeditevent", "bjnotes", "editevent", array("id_space"), array(""), true);
        
        $this->addRoute("bjmigratetask", "bjmigratetask", "bjmigrations", "migratetask", array("id_space", "id"), array("", ""), true);
        
        
        $this->addRoute("bjnotecollections", "bjnotecollections", "bjcollections", "notecollections", array("id_space","id"), array("", ""), true);
        
        
        
    }
}
