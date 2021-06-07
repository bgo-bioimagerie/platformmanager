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
            if(getenv('DEBUG') == '1') {
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
        }
        return self::$parameters;
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
