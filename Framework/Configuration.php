<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\TagProcessor;
use Monolog\Formatter\LineFormatter;
use Symfony\Component\Yaml\Yaml;

require_once 'Framework/Errors.php';

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
            $formatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
            $formatter->includeStacktraces(true);
            $streamHandler = new StreamHandler('php://stderr', $level);
            $streamHandler->setFormatter($formatter);
            self::$logger->pushHandler($streamHandler);
            self::$logger->pushProcessor(function ($entry) {
                $user = 'anonymous';
                if(isset($_SESSION['id_user'])) {
                    $user = $_SESSION['id_user'];
                }
                $entry['extra']['user'] = $user;
                if(isset($_SESSION['id_space'])) {
                    $entry['extra']['space'] = $_SESSION['id_space'];
                }
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
     * @return string|int|array Value of the configuration parameter
     */
    public static function get($name, $defaultValue = null) {
        $parameters = self::getParameters();
        if (isset($parameters[$name])) {
            $value = $parameters[$name];
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
    public static function getParameters() {
        if (self::$parameters == null) {
            $urlFile = self::getConfigFile();
            if (!file_exists($urlFile)) {
                Configuration::getLogger()->warning('No configuration file found, using env vars only');
            } else {
                self::$parameters = parse_ini_file($urlFile);
            }
            $yamlConfig = str_replace('.ini', '.yaml', self::getConfigFile());
            if(file_exists($yamlConfig)) {
                $yamlData = Yaml::parseFile($yamlConfig, Yaml::DUMP_OBJECT_AS_MAP);
                if(isset($yamlData['plans'])) {
                    $plans = $yamlData['plans'];
                    if(!isset($yamlData['plans'][0]['flags'])) {
                        $yamlData['plans'][0]['flags'] = [];
                    }
                    for($i=1;$i<count($plans);$i++) {
                            if(!isset($yamlData['plans'][$i]['flags'])) {
                            $yamlData['plans'][$i]['flags'] = [];
                        }
                        $yamlData['plans'][$i]['flags'] = array_merge($yamlData['plans'][$i]['flags'], $yamlData['plans'][$i-1]['flags']);
                    }
                }
                foreach ($yamlData as $key => $value){
                    self::$parameters[$key] = $value;
                }
            }
            self::override();
        }
        return self::$parameters;
    }

    /**
     * Override some config with env variables
     */
    private static function override() {

        if(getenv('MYSQL_ADMIN_LOGIN')) {
            self::$parameters['mysql_admin_login']= getenv('MYSQL_ADMIN_LOGIN');
        }
        if(getenv('MYSQL_ADMIN_PWD')) {
            self::$parameters['mysql_admin_pwd']= getenv('MYSQL_ADMIN_PWD');
        }

        if(getenv('MYSQL_HOST')) {
            self::$parameters['mysql_host']= getenv('MYSQL_HOST');
        }
        if(getenv('MYSQL_DBNAME')) {
            self::$parameters['mysql_dbname']= getenv('MYSQL_DBNAME');
        }
        if(getenv('MYSQL_USER')) {
            self::$parameters['login']= getenv('MYSQL_USER');
        }
        if(getenv('MYSQL_PASS')) {
            self::$parameters['pwd']= getenv('MYSQL_PASS');
        }
        if(getenv('MYSQL_DSN')) {
            self::$parameters['dsn'] = getenv('MYSQL_DSN');
        }
        if(!isset(self::$parameters['dsn'])) {
            try {
                if(!isset(self::$parameters['mysql_host']) || !isset(self::$parameters['mysql_dbname'])) {
                    throw new PfmException('no dns nor MYSQL env vars set for mysql connection', 500);
                }
                self::$parameters['dsn'] = 'mysql:host='.self::$parameters['mysql_host'].';dbname='.self::$parameters['mysql_dbname'].';charset=utf8';
            } catch(Exception $e) {
                throw new PfmException('no dns nor MYSQL env vars set for mysql connection', 500);
            }
        }

        if(getenv('PFM_HEADLESS')) {
            self::$parameters['headless']= intval(getenv('PFM_HEADLESS')) == 1 ? true : false;
        }

        if(!isset(self::$parameters['rootWeb'])) {
            self::$parameters['rootWeb'] = '/';
        }
        if(getenv('PFM_ROOTWEB')) {
            self::$parameters['rootWeb']= getenv('PFM_ROOTWEB');
        }

        if(!isset(self::$parameters['email_regexp'])) {
            self::$parameters['email_regexp'] = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*))@((([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
        }
        if(getenv('EMAIL_REGEXP')) {
            self::$parameters['email_regexp']= getenv('EMAIL_REGEXP');
        }

        if(!isset(self::$parameters['name'])) {
            self::$parameters['name'] = 'Platform-Manager';
        }

        if(!isset(self::$parameters['modules'])) {
            self::$parameters['modules'] = [
                'core',
                'clients',
                'users',
                'resources',
                'services',
                'booking',
                'catalog',
                'invoices',
                'statistics',
                'mailer',
                'documents',
                'antibodies',
                'quote',
                'com'
            ];
        }

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
        if(getenv('DEBUG_INFLUXDB')) {
            self::$parameters['debug_influxdb'] = boolval(getenv('DEBUG_INFLUXDB'));
        }
        if(!isset(self::$parameters['smtp_from'])) {
            self::$parameters['smtp_from'] = 'pfm+donotreply@pfm.org';
        }
        if(getenv('MAIL_FROM')) {
            self::getLogger()->debug('MAIL_FROM is deprecated, use SMTP_FROM');
            self::$parameters['smtp_from'] = getenv('MAIL_FROM');
        }
        if(getenv('SMTP_FROM')) {
            self::$parameters['smtp_from'] = getenv('SMTP_FROM');
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
        if(getenv('PFM_ADMIN_APIKEY')) {
            self::$parameters['admin_apikey'] = getenv('PFM_ADMIN_APIKEY');
        }
        if(getenv('PFM_KEYCLOAK_OIC_SECRET')) {
            self::$parameters['keycloak_oic_secret'] = getenv('PFM_KEYCLOAK_OIC_SECRET');
        }
        if(getenv('PFM_PUBLIC_URL')) {
            self::$parameters['public_url'] = getenv('PFM_PUBLIC_URL');
        }

        if(getenv('PFM_AMQP_HOST')) {
            self::$parameters['amqp_host'] = getenv('PFM_AMQP_HOST');
        }
        if(getenv('PFM_AMQP_PORT')) {
            self::$parameters['amqp_port'] = intval(getenv('PFM_AMQP_PORT'));
        }
        if(getenv('PFM_AMQP_USER')) {
            self::$parameters['amqp_user'] = getenv('PFM_AMQP_USER');
        }
        if(getenv('PFM_AMQP_PASSWORD')) {
            self::$parameters['amqp_password'] = getenv('PFM_AMQP_PASSWORD');
        }

        if(getenv('PFM_INFLUXDB_URL')) {
            self::$parameters['influxdb_url'] = getenv('PFM_INFLUXDB_URL');
        }
        if(getenv('PFM_INFLUXDB_TOKEN')) {
            self::$parameters['influxdb_token'] = getenv('PFM_INFLUXDB_TOKEN');
        }
        if(getenv('PFM_INFLUXDB_ORG')) {
            self::$parameters['influxdb_org'] = getenv('PFM_INFLUXDB_ORG');
        }

        if(getenv('PFM_SENTRY_DSN')) {
            self::$parameters['sentry_dsn'] = getenv('PFM_SENTRY_DSN');
        }

        if(getenv('PFM_OPENID')) {
            self::$parameters['openid'] = explode(',', getenv('PFM_OPENID'));
        }
        if(isset(self::$parameters['openid'])) {
            foreach (self::$parameters['openid'] as $openid) {
                $envopenid = strtoupper($openid);
                if(getenv("PFM_OPENID_".$envopenid."_URL")) {
                    self::$parameters['openid_'.$openid."_url"] = getenv("PFM_OPENID_".$envopenid."_URL");
                }
                if(getenv("PFM_OPENID_".$envopenid."_LOGIN")) {
                    self::$parameters['openid_'.$openid."_login"] = getenv("PFM_OPENID_".$envopenid."_LOGIN");
                }
                if(getenv("PFM_OPENID_".$envopenid."_ICON")) {
                    self::$parameters['openid_'.$openid."_icon"] = getenv("PFM_OPENID_".$envopenid."_ICON");
                }
                if(getenv("PFM_OPENID_".$envopenid."_CLIENT_ID")) {
                    self::$parameters['openid_'.$openid."_client_id"] = getenv("PFM_OPENID_".$envopenid."_CLIENT_ID");
                }
                if(getenv("PFM_OPENID_".$envopenid."_CLIENT_SECRET")) {
                    self::$parameters['openid_'.$openid."_client_secret"] = getenv("PFM_OPENID_".$envopenid."_CLIENT_SECRET");
                }
            }
        }

        if(getenv('PFM_MODULES')) {
            $extras = explode(',', getenv('PFM_MODULES'));
            foreach ($extras as $extra) {
                self::$parameters['modules'][] = $extra;
            }
        }

        if(getenv('PFM_GRAFANA_URL')) {
            self::$parameters['grafana_url'] = getenv('PFM_GRAFANA_URL');
        }

        if(getenv('PFM_GRAFANA_USER')) {
            self::$parameters['grafana_user'] = getenv('PFM_GRAFANA_USER', 'admin');
        }

        if(getenv('PFM_GRAFANA_PASSWORD')) {
            self::$parameters['grafana_password'] = getenv('PFM_GRAFANA_PASSWORD');
        }

        if(getenv('PFM_HELPDESK_EMAIL')) {
            self::$parameters['helpdesk_email'] = getenv('PFM_HELPDESK_EMAIL');
        }
        if(getenv('PFM_HELPDESK_IMAP_SERVER')) {
            self::$parameters['helpdesk_imap_server'] = getenv('PFM_HELPDESK_IMAP_SERVER');
        }
        if(getenv('PFM_HELPDESK_IMAP_PORT')) {
            self::$parameters['helpdesk_imap_port'] = intval(getenv('PFM_HELPDESK_IMAP_PORT'));
        }
        if(getenv('PFM_HELPDESK_IMAP_USER')) {
            self::$parameters['helpdesk_imap_user'] = getenv('PFM_HELPDESK_IMAP_USER');
        }
        if(getenv('PFM_HELPDESK_IMAP_PASSWORD')) {
            self::$parameters['helpdesk_imap_password'] = getenv('PFM_HELPDESK_IMAP_PASSWORD');
        }
        if(getenv('PFM_HELPDESK_IMAP_TLS')) {  // empty string if not tls, else /ssl
            self::$parameters['helpdesk_imap_tls'] = getenv('PFM_HELPDESK_IMAP_TLS');
        }
        if(getenv('PFM_ALLOW_REGISTRATION')) {
            self::$parameters['allow_registration'] = intval(getenv('PFM_ALLOW_REGISTRATION')) ? true : false;
        }
        if(getenv('PFM_JWT_SECRET')) {
            self::$parameters['jwt_secret'] = getenv('PFM_JWT_SECRET');
        }
        if(getenv('PFM_REDIS_HOST')) {
            self::$parameters['redis_host'] = getenv('PFM_REDIS_HOST');
        }
        if(getenv('PFM_REDIS_PORT')) {
            self::$parameters['redis_port'] = intval(getenv('PFM_REDIS_PORT'));
        }

        self::$parameters['shibboleth'] = getenv('PFM_SHIBBOLETH') === "1" ? true : false;

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
