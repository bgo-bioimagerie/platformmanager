<?php

require_once 'Configuration.php';
use DebugBar\DataCollector\PDO\TraceablePDO;
/**
 * Abstract class Model
 * A model define an access to the database
 *
 * @author Sylvain Prigent
 */
abstract class Model {

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

    /**
     * Run a SQL request
     * 
     * @param string $sql SQL request
     * @param array $params Request parameters
     * @return PDOStatement Result of the request
     */
    protected function runRequest($sql, $params = null) {
        $result = null;
        if (Configuration::get('debug_sql', false)) {
            Configuration::getLogger()->debug('[sql] query', ['sql' => $sql, 'params' => $params]);
        }
        
        try {
            if ($params == null) {
                $result = self::getDatabase()->query($sql);   // direct query
            } else {
                $result = self::getDatabase()->prepare($sql); // prepared request
                //print_r($params);
                //echo "class = " . get_class($this) . "<br/>";
                $result->execute($params);
            }

        } catch (\Throwable $th) {
            $msg = '';
            if($result) {
                $msg = $result->errorInfo();
            }
            Configuration::getLogger()->error('[sql] error', ['sql' => $sql, 'params' => $params, 'error' => $msg]);
        }

        //print_r( $result->errorInfo() );
        return $result;
    }

    /**
     * Return an object that connect the database and initialize the connection if needed
     * 
     * @return PDO Objet PDO of the database connections
     */
    static function getDatabase() {
        if (self::$bdd === null) {
            // load the database informations
            $dsn = Configuration::get("dsn");
            $login = Configuration::get("login");
            $pwd = Configuration::get("pwd");

            // Create connection
            self::$bdd = new PDO($dsn, $login, $pwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            if (getenv('PFM_MODE') == 'dev') {
                self::$bdd = new DebugBar\DataCollector\PDO\TraceablePDO(self::$bdd);
            }
            self::$bdd->exec("SET CHARACTER SET utf8");
        }
        return self::$bdd;
    }

    /**
     * 
     * @param type $dsn
     * @param type $login
     * @param type $pwd
     */
    public function setDatabase($dsn, $login, $pwd) {

        //echo "dsn = " . $dsn . "<br/>";
        //echo "login = " . $login . "<br/>";
        //echo "pwd = " . $pwd . "<br/>";
        // Create connection
        self::$bdd = new PDO($dsn, $login, $pwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        self::$bdd->exec("SET CHARACTER SET utf8");
    }

    /**
     * 
     * @param type $tableName
     * @param type $columnName
     * @param type $columnType
     * @param type $defaultValue
     */
    public function addColumn($tableName, $columnName, $columnType, $defaultValue) {

        $sql = "SHOW COLUMNS FROM `" . $tableName . "` LIKE '" . $columnName . "'";
        $pdo = $this->runRequest($sql);
        $isColumn = $pdo->fetch();
        if ($isColumn == false) {
            $sql = "ALTER TABLE `" . $tableName . "` ADD `" . $columnName . "` " . $columnType . " NOT NULL";
            if($defaultValue != "") {
                if(is_string($defaultValue)) {
                    $sql .= " DEFAULT '" . $defaultValue . "'";
                } else {
                    $sql .= " DEFAULT " . $defaultValue;
                }
            }
            $this->runRequest($sql);
        }
    }

    /**
     * 
     * @param type $table
     * @return boolean
     */
    public function isTable($table) {

        $dsn = Configuration::get("dsn");
        $dsnArray = explode(";", $dsn);
        $dbname = "";
        for ($i = 0; $i < count($dsnArray); $i++) {
            if (strpos($dsnArray[$i], "dbname") === false) {

            } else {
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

    public function createTableInNotExists($tableName){
        $sql = "CREATE TABLE IF NOT EXISTS `" . $tableName . "`"
                . " (id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY)";
        $this->runRequest($sql);
    }
    
    public function addPrimary($tableName, $primary){
        $sql = "ALTER TABLE ".$tableName." ADD PRIMARY KEY (".$primary.");";
        $this->runRequest($sql);   
    }
    
    /**
     * 
     */
    public function createTable() {

        // create database if not exists
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "` (";
        for ($i = 0; $i < count($this->columnsNames); $i++) {
            $sql .= "`" . $this->columnsNames[$i] . "` " . $this->columnsTypes[$i];
            if ($this->columnsDefaultValue[$i] != "") {
                if(is_string($this->columnsDefaultValue[$i])) {
                    $sql .= " NOT NULL DEFAULT '" . $this->columnsDefaultValue[$i] . "' ";
                } else {
                    $sql .= " NOT NULL DEFAULT " . $this->columnsDefaultValue[$i] . " ";
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
     * @param type $name
     * @param type $type
     * @param type $value
     */
    public function setColumnsInfo($name, $type, $value) {
        $this->columnsNames[] = $name;
        $this->columnsTypes[] = $type;
        $this->columnsDefaultValue[] = $value;
    }

    /**
     * 
     * @param type $data
     */
    public function insert($data) {
        $sql = "INSERT INTO " . $this->tableName;
        $keyString = "";
        $valuesString = "";
        foreach ($data as $key => $value) {
            //echo "key = " . $key . "<br/>";
            //echo "value = " . $value . "<br/>";
            $keyString .= $key . ",";
            $valuesString .= "'" . $value . "'" . ",";
        }
        $sql .= " (" . substr($keyString, 0, -1) . ") VALUES (" . substr($valuesString, 0, -1) . ");";

        //echo "query = " . $sql . "<br/>";
        $this->runRequest($sql);
        $this->getDatabase()->lastInsertId();
    }

    /**
     * 
     * @param type $conditions
     * @param type $data
     */
    public function update($conditions, $data) {
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
    public function selectAll($sortEntry = "") {
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
    public function select($conditions, $columnsToSelect = array()) {
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
    public function isEntry($key, $value) {
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
    public function deleteAll() {
        $sql = "DELETE FROM " . $this->tableName;
        $this->runRequest($sql);
    }

}
