<?php

/**
 * Class modeling a session.
 * Use the PHP variable $_SESSION.
 * 
 * @author Sylvain Prigent
 */
class Session {

    /**
     * Constructor.
     * Start or restor the session
     */
    public function __construct() {
        if(!isset($_SESSION)){
            session_start();
        }
    }

    /**
     * Destroy the session
     */
    public function destroy() {
        session_destroy();
    }

    /**
     * Add an attribut to the session
     * 
     * @param string $name Name of the attribut
     * @param string $value Value of the attribut
     */
    public function setAttribut($name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * Return true is the attribut exists in the session
     * 
     * @param string $name Name of the attribut
     * @return bool True is the attribut exists and is not empty 
     */
    public function isAttribut($name) {
        return (isset($_SESSION[$name]) && $_SESSION[$name] != "");
    }

    /**
     * Return the value of an attribut
     * 
     * @param string $name Name of the attribut
     * @return string Value of the attribut
     * @throws Exception If the attribut cannot be found in session
     */
    public function getAttribut($name) {
        if ($this->isAttribut($name)) {
            return $_SESSION[$name];
        } else {
            return "";
            //throw new Exception("Cannot find the Attribut '$name' in the session");
        }
    }

}
