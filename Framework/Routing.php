<?php

/**
 * Abstract class Routing
 * Model storing the routing informations of a module 
 *
 * @author Sylvain Prigent
 */
abstract class Routing {

    protected $identifiers;
    protected $urls;
    protected $controllers;
    protected $actions;
    protected $gets;
    protected $getsRegexp;

    /**
     * Construct
     */
    public function __construct() {
        /*
          $this->identifiers = array();
          $this->url = array();
          $this->controller = array();
          $this->action = array();
          $this->gets = array();
          $this->getsRegexp = array();
         */
    }

    /**
     * 
     */
    public abstract function listRouts();

    /**
     * Add one route
     * 
     * @param string $identifier Rout identifier
     * @param string $url Rout URL
     * @param string $controller Controller name
     * @param string $action Controller action
     * @param string $gets List of gets variables
     * @param string $getsRegexp List of gets variables type as regrexp
     * 
     */
    public function addRoute($identifier, $url, $controller, $action, $gets = array(), $getsRegexp = array()) {
        $this->identifiers[] = $identifier;
        $this->urls[] = $url;
        $this->controllers[] = $controller;
        $this->actions[] = $action;
        $this->gets[] = $gets;
        $this->getsRegexp[] = $getsRegexp;
    }

    /**
     * 
     * @return type
     */
    public function count() {
        //echo "count urls = " . count($this->identifiers) . "<br/>";
        return count($this->identifiers);
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getIdentifier($i) {
        return $this->identifiers[$i];
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getUrl($i) {
        return $this->urls[$i];
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getController($i) {
        return $this->controllers[$i];
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getAction($i) {
        return $this->actions[$i];
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getGet($i) {
        return $this->gets[$i];
    }

    /**
     * 
     * @param type $i
     * @return type
     */
    public function getGetRegexp($i) {
        return $this->getsRegexp[$i];
    }

}
