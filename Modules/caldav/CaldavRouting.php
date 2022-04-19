<?php

require_once 'Framework/Routing.php';

class CaldavRouting extends Routing{

    public function routes($router) {
        $router->map('OPTIONS', '/caldav/[i:id_space]', 'caldav/caldav/discovery', 'caldav_discovery');
        $router->map('PROPFIND', '/caldav/[i:id_space]', 'caldav/caldav/propfind', 'caldav_propfind');
        $router->map('REPORT', '/caldav/[i:id_space]', 'caldav/caldav/report', 'caldav_report');
    }
    
    public function listRoutes(){
    }
}