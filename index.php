<?php


ini_set('display_errors', 1); 
error_reporting(E_ALL); 
//error_reporting(0);
require 'Framework/Router.php';

$router = new Router();
$router->routerRequest();


