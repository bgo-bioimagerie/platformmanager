<?php
/**
 * Handle InfluxDB v2 stats recording and bucket/token creation
 * 
 * 
 * Example usage
 * 
 * Create a space db (bucket): Statistics::createDB('test8');
 * Create a stat:
 * $stat = ['name' => 'measure_test', 'tags' =>['key1' => 'tag1', 'key2' => 'tag2'], 'fields' => ['value' => rand(0, 20)]];
 * Statistics::stat('test8', $stat);
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

    protected static function client($space){
        if (Configuration::get('influxdb_url', '') === '') {
                Configuration::getLogger()->debug('[stats] disabled');
                return null;
        }
        return new Client([
            "url" => Configuration::get('influxdb_url'),  // "http://localhost:8086"
            "token" => Configuration::get('influxdb_token'),
            "bucket" => $space,
            "org" => Configuration::get('influxdb_org', 'pfm'),
            "precision" => InfluxDB2\Model\WritePrecision::S,
            "debug" => Configuration::get('debug', false)

        ]);
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
     * Sends a stat for recording
     * 
     * @param string $space space shortname
     * @param array input stat
     * @return boolean success/failure indication
     * 
    */
    public static function stat($space, $stat) {
        try {
            if (!isset($stat['fields']['value'])) {
                Configuration::getLogger()->error('[stats] missing value in fields', ['stat' => $stat]);
                return false;
            }
            if (!is_int($stat['fields']['value'])) {
                // not an int, try to convert
                $stat['fields']['value'] = intval($stat['fields']['value']);
            }
            $point = self::getPoint($stat);
            $client = self::client($space);
            $writeApi = $client->createWriteApi();
            $writeApi->write($point);
            $client->close();
        } catch(Exception $e) {
            Configuration::getLogger()->error('[stats] stat error', ['message' => e.getMessage()]);
            return false;
        }
        return true;
    }

    /**
     * Create a new database
     */
    public static function createDB($space) {
        try {
            // check if a database exists then create it if it doesn't
            $client = self::client(Configuration::get('influxdb_org', 'pfm'));
            $org = self::getOrg($client);
            if($org === null) {
                Configuration::getLogger()->error('[stats] org not found');
                return;
            }

            // Create bucket in org
            $bucketsService = $client->createService(BucketsService::class);
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
            $client->close();
        } catch(Exception $e) {
            Configuration::getLogger()->error('[stats] createdb error', ['message' => $e->getMessage()]);
        } 

    }

    public static function getOrg($client) {
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