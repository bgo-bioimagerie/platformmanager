<?php

require_once 'Framework/Configuration.php';

/**
 * Class that manage the LDAP configuration file
 * 
 * @author Sylvain Prigent
 */
class CoreLdapConfiguration
{
    /** Configuration parameters table */
    private static $parameters;

    /**
     * Return the value of a configuration parameter
     * 
     * @param string $name Name of the parameter
     * @param string $defaultValue Value returned by default
     * @return string Value of the configuration parameter
     */
    public static function get($name, $defaultValue = null)
    {
        $parameters = self::getParameters();
        if (isset($parameters[$name])) {
            $value = $parameters[$name];
        }
        else {
            $value = $defaultValue;
        }
        return $value;
    }

    /**
     * Return the table of the parametres by loading the configuration file.
     * 
     * @return array Table containing the configuration parameters
     * @throws Exception If the configuration file cannot be located
     */
    private static function getParameters()
    {
        if (self::$parameters == null) {

        	$urlFile = self::getConfigFile();
            if (!file_exists($urlFile)) {
                Configuration::getLogger()->error('[ldap] no config file found');
                throw new PfmFileException("Unable to find the configuration file", 404);
            }
            else {
                self::$parameters = parse_ini_file($urlFile);
            }
            self::override();
        }
        return self::$parameters;
    }
    
    public static function getConfigFile(){
    	return "Config/ldap.ini";
    }

/**
     * Override some config with env variables
     */
    private static function override() {

        // Backward compatibility
        if (self::$parameters['ldapAdress'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapAdress instead of ldap_host');
            self::$parameters['ldap_host'] = self::$parameters['ldapAdress'];
        }
        if (self::$parameters['ldapPort'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapPort instead of ldap_port');
            self::$parameters['ldap_port'] = intval(self::$parameters['ldapPort']);
        }
        if (self::$parameters['ldapId'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapId instead of ldap_user');
            self::$parameters['ldap_user'] = self::$parameters['ldapId'];
        }
        if (self::$parameters['ldapPwd'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapPwd instead of ldap_password');
            self::$parameters['ldap_password'] = self::$parameters['ldapPwd'];
        }
        if (self::$parameters['ldapBaseDN'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapBaseDN instead of ldap_search_dn');
            self::$parameters['ldap_search_dn'] = self::$parameters['ldapBaseDN'];
        }
        if (self::$parameters['ldapUseTls'] != null) {
            Configuration::getLogger()->debug('[deprecated] using ldapUseTls instead of ldap_tls');
            self::$parameters['ldap_tls'] = (
                self::$parameters['ldapUseTls'] == "TRUE" ||
                self::$parameters['ldapUseTls'] == "1" ||
                self::$parameters['ldapUseTls'] == 1
                ) ? true :  false;
        }

        // Check env vars
        if(getenv('PFM_LDAP_HOST')) {
            self::$parameters['ldap_host'] = getenv('PFM_LDAP_HOST');
        }
        if(getenv('PFM_LDAP_PORT')) {
            self::$parameters['ldap_port']= intval(getenv('PFM_LDAP_PORT'));
        }
        if(getenv('PFM_LDAP_USER')) {
            self::$parameters['ldap_user'] = getenv('PFM_LDAP_USER');
        }
        if(getenv('PFM_LDAP_PASSWORD')) {
            self::$parameters['ldap_password'] = getenv('PFM_LDAP_PASSWORD');
        }
        if(getenv('PFM_LDAP_DN')) {
            self::$parameters['ldap_dn'] = getenv('PFM_LDAP_DN');
        }
        if(getenv('PFM_LDAP_SEARCH_DN')) {
            self::$parameters['ldap_search_dn'] = getenv('PFM_LDAP_SEARCH_DN');
        }
        if(getenv('PFM_LDAP_TLS')) {  // 1 or 0
            self::$parameters['ldap_tls'] = getenv('PFM_LDAP_TLS') == "1" ? true : false;
        }


        if(!isset(self::$parameters['ldap_port']) || self::$parameters['ldap_port'] == "") {
            if (isset(self::$parameters['ldap_tls']) && self::$parameters['ldap_tls']) {
                self::$parameters['ldap_port'] = 636;
            } else {
                self::$parameters['ldap_port'] = 389;
            }
        }

    }


}



