<?php
require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Virtual counter
 */
class CoreVirtual extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_virtual` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(150) NOT NULL,
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    /**
     * Create an new virtual entry
     */
    public function new($name) {
        $sql = 'INSERT INTO core_virtual (name) VALUES (?)';
        $this->runRequest($sql, array($name));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * Increment a counter for space/name and return new value
     * 
     * Using Redis
     */
    public function incr($id_space, $name) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        $newid = $redis->incr("pfm:$id_space:$name");
        $redis->close();
        return $newid;
    }

    public function incrBy($id_space, $name, $value) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        $newid = $redis->incrBy("pfm:$id_space:$name", $value);
        $redis->close();
        return $newid;
    }

}


?>