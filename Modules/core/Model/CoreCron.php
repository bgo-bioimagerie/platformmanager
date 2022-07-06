<?php
require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Generic cron handler
 */
class CoreCron extends Model {

    const HOURLY = 0;
    const DAILY = 1;
    const MONTHLY = 2;

    public function __construct() {
        $this->tableName = "core_cron";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_cron` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kind` int NOT NULL,
        `name` varchar(255) NOT NULL,
        `last` int NOT NULL,
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    /**
     * Create a new entry
     */
    public function add(int $kind, string $name) {
        if($this->exists($kind, $name)) {
            return 0;
        }
        $sql = 'INSERT INTO core_cron (kind, name) VALUES (?,?)';
        $this->runRequest($sql, array($kind, $name));
        return $this->getDatabase()->lastInsertId();
    }

    public function exists(int $kind, string $name) :bool {
        $sql = 'SELECT * FROM core_cron where kind=? AND name=?';
        $res = $this->runRequest($sql, array($kind, $name));
        if($res->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function get(int $kind, string $name) {
        $sql = 'SELECT * FROM core_cron where kind=? AND name=?';
        $res = $this->runRequest($sql, array($kind, $name));
        if($res->rowCount() == 1) {
            return $res->fetch();
        }
        return null;
    }

    public function next(int $kind, string $name) {
        $sql = 'UPDATE core_cron SET last=? WHERE kind=? AND name=?';
        $this->runRequest($sql, [time(), $kind, $name]);
    }

    public function delete(int $kind, string $name) {
        $sql = 'DELETE FROM core_cron WHERE kind=? AND name=?';
        $this->runRequest($sql, [$kind, $name]);
    }

    public function run(int $kind, string $name) {
        $job = $this->get($kind, $name);
        if($job == null) {
            Configuration::getLogger()->warning('[cron] no job found', ['kind' => $kind, 'name' => $name]);
            return false;
        }
        switch ($job['kind']) {
            case CoreCron::HOURLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 hour');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    return true;
                }
                break;
            case CoreCron::DAILY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 day');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    return true;
                }
                break;
            case CoreCron::MONTHLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 month');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    return true;
                }
                break;
            default:
                Configuration::getLogger()->error('[cron] invalid kind', ['kind' => $kind, 'name' => $name]);
                return false;
        }

        
    }

}


?>