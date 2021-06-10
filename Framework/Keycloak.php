<?php
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreLdapConfiguration.php';

use GuzzleHttp\Client;

/**
 * Class allowing to communicate with keycloak. 
 * 
 * @author Olivier Sallou
 */
class Keycloak{

    protected $url;
    protected $admin;
    protected $password;
    protected $logger;
    protected $token;  // keycloak token after auth
    
    public function __construct(string $url='', string $user='', string $password=''){
        $this->url = Configuration::get('keycloak_url', $url);
        $this->admin = Configuration::get('keycloak_user', $user);
        $this->password= Configuration::get('keycloak_password', $password);
        $this->logger = Configuration::getLogger();
    }

    public function token(string $token) {
        if($token) {
            $this->token = $token;
        }
        return $this->token;
    }

    public function user_from_token(string $token) {
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('POST',
            sprintf('/auth/realms/%s/protocol/openid-connect/userinfo', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->error('[keycloak] failed to get user info');
            return null;
        }

        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * Create keycloak realm if not exists
     */
    private function create_realm(string $realm_name) {
        $realm = null;
        $template_path = Configuration::get('keycloak_realm_template', __DIR__.'/../externals/keycloak/keycloak-realm.json');
        try {
            // Check if exists
            $client = new Client([
                'base_uri' => $this->url,
                'timeout'  => 2.0,
            ]);
            $response = $client->request('GET',
                "/auth/admin/realms/$realm_name",
                [
                    'headers' => ['Authorization' => "Bearer $this->token"],
                    'http_errors' => false
                ]
            );
            $code = $response->getStatusCode();
            if ($code == 200) {
                $this->logger->info('[keycloak] realm already exists');
                return true;
            }
            $this->logger->info('[keycloak] create realm', ["realm" => $realm_name]);
            $template = file_get_contents($template_path);
            $realm = json_decode($template);

            $realm->{'id'} = $realm_name;
            $realm->{'verifyEmail'} = boolval(Configuration::get('keycloak_email_validate', true));
            $realm->{'realm'} = $realm->{'id'};
            $realm->{'smtpServer'}->{'host'} = Configuration::get('smtp_host');
            $realm->{'smtpServer'}->{'port'} = Configuration::get('smtp_port');
            $realm->{'smtpServer'}->{'from'} = Configuration::get('smtp_from');
            $ldappre = 'ldap';
            if (boolval(Configuration::get('smtp_tls', false))) {
                $realm->{'smtpServer'}->{'starttls'} = Configuration::getbool('smtp_tls', false);
                $ldappre = 'ldaps';
            }
            if (Configuration::get('smtp_user', '')) {
                $realm->{'smtpServer'}->{'auth'} = true;
                $realm->{'smtpServer'}->{'user'} = Configuration::get('smtp_user');
                $realm->{'smtpServer'}->{'password'} = Configuration::get('smtp_password', '');
            }
            foreach($realm->{'components'}->{'org.keycloak.storage.UserStorageProvider'} as &$cfg) {
                if ($cfg->{'providerId'} == 'ldap') {
                    $cfg->{'config'}->{'connectionUrl'} = array(sprintf('%s://%s:%d',
                        $ldappre,
                        Configuration::get('ldap_host', ''),
                        Configuration::get('ldap_port', 389)
                    ));
        
                    $cfg->{'config'}->{'bindDn'} = array(Configuration::get("ldap_user", ""));
                    $cfg->{'config'}->{'bindCredential'} = array(Configuration::get("ldap_password", ""));
                    $cfg->{'config'}->{'usersDn'} = array(Configuration::get("ldap_search_dn", "ou=people,dc=pfm,dc=org"));
                    break;
                }
            }
            unset($cfg);
            foreach($realm->{'clients'} as &$cfg) {
                if($cfg->{'clientId'} == 'pfm-ooc') {
                    if (getenv('PFM_MODE', '') == 'dev') {
                        $cfg->{'redirectUris'} = array('*');
                    } else {
                        $cfg->{'redirectUris'} = array(Configuration::get('public_url', '') . '/*');
                    }
                    if (Configuration::get('keycloak_oic_secret', '')) {
                        $cfg->{'secret'} = Configuration::get('keycloak_oic_secret', '');
                    }
                }
            }
            unset($cfg);

            echo json_encode($realm)."\n";
            $response = $client->request('POST',
                "/auth/admin/realms",
                [
                    'headers' => ['Authorization' => "Bearer $this->token"],
                    'http_errors' => false,
                    'json' => $realm
                ]
            );
            $code = $response->getStatusCode();
            if ($code != 201) {
                $this->logger->info('[keycloak] realm creation failed', ['error' => $response->getReasonPhrase()]);
                return false;
            }
            $this->logger->info('[keycloak] realm created');

        } catch (Exception $e) {
            $this->logger->error('[keycloak][create_realm] error', ['error' => $e->getMessage()]);
            return false;
        }
        return true;
    }

    /**
     * Get a token for a user from Keycloak
     * 
     * @param uid: user identifier
     * @param password: user password
     * @return string  keycloak token
     * 
     */
    public function user_token(string $uid, string $password) {
        $k_user = [
            'client_id' => 'admin-cli',
            'username' => $uid,
            'password' => $password,
            'grant_type' => 'password'
        ];

        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('POST',
            sprintf('/auth/realms/%s/protocol/openid-connect/token', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => $k_user,
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->error('[keycloak] failed to get token');
            return null;
        }

        $body = $response->getBody();
        $json = json_decode($body, true);
        return $json['access_token'];
    }

    /**
     * Get a token for admin user from Keycloak
     * 
     * @param impersonate : optional user id to impersonate
     */
    public function admin_token(string $impersonate='') {
        $admin_user = [
            'client_id' => 'admin-cli',
            'username' => $this->admin,
            'password' => $this->password,
            'grant_type' => 'password'
        ];
        if($impersonate) {
            $admin_user['requested_subject'] = $impersonate;
        }

        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('POST',
            '/auth/realms/master/protocol/openid-connect/token',
            [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => $admin_user,
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->error('[keycloak] failed to get token');
            return null;
        }

        $body = $response->getBody();
        $json = json_decode($body, true);
        return $json['access_token'];
    }

    /**
     * Get a keycloak role
     */
    public function getUser($name) {
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('GET',
            sprintf('/auth/admin/realms/%s/users', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'form_params' => ['username' => $name],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->info('[keycloak] could not get user');
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        if (empty($json)) {
            $this->logger->info('[keycloak] user not found', ['name' => $name]);
            return null;
        }
        return $json[0];
    }

    /**
     * Get a keycloak role
     */
    public function getRole($name) {
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('GET',
            sprintf('/auth/admin/realms/%s/roles', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->info('[keycloak] could not get role');
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        foreach($json as $role) {
            if ($role['name'] == $name) {
                return $role;
            }
        }
        return null;
    }

    /**
     * Get a keycloak group
     */
    public function getGroup($name) {
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('GET',
            sprintf('/auth/admin/realms/%s/groups', Configuration::get('keycloak_realm','pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200) {
            $this->logger->info('[keycloak] could not get groups', [
                'code' => $code,
                'reason' => $response->getReasonPhrase(),
                'url' => sprintf('/auth/admin/realms/%s/groups', Configuration::get('keycloak_realm','pfm'))
            ]);
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        foreach($json as $group) {
            if ($group['name'] == $name) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Create a new group and role
     */
    public function create_group(string $group) {
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        # create group
        $response = $client->request('POST',
            sprintf('/auth/admin/realms/%s/groups', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'json' => ['name' => $group],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200 && $code != 201) {
            $this->logger->error('[keycloak] failed to get group', ['group' => $group]);
            return false;
        }

        $newgroup = $this->getGroup($group);
        if($newgroup == null) {
            return false;
        }
        $this->logger->debug('[keycloak] group created', ['group' => $group]);

        # create role
        $response = $client->request('POST',
            sprintf('/auth/admin/realms/%s/roles', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'json' => ['name' => $group],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200 && $code != 201) {
            $this->logger->error('[keycloak] failed to get group', ['group' => $group]);
            return false;
        }

        $newrole = $this->getRole($group);
        if($newrole == null) {
            return false;
        }

        $this->logger->debug('[keycloak] role created', ['role' => $group]);

        # link group and role
        $response = $client->request('POST',
            sprintf('/auth/admin/realms/%s/groups/%s/role-mappings/realm', Configuration::get('keycloak_realm', 'pfm'), $newgroup['id']),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'json' => [
                    [
                    'name' => $newrole['name'],
                    'id' => $newrole['id'],
                    'containerId' => Configuration::get('keycloak_realm', 'pfm'),
                    'composite' => false,
                    'clientRole' => false
                    ]
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 204) {
            $this->logger->error('[keycloak] failed to map role and group', ['group' => $group]);
            return false;
        }

        $this->logger->debug('[keycloak] role mapped to group', ['role' => $group]);


        return true;
    }

    /**
     * Add user to group
     */
    public function add_to_group(string $uid, string $group_name) {
        $user = $this->getUser($uid);
        if(!$user) {
            return false;
        }
        $group = $this->getGroup($group_name);
        if(!$group) {
            return false;
        }
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);

        $response = $client->request('PUT',
            sprintf('/auth/admin/realms/%s/users/%s/groups/%s', Configuration::get('keycloak_realm', 'pfm'), $user['id'], $group['id']),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'json' => [
                    'groupId' => $group['id'],
                    'realm' => Configuration::get('keycloak_realm', 'pfm'),
                    'userId' => $user['id']
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 204) {
            $this->logger->error('[keycloak] failed to add user to group', ['name' => $uid, 'group' => $group_name]);
            return false;
        }
        $this->logger->debug('[keycloak] user added to group', ['name' => $uid, 'group' => $group_name]);

        return true;
    }

    /**
     * Add user to group
     */
    public function remove_from_group(string $uid, string $group_name) {
        $user = $this->getUser($uid);
        if(!$user) {
            return false;
        }
        $group = $this->getGroup($group_name);
        if(!$group) {
            return false;
        }
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);

        $response = $client->request('DELETE',
            sprintf('/auth/admin/realms/%s/users/%s/groups/%s', Configuration::get('keycloak_realm', 'pfm'), $user['id'], $group['id']),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 204) {
            $this->logger->error('[keycloak] failed to remove user from group', ['name' => $uid, 'group' => $group_name]);
            return false;
        }
        $this->logger->debug('[keycloak] user removed from group', ['name' => $uid, 'group' => $group_name]);

        return true;
    }

    /**
     * Create keycloak pfm user if not exists
     */
    public function create_user(string $uid, string $password, string $email, string $group) {
        $new_user = [
            'username' => $uid,
            'email' => $email,
            'enabled' => true,
            'credentials' => [
                [
                    "type" => "password",
                    "value" => $password
                ]
            ],
            'emailVerified' => true   
        ];
        $client = new Client([
            'base_uri' => $this->url,
            'timeout'  => 2.0,
        ]);
        $response = $client->request('GET',
            sprintf('/auth/admin/realms/%s/users/%s', Configuration::get('keycloak_realm', 'pfm'), Configuration::get('admin_user')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code == 200) {
            $this->logger->info('[keycloak] pfm admin user already exists');
            return true;
        }

        $response = $client->request('POST',
            sprintf('/auth/admin/realms/%s/users', Configuration::get('keycloak_realm', 'pfm')),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token
                ],
                'json' => $new_user,
                'http_errors' => false
            ]
        );
        
        $code = $response->getStatusCode();
        if ($code != 200 && $code != 201) {
            $this->logger->error('[keycloak] pfm admin user creation failed', ['error' => $response->getReasonPhrase()]);
            return false;
        }

        $this->logger->debug('[keycloak] used created', ['name' => $uid]);


        if (!$this->create_group($group)) {
            return false;
        }
        if(!$this->add_to_group($uid, $group)) {
            return false;
        }

        return true;
    }

    /**
     * Initialize keycloak with admin user
     */
    public function setup() {
        if($this->url == '') {
            $this->logger->warn('[keycloak] no url set, skipping');
            return;
        }
        $this->token = $this->admin_token();
        $this->create_realm(Configuration::get('keycloak_realm', 'pfm'));
        $this->create_user(
            Configuration::get('admin_user', 'admin'),
            Configuration::get('admin_password', 'admin'),
            Configuration::get('admin_email', 'admin@pfm.org'),
            'pfmadmin'
        );
        // TODO check existing users to migrate them to keycloak
    }
}
?>