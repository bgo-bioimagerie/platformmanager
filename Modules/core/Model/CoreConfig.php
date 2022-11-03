<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Class defining the config model
 *
 * @author Sylvain Prigent
 */
class CoreConfig extends Model
{
    public static int $ONEXPIRE_INACTIVATE = 0;
    public static int $ONEXPIRE_REMOVE = 1;

    public function __construct()
    {
        $this->tableName = "core_config";
    }

    private static $params = null;

    /**
     * Create the table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `core_config` (
        `keyname` varchar(30) NOT NULL DEFAULT '',
        `value` text NOT NULL,
        `id_space` int(11) NOT NULL DEFAULT 0
        );";

        $this->runRequest($sql);

        $sqlCol = "SHOW COLUMNS FROM `core_config` WHERE Field='id';";
        $reqCol = $this->runRequest($sqlCol);

        if ($reqCol->rowCount() > 0) {
            $sql2 = "ALTER TABLE core_config CHANGE id `keyname` varchar(30) NOT NULL;";
            $this->runRequest($sql2);
            $sql3 = "alter table core_config drop primary key;";
            $this->runRequest($sql3);
        }

        $this->addColumn('core_config', 'id_space', 'int(11)', 0);
    }

    /**
     * Create the application contact
     *
     * @return PDOStatement
     */
    public function createDefaultConfig()
    {
        $adminEmail = Configuration::get('admin_email', 'admin@pfm.org');
        $this->setParam("admin_email", $adminEmail);
        $this->setParam("user_desactivate", "0");
        $this->setParam("logo", "Theme/logo.jpg");
        $this->setParam("home_title", "Database");
        $this->setParam("home_message", "");
    }

    /**
     * Check if a config key exists
     */
    public function isKey($key, $id_space)
    {
        $this->loadParams($id_space);
        if (isset(self::$params[$id_space]) && isset(self::$params[$id_space][$key])) {
            return true;
        }
        return false;
    }

    /**
     * Load config parameters
     * @param string $id_space
     */
    private function loadParams($id_space)
    {
        if (isset(self::$params[$id_space])) {
            return;
        }
        Configuration::getLogger()->debug('load config', ['space' => $id_space]);
        $sql = "SELECT * FROM core_config where id_space=?";
        $configParams = $this->runRequest($sql, array($id_space));
        $dbconfig = $configParams->fetchAll();
        foreach ($dbconfig as $param) {
            if (!isset(self::$params[$param["id_space"]])) {
                self::$params[$param["id_space"]] = [];
            }
            self::$params[$param["id_space"]][$param["keyname"]] = $param["value"];
        }
    }

    public static function clearParams()
    {
        self::$params = null;
    }

    /**
     * Add a config parameter
     * @param string $key
     * @param string $value
     */
    public function addParam($key, $value, $id_space=0)
    {
        $sql = "INSERT INTO core_config (keyname, value, id_space) VALUES(?,?,?)";
        $this->runRequest($sql, array($key, $value, $id_space));
        if (!isset(self::$params[$id_space])) {
            self::$params[$id_space] = [];
        }
        self::$params[$id_space][$key] = $value;
    }

    /**
     * Update a parameter
     * @param string $key
     * @param string $value
     */
    public function updateParam($key, $value, $id_space=0)
    {
        $sql = "update core_config set value=?  where keyname=? AND id_space=?";
        $this->runRequest($sql, array($value, $key, $id_space));
        $this->loadParams($id_space);
        if (!isset(self::$params[$id_space])) {
            self::$params[$id_space] = [];
        }
        self::$params[$id_space][$key] = $value;
    }

    /**
     * Get a parameter
     * @param string $key
     * @param mixed $default default value
     * @return string value
     */
    public function getParam($key, $default="")
    {
        return $this->getParamSpace($key, 0, $default);
    }

   /**
     * Get a parameter
     * @param string $key
     * @return string value
     */
    public function getParamSpace($key, $id_space, $default="")
    {
        $this->loadParams($id_space);
        if (!isset(self::$params[$id_space])) {
            return $default;
        }
        if (!isset(self::$params[$id_space][$key])) {
            return $default;
        }
        return self::$params[$id_space][$key];
    }

    /**
     * Set a parameter (add if not exists, otherwise update)
     * @param string $key
     * @param string $value
     */
    public function setParam($key, $value, $id_space=0)
    {
        if ($this->isKey($key, $id_space)) {
            $this->updateParam($key, $value, $id_space);
        } else {
            $this->addParam($key, $value, $id_space);
        }
    }

    public function initParam($key, $value)
    {
        if (!$this->isKey($key, 0)) {
            $this->addParam($key, $value);
        }
    }

    public function getExpirationChoices($lang)
    {
        $choices = array();
        $choicesid = array();
        $choicesid[] = 1;
        $choices[] = CoreTranslator::never($lang);
        $choicesid[] = 2;
        $choices[] = CoreTranslator::contract_ends($lang);
        $choicesid[] = 3;
        $choices[] = CoreTranslator::does_not_login_for_n_year(1, $lang);
        $choicesid[] = 4;
        $choices[] = CoreTranslator::does_not_login_for_n_year(2, $lang);
        $choicesid[] = 5;
        $choices[] = CoreTranslator::does_not_login_for_n_year(3, $lang);
        $choicesid[] = 6;
        $choices[] = CoreTranslator::contract_ends_or_does_not_login_for_1_year($lang);
        return ['ids' => $choicesid, 'labels' => $choices];
    }
}
