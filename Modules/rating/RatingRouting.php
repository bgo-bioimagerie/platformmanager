<?php

require_once 'Framework/Routing.php';

class RatingRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/rating/[i:id_space]', 'rating/rating/index', 'rating_index');
        $router->map('GET|POST', '/rating/[i:id_space]/[a:module]/[i:resource]', 'rating/rating/rate', 'rating_rate');
        $router->map('GET|POST', '/ratingconfig/[i:id_space]', 'rating/ratingconfig/index', 'rating_config');
        $router->map('GET', '/rating/[i:id_space]/evaluations/[a:module]/[i:resource]', 'rating/rating/ratings', 'rating_evaluations');
    }
    
    /**
     * Empty function to implement interface
     */
    public function listRoutes(){
    }
}
?>