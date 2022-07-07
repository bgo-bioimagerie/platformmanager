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
        if($this->exists($name)) {
            return 0;
        }
        $sql = 'INSERT INTO core_cron (kind, name) VALUES (?,?)';
        $this->runRequest($sql, array($kind, $name));
        return $this->getDatabase()->lastInsertId();
    }

    public function exists(string $name) :bool {
        $sql = 'SELECT * FROM core_cron where name=?';
        $res = $this->runRequest($sql, array($name));
        if($res->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function get(string $name) {
        $sql = 'SELECT * FROM core_cron where name=?';
        $res = $this->runRequest($sql, array($name));
        if($res->rowCount() == 1) {
            return $res->fetch();
        }
        return null;
    }

    function next(string $name) {
        $job = $this->get($name);
        if($job == null) {
            throw new PfmException('[cron] job not found:  '.$name);
        }
        $next = 0;
        if($job['last'] == 0) {
            $job['last'] = time();
        }
        switch ($job['kind']) {
            case CoreCron::HOURLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 hour');
                $next = $last->getTimestamp();
                break;
            case CoreCron::DAILY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 day');
                $next = $last->getTimestamp();
                break;
            case CoreCron::MONTHLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 month');
                $next = $last->getTimestamp();
                break;
            default:
                Configuration::getLogger()->error('[cron] job kind unknown', ['job' => $job]);
                break;
        }
        $sql = 'UPDATE core_cron SET last=? WHERE name=?';
        $this->runRequest($sql, [$next, $name]);
    }

    public function delete(string $name) {
        $sql = 'DELETE FROM core_cron WHERE name=?';
        $this->runRequest($sql, [$name]);
    }

    public function run(string $name) {
        $job = $this->get($name);
        if($job == null) {
            Configuration::getLogger()->warning('[cron] no job found', ['kind' => $job['kind'], 'name' => $name]);
            return false;
        }
        $doJob = false;
        switch ($job['kind']) {
            case CoreCron::HOURLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 hour');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    $doJob = true;
                }
                break;
            case CoreCron::DAILY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 day');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    $doJob = true;
                }
                break;
            case CoreCron::MONTHLY:
                $last = new DateTime();
                $last->setTimestamp($job['last']);
                $last->modify('+1 month');
                $now = new DateTime();
                if($job['last'] == 0 || $last < $now) {
                    $doJob = true;
                }
                break;
            default:
                Configuration::getLogger()->error('[cron] invalid kind', ['kind' => $job['kind'], 'name' => $name]);
                break;
        }

        if($doJob) {
            $this->next($name);
        }
        return $doJob;

        
    }

}


?>