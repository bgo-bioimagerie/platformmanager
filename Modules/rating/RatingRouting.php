<?php

require_once 'Framework/Routing.php';

class RatingRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/rating/[i:id_space]', 'rating/rating/campaigns', 'rating_campaign_index');
        $router->map('GET|POST', '/ratingconfig/[i:id_space]', 'rating/ratingconfig/index', 'rating_config');
        $router->map('GET', '/rating/[i:id_space]/evaluations/[a:module]/[i:resource]', 'rating/rating/ratings', 'rating_evaluations');

        $router->map('GET|POST', '/rating/[i:id_space]/campaign/[i:id_campaign]', 'rating/rating/campaign', 'rating_campaign');
        $router->map('GET', '/rating/[i:id_space]/campaign/[i:id_campaign]/rate', 'rating/rating/survey', 'rating_survey');
    }
    
    /**
     * Empty function to implement interface
     */
    public function listRoutes(){
    }
}
?>