<?php

require_once 'Session.php';

/**
 * Class defining a request.
 *
 * @author Sylvain Prigent
 */
class Request {

    /**
     * Table containg the request parameters
     */
    private $parameters;

    /**
     * Session Objet associated to the request
     */
    private $session;

    /**
     * Constructor
     *
     * @param array $parameters
     *        	Parameters of the request
     */
    public function __construct($parameters, $createSession = true) {
        $this->parameters = $parameters;
        if($createSession){
            $this->session = new Session ();
        }
    }

    /**
     * Return the session object
     *
     * @return Session Session Objet
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * Return true if a parameter exists in the request and is not empty
     *
     * @param string $name
     *        	Name of the parameter
     * @return bool True if the parameter exists and is not empty
     */
    public function isParameterNotEmpty($name) {
        return (isset($this->parameters [$name]) && $this->parameters [$name] != "");
    }

    /**
     * Return true if a parameter exists in the request
     *
     * @param string $name
     *        	Name of the parameter
     * @return bool True if the parameter exists and is not empty
     */
    public function isParameter($name) {
        return (isset($this->parameters [$name]) );
    }

    /**
     * Return the value of a parameter
     *
     * @param string $name
     *        	Name of the parameter
     * @return string Value of the parameter
     * @throws Exception If the parameter does not exist in the request
     */
    public function getParameter($name) {
        if ($this->isParameter($name)) {
            return $this->parameters [$name];
        } else {
            throw new Exception("Parameter '$name' is not in the request");
        }
    }

    /**
     * Return the value of a parameter
     *
     * @param string $name
     *        	Name of the parameter
     * @return string Value of the parameter, or en empty string if the parameter is not set
     */
    public function getParameterNoException($name) {
        if ($this->isParameter($name)) {
            return $this->parameters [$name];
        } else {
            return '';
        }
    }

}
