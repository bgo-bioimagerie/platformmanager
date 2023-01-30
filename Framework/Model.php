<?php

require_once 'Configuration.php';
require_once 'Errors.php';

use DebugBar\DataCollector\PDO\TraceablePDO;

/**
 * Abstract class Model
 * A model define an access to the database
 *
 * @author Sylvain Prigent
 */
abstract class Model
{
    /** PDO object of the database
     */
    private static $bdd;

    /** PDO object of the database
     */
    protected $tableName;
    private $columnsNames;
    private $columnsTypes;
    private $columnsDefaultValue;
    protected $primaryKey;

    // instance model variables
    public int $id=0;

    public static $reconnectErrors = [
        1317 // interrupted
        ,2002 // refused
        ,2006 // gone away
    ];

    /**
     * Check if table already contains a value for column
     *
     * @param string $columnName name of the column
     * @param mixed  $value value to search
     * @throws PfmDbException
     */
    protected function alreadyExists($columnName, $value)
    {
        if (!isset($this->tableName) || empty($this->tableName)) {
            throw new PfmDbException("Table name not defined", 500);
        }
        $table = $this->tableName;
        $sql = "SELECT $columnName FROM $table WHERE $columnName=?";
        $req = $this->runRequest($sql, array($value));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Run a SQL request
     *
     * @param string $sql SQL request
     * @param array $params Request parameters
     * @return PDOStatement Result of the request
     */
    protected function runRequest($sql, $params = null)
    {
        $result = null;
        if (Configuration::get('debug_sql', false)) {
            Configuration::getLogger()->debug('[sql] query', ['sql' => $sql, 'params' => $params]);
        }

        try {
            if ($params == null) {
                $result = self::getDatabase()->query($sql);   // direct query
            } else {
                $result = self::getDatabase()->prepare($sql); // prepared request
                $result->execute($params);
            }
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            Configuration::getLogger()->error('[sql] database error', ['sql' => $sql, 'params' => $params, 'error' => $msg, 'line' => $e->getLine(), 'file' => $e->getFile()]);
            if (Configuration::get('sentry_dsn', '')) {
                \Sentry\captureException($e);
            }
            if (isset($e->errorInfo) && in_array($e->errorInfo[1], self::$reconnectErrors)) {
                // conn error, try to disconnect/reconnect and re-execute
                try {
                    self::resetDatabase();
                    if ($params == null) {
                        $result = self::getDatabase()->query($sql);   // direct query
                    } else {
                        $result = self::getDatabase()->prepare($sql); // prepared request
                        $result->execute($params);
                    }
                } catch (PdoException $e2) {
                    Configuration::getLogger()->error('[sql] connection reset failed', ['error' => $e2]);
                    Configuration::getLogger()->error('[sql] retry error', ['sql' => $sql, 'params' => $params, 'error' => $msg]);
                    if (Configuration::get('sentry_dsn', '')) {
                        \Sentry\captureException($e);
                    }
                }
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            Configuration::getLogger()->error('[sql] other error', ['sql' => $sql, 'params' => $params, 'error' => $msg, 'line' => $e->getLine(), 'file' => $e->getFile()]);
            if (Configuration::get('sentry_dsn', '')) {
                \Sentry\captureException($e);
            }
        }
        if ($result === false) {
            Configuration::getLogger()->debug('[sql] error', ['sql' => $sql, 'params' => $params]);
        }
        return $result;
    }

    public static function resetDatabase()
    {
        self::$bdd = null;
    }

    /**
     * Return an object that connect the database and initialize the connection if needed
     *
     * @return PDO Objet PDO of the database connections
     */
    public static function getDatabase()
    {
        if (self::$bdd === null) {
            // load the database informations
            $dsn = Configuration::get("dsn");
            $login = Configuration::get("login");
            $pwd = Configuration::get("pwd");
            // Create connection
            self::$bdd = new PDO($dsn, $login, $pwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            if (getenv('PFM_MODE') == 'dev' && Configuration::get('debug_sql', false)) {
                self::$bdd = new DebugBar\DataCollector\PDO\TraceablePDO(self::$bdd);
            }
            self::$bdd->exec("SET CHARACTER SET utf8");
        }
        return self::$bdd;
    }

    /**
     *
     * @param string $dsn
     * @param string $login
     * @param string $pwd
     */
    public function setDatabase($dsn, $login, $pwd)
    {
        //echo "dsn = " . $dsn . "<br/>";
        //echo "login = " . $login . "<br/>";
        //echo "pwd = " . $pwd . "<br/>";
        // Create connection
        self::$bdd = new PDO($dsn, $login, $pwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        self::$bdd->exec("SET CHARACTER SET utf8");
    }

    public function checkColumn($tableName, $columnName)
    {
        $sql = "SHOW COLUMNS FROM `" . $tableName . "` WHERE Field=?";
        $pdo = $this->runRequest($sql, array($columnName));
        $isColumn = $pdo->fetch();
        if ($isColumn === false) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $columnType
     * @param mixed $defaultValue
     */
    public function addColumn($tableName, $columnName, $columnType, $defaultValue)
    {
        //$sql = "SHOW COLUMNS FROM `" . $tableName . "` LIKE '" . $columnName . "'";
        //$pdo = $this->runRequest($sql);
        $sql = "SHOW COLUMNS FROM `" . $tableName . "` WHERE Field=?";
        $pdo = $this->runRequest($sql, array($columnName));
        $isColumn = $pdo->fetch();
        if ($isColumn === false) {
            Configuration::getLogger()->debug('[db] add column', ['table' => $tableName, 'col' => $columnName]);
            $sql = "ALTER TABLE `" . $tableName . "` ADD `" . $columnName . "` " . $columnType;
            if ($defaultValue != "") {
                if (is_string($defaultValue)) {
                    if ($defaultValue == 'INSERT_TIMESTAMP') {
                        $sql .= " NOT NULL DEFAULT CURRENT_TIMESTAMP";
                    } elseif ($defaultValue == 'UPDATE_TIMESTAMP') {
                        $sql .= " NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                    } else {
                        $sql .= " NOT NULL DEFAULT '" . $defaultValue . "'";
                    }
                } else {
                    $sql .= " NOT NULL DEFAULT " . $defaultValue;
                }
            }
            $this->runRequest($sql);
        } else {
            Configuration::getLogger()->debug('[db] column already exists, skipping', ['table' => $tableName, 'col' => $columnName]);
        }
    }

    /**
     *
     * @param string $table
     * @return boolean
     */
    public function isTable($table)
    {
        $dsn = Configuration::get("dsn");
        $dsnArray = explode(";", $dsn);
        $dbname = "";
        for ($i = 0; $i < count($dsnArray); $i++) {
            if (strpos($dsnArray[$i], "dbname") !== false) {
                $dbnameArray = explode("=", $dsnArray[$i]);
                $dbname = $dbnameArray[1];
                break;
            }
        }

        $sql = 'SHOW TABLES FROM ' . $dbname . ' LIKE \'' . $table . '\'';
        $req = $this->runRequest($sql);
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function createTableInNotExists($tableName)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $tableName . "`"
                . " (id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY)";
        $this->runRequest($sql);
    }

    public function addPrimary($tableName, $primary)
    {
        $sql = "ALTER TABLE ".$tableName." ADD PRIMARY KEY (".$primary.");";
        $this->runRequest($sql);
    }

    /**
     *
     */
    public function createTable()
    {
        // create database if not exists
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "` (";
        for ($i = 0; $i < count($this->columnsNames); $i++) {
            $sql .= "`" . $this->columnsNames[$i] . "` " . $this->columnsTypes[$i];
            if ($this->columnsDefaultValue[$i] != "") {
                if (is_string($this->columnsDefaultValue[$i])) {
                    if ($this->columnsDefaultValue[$i] == 'INSERT_TIMESTAMP') {
                        $sql .= " NOT NULL DEFAULT CURRENT_TIMESTAMP";
                    } elseif ($this->columnsDefaultValue[$i] == 'UPDATE_TIMESTAMP') {
                        $sql .= " NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                    } else {
                        $sql .= " NOT NULL DEFAULT '" . $this->columnsDefaultValue[$i] . "' ";
                    }
                } else {
                    $sql .= " NOT NULL DEFAULT " .$this->columnsDefaultValue[$i] . " ";
                }
            }
            if ($this->columnsNames[$i] == $this->primaryKey) {
                $sql .= " AUTO_INCREMENT ";
            }

            if ($i != count($this->columnsNames) - 1) {
                $sql .= ", ";
            }
        }
        if ($this->primaryKey != "") {
            $sql .= ", PRIMARY KEY (`" . $this->primaryKey . "`)";
        }
        $sql .= ");";


        //return
        $this->runRequest($sql);

        // add columns if added later
        for ($i = 0; $i < count($this->columnsNames); $i++) {
            $this->addColumn($this->tableName, $this->columnsNames[$i], $this->columnsTypes[$i], $this->columnsDefaultValue[$i]);
        }
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param mixed $value
     */
    public function setColumnsInfo($name, $type, $value)
    {
        $this->columnsNames[] = $name;
        $this->columnsTypes[] = $type;
        $this->columnsDefaultValue[] = $value;
    }

    /**
     *
     * @param type $data
     */
    public function insert($data)
    {
        $sql = "INSERT INTO " . $this->tableName;
        $keyString = "";
        $valuesString = "";
        foreach ($data as $key => $value) {
            $keyString .= $key . ",";
            $valuesString .= "'" . $value . "'" . ",";
        }
        $sql .= " (" . substr($keyString, 0, -1) . ") VALUES (" . substr($valuesString, 0, -1) . ");";

        $this->runRequest($sql);
        $this->getDatabase()->lastInsertId();
    }

    /**
     *
     * @param type $conditions
     * @param type $data
     */
    public function update($conditions, $data)
    {
        $sql = "UPDATE " . $this->tableName . " SET ";
        $condStr = "";
        foreach ($conditions as $k => $v) {
            $condStr .= $k . "=" . $v . " AND";
        }
        $dataStr = "";
        foreach ($data as $k => $v) {
            $dataStr .= $k . "=\"" . $v . "\",";
        }
        $sql .= substr($dataStr, 0, -1) . " WHERE " . substr($condStr, 0, -3);
        $this->runRequest($sql);
    }

    /**
     *
     * @param type $sortEntry
     * @return type
     */
    public function selectAll($sortEntry = "")
    {
        $sql = "SELECT * FROM " . $this->tableName;
        if ($sortEntry != "") {
            $sql .= " ORDER BY " . $sortEntry . " ASC;";
        }
        return $this->runRequest($sql)->fetchAll();
    }

    /**
     *
     * @param type $conditions
     * @param type $columnsToSelect
     * @return type
     */
    public function select($conditions, $columnsToSelect = array())
    {
        $sql = "SELECT ";
        if (count($columnsToSelect) < 1) {
            $sql .= " * ";
        } else {
            $cols = "";
            foreach ($columnsToSelect as $c) {
                $cols .= $c . ",";
            }
            $sql .= substr($cols, 0, -1);
        }
        $sql .= " FROM " . $this->tableName . "WHERE ";
        $conds = "";
        foreach ($conditions as $key => $value) {
            $conds .= " " . $key . "=" . $value . " AND";
        }
        $sql .= substr($conds, 0, -3);
        return $this->runRequest($sql);
    }

    /**
     *
     * @param type $key
     * @param type $value
     * @return boolean
     */
    public function isEntry($key, $value)
    {
        $sql = "SELECT " . $key . " FROM " . $this->tableName . " WHERE " . $key . "=" . $value;
        $req = $this->runRequest($sql);
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     *  Delete all the data from a table
     */
    public function deleteAll()
    {
        $sql = "DELETE FROM " . $this->tableName;
        $this->runRequest($sql);
    }

    public function admGetBy($key, $value, $id_space=0)
    {
        $sql = "SELECT * from $this->tableName WHERE $key=?";
        $params = array($value);
        if ($id_space) {
            $sql .= " AND id_space=?";
            $params[] = $id_space;
        }
        return $this->runRequest($sql, $params)->fetch();
    }

    public function admGetAll($id_space=0)
    {
        $sql = "SELECT * from $this->tableName";
        $params = array();
        if ($id_space) {
            $sql .= " WHERE id_space=?";
            $params = [$id_space];
        }
        return $this->runRequest($sql, $params)->fetchAll();
    }

    public function admCount($id_space=0)
    {
        $sql = "SELECT count(*) as total from $this->tableName where deleted=0";
        $params = array();
        if ($id_space) {
            $sql .= " AND id_space=?";
            $params = [$id_space];
        }
        return $this->runRequest($sql, $params)->fetch();
    }

    /**
     * @param $space space object
     */
    public function createDbAndViews($space)
    {
        $dsn = Configuration::get("dsn", null);
        if (!$dsn) {
            $dsn = Configuration::get("dsn", 'mysql:host='.Configuration::get('mysql_host', 'mysql').';dbname='.Configuration::get('mysql_dbname', 'platform_manager').';charset=utf8');
        }
        $login = Configuration::get("mysql_admin_login", "root");
        $pwd = Configuration::get("mysql_admin_pwd", "platform_manager");
        $pdo = new PDO($dsn, $login, $pwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $pdo->exec("SET CHARACTER SET utf8");

        $spaceID = $space['id'];
        $spaceName = "pfm".$spaceID;
        $sql = "CREATE DATABASE IF NOT EXISTS $spaceName";
        $pdo->query($sql);
        $password = crypt($spaceName, Configuration::get('jwt_secret'));
        $sql = "CREATE USER IF NOT EXISTS '$spaceName'@'%' IDENTIFIED BY '$password'";
        $pdo->query($sql);
        $sql = "GRANT SELECT ON $spaceName.* TO '$spaceName'@'%'";
        $pdo->query($sql);

        $sql = "show tables";
        $tables = $pdo->query($sql)->fetchAll();
        foreach ($tables as $tb) {
            $table = $tb[0];
            try {
                $sql = "CREATE OR REPLACE VIEW $spaceName.$table  AS SELECT * FROM $table WHERE id_space=$spaceID";
                $pdo->query($sql);
            } catch(Exception $e) {
                Configuration::getLogger()->warning("[db] could not create view", ["error" => $e->getMessage()]);
            }
        }
        try {
            $sql = "CREATE or REPLACE VIEW $spaceName.users AS ";
            $sql .= "SELECT core_users.login, core_users.id from core_users ";
            $sql .= "INNER JOIN core_j_spaces_user on core_j_spaces_user.id_user=core_users.id ";
            $sql .= "WHERE core_j_spaces_user.status > 0 and core_j_spaces_user.id_space=$spaceID";
            $pdo->query($sql);
        } catch(Exception $e) {
            Configuration::getLogger()->warning("[db] could not create user view", ["error" => $e->getMessage()]);
        }
    }

    /**
     * Get an object instance from an array
     */
    public function loadFrom(array $data)
    {
        foreach (get_object_vars($this) as $attrName => $attrValue) {
            if (array_key_exists($attrName, $data)) {
                $this->{$attrName} = $data[$attrName];
            }
        }
    }

    /**
     * Load an object from db based on its id, returns false if not found
     *
     * @param int $id_space optional control on id_space
     */
    public function from(int $id_space=0): int
    {
        if (!$this->tableName) {
            throw new PfmDbException('No table name defined');
        }
        $sql = "SELECT * FROM ".$this->tableName." WHERE id=?";
        $params = array($this->id);
        if ($id_space) {
            $sql .= " AND id_space=?";
            $params[] = $id_space;
        }
        $res = $this->runRequest($sql, $params);
        if ($res->rowCount() == 0) {
            return false;
        }
        $this->loadFrom($res->fetch());
        return true;
    }

    /**
     * Get a list of object from an array of array
     */
    public function loadArray(array $data)
    {
        $list = [];
        foreach ($data as $elt) {
            $e = new (get_class($this))();
            $e->load($elt);
            $list[] = $e;
        }
        return $list;
    }

    /**
     * Create/update object in db
     *
     * @param int $id_space optional control on id_space
     */
    public function save(int $id_space=0)
    {
        if (!$this->tableName) {
            throw new PfmDbException('No table name defined');
        }
        $protectedColumns = ['tableName', 'columnsNames', 'columnsTypes', 'columnsDefaultValue', 'primaryKey'];
        $columns = [];
        $params = [];
        $values = [];
        foreach (get_object_vars($this) as $attrName => $attrValue) {
            if ($attrName == 'id') {
                continue;
            }
            if (in_array($attrName, $protectedColumns)) {
                continue;
            }
            $columns[] = $attrName;
            $params[] = '?';
            $values[] = $attrValue;
        }

        $id = $this->id;
        if ($this->id) {
            $update = [];
            for ($i=0;$i<count($columns);$i++) {
                $update[] = $columns[$i]. " = ?";
            }
            $sql = "UPDATE ".$this->tableName." SET ".implode(',', $update)." WHERE id=?";
            $values[] = $this->id;
            if ($id_space) {
                $sql .= " AND id_space=?";
                $values[] = $id_space;
            }
            $this->runRequest($sql, $values);
        } else {
            $sql = "INSERT INTO ".$this->tableName." (".implode(',', $columns).") VALUES (".implode(',', $params).")";
            $this->runRequest($sql, $values);
            $id = $this->getDatabase()->lastInsertId();
            $this->id = $id;
        }
        return $id;
    }
}
