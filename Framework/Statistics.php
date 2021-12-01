<?php
/**
 * Handle InfluxDB v2 stats recording and bucket/token creation
 * 
 * 
 * Example usage
 * 
 * Create a space db (bucket): $s = new Statistics(); $s-> $createDB('test8');
 * Create a stat:
 * $stat = ['name' => 'measure_test', 'tags' =>['key1' => 'tag1', 'key2' => 'tag2'], 'fields' => ['value' => rand(0, 20)]];
 * $s = new Statistics()
 * $s->record('test8', $stat);
 */
require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';

use InfluxDB2\Client;
use InfluxDB2\Point;

use InfluxDB2\Model\BucketRetentionRules;
use InfluxDB2\Model\Organization;
use InfluxDB2\Model\PostBucketRequest;
use InfluxDB2\Service\BucketsService;
use InfluxDB2\Service\OrganizationsService;
use InfluxDB2\Model\Authorization;
use InfluxDB2\Model\Permission;
use InfluxDB2\Service\AuthorizationsService;


class BucketStatistics extends Model {


    public function __construct() {
        $this->tableName = "stats_buckets";
    }

    /**
     * Create the stats_buckets table
     * 
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `stats_buckets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `token` varchar(200) NOT NULL,
            `bucket` varchar(30) NOT NULL,
            `space` varchar(30) NOT NULL,
            PRIMARY KEY (`id`)
            );";
        $this->runRequest($sql);
    }

    /**
     * Store token of space bucket in db
     */
    public function add($space, $bucket, $token) {
        $sql = "INSERT INTO stats_buckets(space, token, bucket) VALUES (?, ?,?)";
        $this->runRequest($sql, [$space, $token, $bucket]);
    }

    /**
     * Get bucket,token... for space bucket
     */
    public function get($space) {
        $sql = "SELECT * FROM stats_buckets WHERE space=?";
        return $this->runRequest($sql, [$space])->fetch();
    }

}


class Statistics {

    private $clients = [];
    private $wapis = [];

    /**
     * Checks if stats are enabled (influxdb configured)
     */
    public static function enabled() {
        return Configuration::get('influxdb_url', '') !== '';
    }

    public function getClient($space) {
        if(!isset($this->clients[$space])) {
            if (Configuration::get('influxdb_url', '') === '') {
                Configuration::getLogger()->debug('[stats] disabled');
                return null;
            }
            $this->clients[$space] = new Client([
                "url" => Configuration::get('influxdb_url'),  // "http://localhost:8086"
                "token" => Configuration::get('influxdb_token'),
                "bucket" => $space,
                "org" => Configuration::get('influxdb_org', 'pfm'),
                "precision" => InfluxDB2\Model\WritePrecision::S,
                "debug" => Configuration::get('debug_influxdb', false)
    
            ]);
            $writeApi = $this->clients[$space]->createWriteApi();
            $this->wapis[$space] = $writeApi;
        }
        return $this->clients[$space];
    }

    public function closeClient($space) {
        $this->clients[$space]->close();
        unset($this->clients[$space]);
        unset($this->wapis[$space]);
    }

    public function getWriteApi($space) {
        if(!isset($this->clients[$space])) {
            $this->getClient($space);
        }
        return $this->wapis[$space];
    }

    /**
     * Sends a stat for recording
     * 
     * @param string $space space shortname
     * @param array input stat
     * @return boolean success/failure indication
     * 
    */
    public function record($space, $stat) {
        try {
            if (!isset($stat['fields']['value'])) {
                Configuration::getLogger()->error('[stats] missing value in fields', ['stat' => $stat]);
                return false;
            }
            if (!is_int($stat['fields']['value'] && !is_float($stat['fields']['value']))) {
                // not an int, try to convert
                $stat['fields']['value'] = floatval($stat['fields']['value']);
            }
            $point = self::getPoint($stat);
            $writeApi = $this->getWriteApi($space);
            if($writeApi == null) {
                return;
            }
            $writeApi->write($point);
        } catch(Throwable $e) {
            Configuration::getLogger()->error('[stats] stat error', ['message' => $e->getMessage()]);
            $this->closeClient($space);
            return false;
        }
        return true;
    }

    /**
     * Get an array of influxdb stats from input stats
     * 
     * $stats =
     *  ['name' => 'measure_name', 'tags' =>['key1' => 'tag1', 'key2' => 'tag2'], 'time' => timestamp, 'fields' => ['value' => 123]]
     * 
     * value key in fields is mandary and must be numeric
     * 
     * fields and ts are optional, in this case current timestamp is used and fields are empty
     */
    private static function getPoint($stat) {
        return Point::fromArray($stat);
    }

    /**
     * Create a new database
     */
    public function createDB($space) {
        try {
            // check if a database exists then create it if it doesn't
            $client = $this->getClient(Configuration::get('influxdb_org', 'pfm'));
            if($client == null) {
                return;
            }
            $org = self::getOrg($client);
            if($org === null) {
                Configuration::getLogger()->error('[stats] org not found');
                return;
            }

            // Create bucket in org
            $bucketsService = $client->createService(BucketsService::class);
            $buckets = $bucketsService->getBuckets();
            if ($buckets) {
            foreach ($buckets['buckets'] as $bucket) {
                if($bucket['name'] === $space) {
                    Configuration::getLogger()->info('[stats] bucket already exists, skipping', ['bucket' => $space]);
                    return;
                }
            }
            }
            $rule = new BucketRetentionRules();
            $rule->setEverySeconds(3600*24*365*10);  // ten years
            $bucketName = $space;
            $bucketRequest = new PostBucketRequest();
            $bucketRequest->setName($bucketName)->setOrgId($org->getId());
            $bucket = $bucketsService->postBuckets($bucketRequest);

            // Create a token to access this bucket and store it 
            $authService = $client->createService(AuthorizationsService::class);
            $auth = new Authorization();
            $auth->setOrgId($org->getId());
            $auth->setDescription($space);
            $permission = new InfluxDB2\Model\Permission();
            $permission->setAction(InfluxDB2\Model\Permission::ACTION_READ);
            $permResource = new InfluxDB2\Model\PermissionResource();
            $permResource->setType(InfluxDB2\Model\PermissionResource::TYPE_BUCKETS);
            $permResource->setId($bucket->getId());
            $permission->setResource($permResource);
            $auth->setPermissions([$permission]);
            $authRes = $authService->postAuthorizations($auth);
            $token = $authRes->getToken();
            $sm = new BucketStatistics();
            $sm->add($space, $bucket->getId(), $token);
            Configuration::getLogger()->debug('[stats] bucket created', ['bucket' => $bucket->getId(), 'token' => $token]);
            // $client->close();
        } catch(Throwable $e) {
            Configuration::getLogger()->error('[stats] createdb error', ['message' => $e->getMessage(), 'line' => $e->getLine(), "file" => $e->getFile(),  'stack' => $e->getTraceAsString()]);
        } 

    }

    public static function getOrg($client) {
        if($client == null) {
            return null;
        }
        $orgService = $client->createService(OrganizationsService::class);
        $orgs = $orgService->getOrgs()->getOrgs();
        foreach ($orgs as $org) {
            if ($org->getName() == Configuration::get('influxdb_org', 'pfm')) {
                return $org;
            }
        }
        return null;
    }
}
?>