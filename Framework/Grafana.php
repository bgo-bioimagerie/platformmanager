<?php

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Constants.php';

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

    public function configured() {
        if(!Configuration::get('grafana_url')) {
            Configuration::getLogger()->info("[grafana] grafana not configured");
            return false;
        }
        return true;
    }


    public function getOrg($name) {
        if(!$this->configured()) {
            return null;
        }
        $client = new Client([
            'base_uri' => Configuration::get('grafana_url'),
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET',
        '/api/orgs/name/'.$name,
        [
            'headers' => ['Accept' => Constants::APPLICATION_JSON],
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

    public function dashboardsImport($spaceObject) {
        $space = $spaceObject['shortname'];
        $orgID = $this->getOrg($space);
        if(! $orgID) {
            Configuration::getLogger()->debug("[grafana][dashboard][import] org does not exists", ["org" => $space]);
            return false;
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
                    'Accept' => Constants::APPLICATION_JSON
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'http_errors' => false
            ]
        );

        $templates = scandir('externals/grafana/templates');
        $ok = true;
        foreach($templates as $tmpl) {
            if (! str_ends_with($tmpl, ".json")) {
                continue;
            }
            Configuration::getLogger()->debug('[grafana] import dashboard', ['template' => $tmpl]);
            $template_name = str_replace(".json", "", $tmpl);
            $template = file_get_contents("externals/grafana/templates/$tmpl");
            $template = str_replace("space1", $space, $template);
            $template = str_replace("mysql_0", "mysql_".$spaceObject['id'], $template);
            $template = str_replace("influxdb_0", "influxdb_".$spaceObject['id'], $template);
            $template = str_replace("dash_0", str_replace('.json', '', $tmpl), $template);

            $dashboard = json_decode($template, true);
            $dashboard["id"] = null;
            $dashboard["uid"] = "pfm_$template_name";
            $req = ['dashboard' => $dashboard, "folderId" => 0, "overwrite" => true];

            $client = new Client([
                'base_uri' => Configuration::get('grafana_url'),
                'timeout'  => 2.0,
            ]);

            # Create template
            $response =  $client->request('POST',
                '/api/dashboards/db',
                [
                    'headers' => [
                        'Accept' => Constants::APPLICATION_JSON,
                        'Content-Type' => Constants::APPLICATION_JSON
                    ],
                    'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                    'json' => $req,
                    'http_errors' => false
                ]
            );

            $status = $response->getStatusCode();
            if ($status != 200) {
                Configuration::getLogger()->error('[grafana][error] failed to create dashboard', ["org" => $space, "err" => $response->getBody()]);
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * @param mixed $space space object
     */
    public function createOrg($spaceObject) {
        if(!$this->configured()) {
            return false;
        }
        $space = $spaceObject['shortname'];
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
                        'Accept' => Constants::APPLICATION_JSON,
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
                    'Accept' => Constants::APPLICATION_JSON
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
        
        # create influx data source
        $req = [
            "jsonData" => ["defaultBucket" => $bucket, "organization" => $org, "version" => "Flux", "httpMode" => "POST"],
            "secureJsonData" => [
                "token" => $token
            ],
            'uid' => 'influxdb_'.$spaceObject['id'],
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
        Configuration::getLogger()->debug('[grafana] Create influxdb data source', ['source' => $req]);

        $response = $client->request('POST',
            '/api/datasources',
            [
                'headers' => [
                    'Accept' => Constants::APPLICATION_JSON
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );

        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[grafana][error] failed to create datasource', ["org" => $space, "err" => $response->getBody()]);
        }


        # create mysql data source   db/user = "pfm".$spaceID;
        $mysqlID = 'pfm'.$spaceObject['id'];
        $req = [
            "orgId" => $orgID,
            'uid' => 'mysql_'.$spaceObject['id'],
            "name" => "MySQL",
            "type" => "mysql",
            "typeLogoUrl" => "",
            "access" => "proxy",
            "url" => sprintf("%s:%s", Configuration::get('mysql_host', 'mysql'),Configuration::get('mysql_port', 3306)),
            "password" => "",
            "user" => $mysqlID,
            "database" => $mysqlID,
            "basicAuth" => false,
            "basicAuthUser" => "",
            "basicAuthPassword" => "",
            "withCredentials" => false,
            "isDefault" => false,
            "secureJsonData" => ["password" => crypt($mysqlID, Configuration::get('jwt_secret'))],
            "version" => 1,
            "readOnly" => false,
        ];
        Configuration::getLogger()->debug('[grafana] Create mysql data source', ['source' => $req]);

        $response = $client->request('POST',
            '/api/datasources',
            [
                'headers' => [
                    'Accept' => Constants::APPLICATION_JSON
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'json' => $req,
                'http_errors' => false
            ]
        );

        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[grafana][error] failed to create datasource', ["org" => $space, "err" => $response->getBody()]);
        }

       return $this->dashboardsImport($spaceObject);

    }


    public function getUser($name) {
        if(!$this->configured()) {
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
                    'Accept' => Constants::APPLICATION_JSON
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
        if(!$this->configured()) {
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
                    'Accept' => Constants::APPLICATION_JSON
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
    public function createUser($name, $apikey) {
        if(!$this->configured()) {
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
                    'Accept' => Constants::APPLICATION_JSON
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
                    'Accept' => Constants::APPLICATION_JSON
                ],
                'auth' => [Configuration::get('grafana_user'), Configuration::get('grafana_password')],
                'http_errors' => false
            ]
        );
        if ($status != 200) {
            Configuration::getLogger()->debug('[grafana][user_create] failed to remove from main org', ["name" => $name, "err" => $response->getBody()]);
        }

        Configuration::getLogger()->debug('[grafana][user_create] created', ["name" => $name]);


        return $user;
    }

    /**
     * Create user if needed and add to org
     * 
     * @var mixed space space object
     * @var string name  user login
     * @var string apikey used for password
     */
    public function addUser($space, string $name, string $apikey) {
        if(!$this->configured()) {
            return false;
        }
        $orgID = $this->getOrg($space);
        if(!$orgID) {
            Configuration::getLogger()->debug('[grafana][user_add] org not found', ['space' => $space, 'user' => $name]);
            return false;
        }
        $user = $this->getUser($name);
        if(!$user) {
            $this->createUser($name, $apikey);
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
                    'Accept' => Constants::APPLICATION_JSON
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
        Configuration::getLogger()->debug('[grafana][user_add] added', ["space" => $space, "name" => $name]);
        return true;
    }

    /**
     * Remove user role from org
     */
    public function delUser($space, $name) {
        if(!$this->configured()) {
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
                    'Accept' => Constants::APPLICATION_JSON
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