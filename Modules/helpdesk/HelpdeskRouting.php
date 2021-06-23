<?php

require_once 'Framework/Routing.php';

class HelpdeskRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/helpdesk/[i:id_space]', 'helpdesk/helpdesk/index', 'helpdesk_index');
    }
    public function listRoutes(){
    }
} 
