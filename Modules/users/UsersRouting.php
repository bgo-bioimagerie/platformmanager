<?php

require_once 'Framework/Routing.php';

class UsersRouting extends Routing
{
    public function listRoutes()
    {
        // config
        $this->addRoute("usersconfig", "usersconfig", "usersconfig", "index", array("id_space"), array(""));

        // providers example routes
        $this->addRoute("usersmyaccount", "usersmyaccount", "useraccount", "index");
    }
}
