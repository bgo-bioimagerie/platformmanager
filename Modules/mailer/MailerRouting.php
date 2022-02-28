<?php

require_once 'Framework/Routing.php';

class MailerRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/core/tiles/[i:id_space]/module/mailer/notifs', 'mailer/mailer/notifs', 'mailer_notifs');
        $router->map('GET', '/mailer/[i:id_space]/delete/[i:id]', 'mailer/mailer/delete', 'mailer_delete');
    }
    
    public function listRoutes(){
        
        // config
        // $this->addRoute("mailerconfigadmin", "mailerconfigadmin", "mailerconfigadmin", "index");
        $this->addRoute("mailerconfig", "mailerconfig", "mailerconfig", "index", array("id_space"), array(""));

        
        // add here the module routes
        $this->addRoute("mailer", "mailer", "mailer", "index", array("id_space"), array(""));
        $this->addRoute("mailersend", "mailersend", "mailer", "send", array("id_space"), array(""));
    }
}
