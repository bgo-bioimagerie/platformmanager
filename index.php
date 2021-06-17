<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'Framework/Configuration.php';
// settings
ini_set('display_errors', 0); 
//error_reporting(E_ALL); 
error_reporting(0);

$sessionPath = dirname(__FILE__).'/tmp/';
session_save_path( $sessionPath );

require 'Framework/Router.php';

if(Configuration::get('sentry_dsn', '')) {
    \Sentry\init(['dsn' => Configuration::get('sentry_dsn')]);
}


$router = new Router();
$router->routerRequest();
debug_print_backtrace();
