<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\TagProcessor;
use Monolog\Formatter\LineFormatter;

/**
 * Class that manage the configuration parameters
 * 
 * @author Sylvain Prigent
 */
class Configuration {

    /** Configuration parameters table */
    private static $parameters;

    /** Logger */
    private static $logger;

    public static function getLogger() {
        if (self::$logger == null) {
            self::$logger = new Logger('pfm');
            $level = Logger::INFO;
            if(Configuration::get('debug', false)) {
                $level = Logger::DEBUG;
            }
            //$output = "[%datetime%] %channel%.%level_name%: %message%\n";
            $formatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
            $formatter->includeStacktraces(true);
            $streamHandler = new StreamHandler('php://stderr', $level);
            $streamHandler->setFormatter($formatter);
            self::$logger->pushHandler($streamHandler);
            self::$logger->pushProcessor(function ($entry) {
                $user = 'anonymous';
                if(isset($_SESSION["id_user"])) {
                    $user = $_SESSION["id_user"];
                }
                $entry['extra']['user'] = $user;
                return $entry;
            });

        }
        return self::$logger;
    }

    /**
     * Return the value of a configuration parameter
     * 
     * @param string $name Name of the parameter
     * @param string $defaultValue Value returned by default
     * @return string Value of the configuration parameter
     */
    public static function get($name, $defaultValue = null) {
        $parameters = self::getParameters();
        //print_r($parameters);
        if (isset($parameters[$name])) {
            $value = $parameters[$name];
        } else {
            $value = $defaultValue;
        }
        return $value;
    }

        /**
     * Return the value of a configuration parameter
     * 
     * @param string $name Name of the parameter
     * @param string $defaultValue Value returned by default
     * @return boolean Value of the configuration parameter
     */
    public static function getbool($name, $defaultValue = null) {
        $parameters = self::getParameters();
        if (isset($parameters[$name])) {
            $value = $parameters[$name];
            if($value == '1' || $value == 'true' || $value == 'TRUE' || $value == true || $value == 1) {
                return true;
            }
            return false;
        } else {
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
    private static function getParameters() {
        if (self::$parameters == null) {

            $urlFile = self::getConfigFile();
            if (!file_exists($urlFile)) {
                throw new Exception("Unable to find the configuration file");
            } else {
                self::$parameters = parse_ini_file($urlFile);
            }
            self::override();
        }
        return self::$parameters;
    }

    /**
     * Override some config with env variables
     */
    private static function override() {
        self::$parameters['smtp_host'] = getenv('SMTP_HOST', '');
        if(!isset(self::$parameters['smtp_port'])) {
            self::$parameters['smtp_port'] = 25;
        }
        if(getenv('SMTP_PORT')) {
            self::$parameters['smtp_port']= intval(getenv('SMTP_PORT'));
        }
        if(getenv('DEBUG')) {
            self::$parameters['debug'] = boolval(getenv('DEBUG'));
        }
        if(getenv('DEBUG_SQL')) {
            self::$parameters['debug_sql'] = boolval(getenv('DEBUG_SQL'));
        }
        if(!isset(self::$parameters['smtp_from'])) {
            self::$parameters['smtp_from'] = 'donotreply@pfm.org';
        }
        if(getenv('MAIL_FROM')) {
            self::$parameters['smtp_from'] = getenv('MAIL_FROM');
        }
        if(getenv('PFM_ADMIN_USER')) {
            self::$parameters['admin_user'] = getenv('PFM_ADMIN_USER');
        }
        if(getenv('PFM_ADMIN_EMAIL')) {
            self::$parameters['admin_email'] = getenv('PFM_ADMIN_EMAIL');
        }
        if(getenv('PFM_ADMIN_PASSWORD')) {
            self::$parameters['admin_password'] = getenv('PFM_ADMIN_PASSWORD');
        }

        if(getenv('PFM_WEB_URL')) {
            self::$parameters['public_url'] = getenv('PFM_WEB_URL');
        }

        if(getenv('PFM_LDAP_HOST')) {
            self::$parameters['ldap_host'] = getenv('PFM_LDAP_HOST');
        }
        if(getenv('PFM_LDAP_PORT')) {
            self::$parameters['ldap_port']= intval(getenv('PFM_LDAP_PORT'));
        }
        if(getenv('PFM_LDAP_USER')) {
            self::$parameters['ldap_admin'] = getenv('PFM_LDAP_USER');
        }
        if(getenv('PFM_LDAP_PASSWORD')) {
            self::$parameters['ldap_password'] = getenv('PFM_LDAP_PASSWORD');
        }
        if(getenv('PFM_LDAP_BASEDN')) {
            self::$parameters['ldap_dn'] = getenv('PFM_LDAP_BASEDN');
        }
        if(getenv('PFM_LDAP_BASESEARCH')) {
            self::$parameters['ldap_search_dn'] = getenv('PFM_LDAP_BASESEARCH');
        }

        if(getenv('KEYCLOAK_URL')) {
            self::$parameters['keycloak_url'] = getenv('KEYCLOAK_URL');
        }
        if(getenv('KEYCLOAK_ADMIN_USER')) {
            self::$parameters['keycloak_user'] = getenv('KEYCLOAK_ADMIN_USER');
        }
        if(getenv('KEYCLOAK_ADMIN_PASSWORD')) {
            self::$parameters['keycloak_password'] = getenv('KEYCLOAK_ADMIN_PASSWORD');
        }

        if(getenv('PFM_KEYCLOAK_OIC_SECRET')) {
            self::$parameters['keycloak_oic_secret'] = getenv('PFM_KEYCLOAK_OIC_SECRET');
        }





    }

    /**
     * 
     * @return string Configuration file
     */
    public static function getConfigFile() {
        if(getenv("PFM_CONFIG")) {
            return getenv("PFM_CONFIG");
        }
        return "Config/conf.ini";
    }

    /**
     * 
     * @return type Reas the parameter file
     */
    public static function read() {
        return self::getParameters();
    }

    /**
     * Write the param in the config file
     * @param array $config Config file URL
     */
    public static function write(array $config) {
        $configd = var_export($config, true);
        file_put_contents(self::getConfigFile(), "<?php return $configd ;");
    }

}
