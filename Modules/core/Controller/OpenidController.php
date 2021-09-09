<?php
require_once 'Framework/Configuration.php';

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CorecookiesecureController.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreOpenId.php';
require_once 'Modules/core/Model/CoreUser.php';


use GuzzleHttp\Client;
/**
 * 
 * @author osallou
 * Controller for openid connections
 */
class OpenidController extends CorecookiesecureController {

/**
     * Check ORCID code for user authentication
     */
    private function google($code) {
        Configuration::getLogger()->debug('[openid][google] check');
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => Configuration::get('openid_google_url'),
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $req = [
            'client_id' =>  Configuration::get('openid_google_client_id'),
            'client_secret' =>  Configuration::get('openid_google_client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => Configuration::get("public_url")."/ooc/google/authorized",
        ];
        $response = $client->request('POST',
            '/token',
            [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => $req
            ]
        );
        
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[oid][google] error', ['code' => $status, 'error' => $response->getMessage()]);
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        Configuration::getLogger()->debug('google auth answer', ['data' => $json]);
        $token = $json['access_token'];

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://openidconnect.googleapis.com',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET',
            '/v1/userinfo',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
            ]
        );

        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[oid][google] error', ['code' => $status, 'error' => $response->getMessage()]);
            return null;
        }

        $body = $response->getBody();
        $json = json_decode($body, true);
        Configuration::getLogger()->debug('google userinfo answer', ['data' => $json]);

        $oid = $json['sub'];

        $login = "";
        // if authenticated add or update link
        if( $_SESSION["redirect"] == "usersmyaccount") {
            Configuration::getLogger()->debug('[oid][google] session', ['session' => $_SESSION]);
            $login = $_SESSION['login'];
            Configuration::getLogger()->debug('[oid][google] add oid link for user', ['oid' => $oid]);
            $openidModel = new CoreOpenId();
            $openidModel->add('google', $oid, $_SESSION['id_user']);
        } else {
            // Find login user
            $openidModel = new CoreOpenId();
            try {
                $oidUser = $openidModel->getByOid('google', $oid);
                $userModel = new CoreUser();
                $relUser = $userModel->getInfo($oidUser['user']);
                if($relUser) {
                    $login = $relUser['login'];
                }
                Configuration::getLogger()->debug('[oid][google] load oid', ['oid' => $oidUser, 'user' => $relUser]);

            } catch(Exception $e) {
                Configuration::getLogger()->error('[oid][google] no link found', ['oid' => $oid]);
            }
        }

        if($login != "") {
            $user = $this->initSession($login);
            Configuration::getLogger()->debug('[oid][google] open session for user', ['oid' => $oid, 'user' => $user, 'login' => $login]);
            $this->request->getSession()->setAttribut("oid", $oid);
            $login = $user['login'];
        }
        return $login;
    }


    /**
     * Check ORCID code for user authentication
     */
    private function orcid($code) {
        Configuration::getLogger()->debug('[openid][orcid] check');
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => Configuration::get('openid_orcid_url'),
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $req = [
            'client_id' =>  Configuration::get('openid_orcid_client_id'),
            'client_secret' =>  Configuration::get('openid_orcid_client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => Configuration::get("public_url")."/ooc/orcid/authorized",
        ];
        $response = $client->request('POST',
            '/oauth/token',
            [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => $req
            ]
        );
        
        $status = $response->getStatusCode();
        if ($status != 200) {
            Configuration::getLogger()->error('[oid][orcid] error', ['code' => $status, 'error' => $response->getMessage()]);
            return null;
        }
        $body = $response->getBody();
        $json = json_decode($body, true);
        $oid = $json['orcid'];


        $login = "";
        // if authenticated add or update link
        if( $_SESSION["redirect"] == "usersmyaccount") {
            Configuration::getLogger()->debug('[oid][orcid] session', ['session' => $_SESSION]);
            $login = $_SESSION['login'];
            Configuration::getLogger()->debug('[oid][orcid] add oid link for user', ['oid' => $oid]);
            $openidModel = new CoreOpenId();
            $openidModel->add('orcid', $oid, $_SESSION['id_user']);
        } else {
            // Find login user
            $openidModel = new CoreOpenId();
            try {
                $oidUser = $openidModel->getByOid('orcid', $oid);
                $userModel = new CoreUser();
                $relUser = $userModel->getInfo($oidUser['user']);
                if($relUser) {
                    $login = $relUser['login'];
                }
                Configuration::getLogger()->debug('[oid][orcid] load oid', ['oid' => $oidUser, 'user' => $relUser]);

            } catch(Exception $e) {
                Configuration::getLogger()->error('[oid][orcid] no link found', ['oid' => $oid]);
            }
        }

        if($login != "") {
            $user = $this->initSession($login);
            Configuration::getLogger()->debug('[oid][orcid] open session for user', ['oid' => $oid, 'user' => $user, 'login' => $login]);
            $this->request->getSession()->setAttribut("oid", $oid);
            $login = $user['login'];
        }
        return $login;
    }

    /**
     * Check code for external auth providers
     */
    public function connectAction($provider) {
        // Check openid connection
        if(!isset($_GET['code'])) {
            $_SESSION["message"] = "Authentication failed";
            Configuration::getLogger()->debug('[openid][code] no code provided');
            $this->redirect("coreconnection");
            return;
        }
        Configuration::getLogger()->debug('[openid][code]', ['code' => $_GET['code']]);
        $redirect = '';
        if(isset($_SESSION['redirect'])) {
            $redirect = $_SESSION['redirect'];
        }
        $user = '';
        switch ($provider) {
            case 'orcid':
                $user = $this->orcid($_GET['code']);
                break;
            case 'google':
                $user = $this->google($_GET['code']);
                break;
            default:
                $_SESSION['message'] = "unknown provider";
                break;
        }
        if($user == '') {
            $_SESSION['message'] = "$provider connection failed or no link set for this provider in your account settings";
        }
        if($redirect) {
            unset($_SESSION['redirect']);
            Configuration::getLogger()->debug('[openid][code] redirect', ['controller' => $redirect]);
            $this->redirect($redirect);
            return;

        }
        $this->redirect("coreconnection");
    }

}

?>
