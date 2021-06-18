<?php

require_once 'Framework/Routing.php';

class MailerRouting extends Routing{
    
    public function listRoutes(){
        
        // config
        $this->addRoute("mailerconfigadmin", "mailerconfigadmin", "mailerconfigadmin", "index");
        $this->addRoute("mailerconfig", "mailerconfig", "mailerconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("mailer", "mailer", "mailer", "index", array("id_space"), array(""));
        $this->addRoute("mailersend", "mailersend", "mailer", "send", array("id_space"), array(""));
    }
}
