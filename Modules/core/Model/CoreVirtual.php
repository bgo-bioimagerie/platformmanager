<?php
require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Virtual counter
 */
class CoreVirtual extends Model {

    public function __construct() {
        $this->tableName = "core_virtual";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_virtual` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(150) NOT NULL,
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
        $this->baseSchema();
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

    /**
     * Set name/value in redis, value will be json_encoded
     */
    public function set(int $id_space, string $name, mixed $value, $expire=null) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        try {
        $val = json_encode($value);
        } catch(Exception $e) {
            $redis->close();
            throw $e;
        }
        $redis->set("pfm:$id_space:$name", $val, $expire);
        $redis->close();   
    }

    /**
     * Get name key from redis, value is json_decoded
     */
    public function get($id_space, $name): mixed {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        $value = $redis->get("pfm:$id_space:$name");
        $redis->close();
        $val = null;
        if($value) {
            try {
            $val = json_decode($value, true);
            } catch(Exception $e) {
                Configuration::getLogger()->error('[core][virtual] json error', ['error' => $e->getMessage()]);
                $val = null;
            }
        }
        return $val;
    }

    public function newRequest($id_space, $module, $name) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        $rid = time().':'.$name;
        try {
        $redis->hSet('pfm:'.$id_space.':'.$module.':request', $rid, 'waiting');
        $redis->expire('pfm:'.$id_space.':'.$module.':request', 3600 * 5); // expire in 5h
        } catch(Exception $e) {
            $redis->close();
            throw $e;
        }
        $redis->close();
        return $rid;
    }

    public function updateRequest($id_space, $module, $rid, $msg) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        try {
        $redis->hSet('pfm:'.$id_space.':'.$module.':request', $rid, $msg);
        $redis->expire('pfm:'.$id_space.':'.$module.':request', 3600 * 5); // expire in 5h
        } catch(Exception $e) {
            $redis->close();
            throw $e;
        }
        $redis->close();
        return $rid;
    }

    public function deleteRequest($id_space, $module, $rid) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        try {
        $redis->hDel('pfm:'.$id_space.':'.$module.':request', $rid);
        } catch(Exception $e) {
            $redis->close();
            throw $e;
        }
        $redis->close();
        return $rid;
    }

    public function getRequests($id_space, $module) {
        $redis = new Redis();
        $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
        $requests = [];
        try {
            $requests = $redis->hGetAll('pfm:'.$id_space.':'.$module.':request');
            krsort($requests);
        } catch(Exception $e) {
            $redis->close();
            throw $e;
        }
        $redis->close();
        return $requests;        
    }


}


?>