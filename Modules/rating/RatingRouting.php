<?php

require_once 'Framework/Routing.php';

class CoreRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/rating/[i:id_space]/', 'rating/rating/index', 'rating_index');
        $router->map('POST', '/rating/[i:id_space]/[s:module]/[i:resource]', 'rating/rating/rate', 'rating_rate');
    }
    
    /**
     * Empty function to implement interface
     */
    public function listRoutes(){
    }
}
?>