<?php

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Framework/Statistics.php';

use GuzzleHttp\Client;

/**
 * Example flux query on measurement uers for bucket test (space shortname)
 * 
 * from(bucket: "test")
 * |> range(start: v.timeRangeStart, stop: v.timeRangeStop)
 * |> filter(fn: (r) => r["_measurement"] == "users")
 * |> filter(fn: (r) => r["_field"] == "value")
 * |> aggregateWindow(every: v.windowPeriod, fn: last, createEmpty: false)
 * |> yield(name: "last")
 * 
 * Get last
 * 
 * from(bucket: "test")
 * |>range(start: -90d)
 * |> filter(fn: (r) => r["_measurement"] == "users")
 * |> last()
 */

class Grafana {


    public function getOrg($name) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return null;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET',
        '/api/orgs/name/'.$name,
        [
            'headers' => ['Accept' => 'application/json'],
            'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
            'http_errors' => false
        ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        return $json["id"];
    }

    /**
     * @param string $space shortname of the space
     */
    public function createOrg($space) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return false;
        }
        Configuration::getLogger()->debug("[grafana] create org", ["org" => $space]);
        $orgID = $this->getOrg($space);
        if($orgID) {
            Configuration::getLogger()->debug("[grafana][create] org already exists", ["org" => $space]);
        } else {
            $client = new Client([
                'base_uri' => Configuration::get('grafana_url'),
                'timeout'  => 2.0,
            ]);
    
            $req = ['name' => $space];
            $response = $client->request('POST',
                '/api/orgs',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                    'json' => $req,
                    'http_errors' => false
                ]
            );
            
            $status = $response->getStatusCode();
            if ($status != 200) {
                Configuration::getLogger()->error('[grafana][error] failed to create org', ["org" => $space, "err" => $response->getBody(), 'req' => $req]);
                return false;
            }
            $body = $response->getBody();
            $json = json_decode($body, true);
            $orgID = $json['orgId'];
            Configuration::getLogger()->debug("[grafana][create] org created", ["org" => $space, "id" => $orgID]);

        }

        # switch to org
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $req = [];
        $client->request('POST',
            '/api/user/using/'.$orgID,
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'http_errors' => false
            ]
        );

        $bsm = new BucketStatistics();
        $bucketObj = $bsm->get($space);
        $org = Configuration::get('influxdb_org', 'pfm');
        $token = $bucketObj['token'];
        $bucket = $bucketObj['bucket'];
        
        # create data source
        $req = [
            "jsonData" => ["defaultBucket" => $bucket, "organization" => $org, "version" => "Flux", "httpMode" => "POST"],
            "secureJsonData" => [
                "token" => $token
            ],
            'access' => "proxy",
            'basicAuth' => true,
            'basicAuthPassword' => "",
            'basicAuthUser' => "",
            'isDefault' => true,
            'name' => $space,
            'orgId' => $orgID,
            'password' => "",
            'readOnly' => false,
            'secureJsonFields' => [],
            'type' => "influxdb",
            'typeLogoUrl' => "",
            'url' => Configuration::get('influxdb_url'),
            'version' => 1,
            'withCredentials' => false
        ];
        Configuration::getLogger()->debug('[grafana] Create data source', ['source' => $req]);

        $response = $client->request('POST',
            '/api/datasources',
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );

        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[grafana][error] failed to create datasource', ["org" => $space, "err" => $response->getBody()]);
            return false;
        }

        if(!file_exists('externals/grafana/dashboard.tmpl')) {
            return true;
        }

        $template = file_get_contents('externals/grafana/dashboard.tmpl');
        $template = str_replace("space1", $space, $template);
        $template = str_replace("space1", $space, $template);
        $dashboard = json_decode($template, true);
        $dashboard["id"] = null;
        $dashboard["uid"] = null;
        $req = ['dashboard' => $dashboard, "folderId" => 0, "overwrite" => false];

        # Create template
        $response =  $client->request('POST',
            '/api/dashboards/db',
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );

        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[grafana][error] failed to create dashboard', ["org" => $space, "err" => $response->getBody()]);
            return false;
        }
        return true;
        
    }


    public function getUser($name) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return null;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $response =  $client->request('GET',
            '/api/users/lookup',
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'query' => ['loginOrEmail' => $name],
                'http_errors' => false
            ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_get] user not found', ["name" => $name, "err" => $response->getBody()]);
            return null;
        }

        $body = $response->getBody();
        $json = json_decode($body, true);
        return $json['id'];
    }

    public function updateUserPassword($name, $apikey) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return false;
        }
        $user = $this->getUser($name);
        if(!$user) {
            return false;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $req = [
            "password" => $apikey,
        ];

        $response =  $client->request('PUT',
            "/api/admin/users/".$user."/password",
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_password] update failed', ["name" => $name, "err" => $response->getBody()]);
            return false;
        }
        return true;
    }

    /**
     * Create user and remove from main org
     */
    public function createUser($orgID, $name, $apikey) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return null;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $req = [
            "name" => $name,
            "login" => $name,
            "password" => $apikey,
            "orgId" => 1
        ];

        $response =  $client->request('POST',
            "/api/admin/users",
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_create] add failed', ["name" => $name, "err" => $response->getBody()]);
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        $user = $json["id"];

        $client->request('DELETE',
            "/api/orgs/1/users/".$user,
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'http_errors' => false
            ]
        );
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_create] failed to remove from main org', ["name" => $name, "err" => $response->getBody()]);
        }

        return $user;
    }

    /**
     * Create user if needed and add to org
     */
    public function addUser($space, $name, $apikey) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return false;
        }
        $orgID = $this->getOrg($space);
        if(!$orgID) {
            Configuration::getLogger()->debug('[grafana][user_add] org not found', ['space' => $space, 'user' => $name]);
            return false;
        }
        $user = $this->getUser($name);
        if(!$user) {
            $user = $this->createUser($orgID, $name, $apikey);
        }

        // Add editor role to user in org
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $req = [
            "loginOrEmail" => $name,
            "role" => "Editor"
        ];

        $response =  $client->request('POST',
            "/api/orgs/".$orgID."/users",
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_add] add failed', ["space" => $space, "name" => $name, "err" => $response->getBody()]);
            return false;
        }
        return true;
    }

    /**
     * Remove user role from org
     */
    public function delUser($space, $name) {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return false;
        }
        $orgID = $this->getOrg($space);
        if(!$orgID) {
            Configuration::getLogger()->debug('[grafana][delete_user] org not found', ['space' => $space, 'user' => $name]);
            return false;
        }
        $user = $this->getUser($name);
        if(!$user) {
            return false;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $response =  $client->request('DELETE',
            "/api/orgs/".$orgID."/users/".$user,
            [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'http_errors' => false
            ]
        );
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_delete] delete failed', ["space" => $space, "name" => $name, "err" => $response->getBody()]);
            return false;
        }
        return true;
    }
}

?>