<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/Email.php';
require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CorecookiesecureController.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreOpenId.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/users/Model/UsersInfo.php';




/**
 * 
 * @author osallou
 * Controller for shibboleth connections
 */
class ShibbolethController extends CorecookiesecureController {

    public function connectAction() {
        // TODO remove, for debug only
        Configuration::getLogger()->debug('[ooc][shibboleth]', ['server' => $_SERVER]);
        $lang = $this->getLanguage();
        if(!isset($_SERVER["REDIRECT_REMOTE_USER"])) {
            throw new PfmAuthException('not authenticated');
        }
        $oid = $_SERVER["REDIRECT_REMOTE_USER"];
        $email = $_SERVER["REDIRECT_mail"];
        $firstName = $_SERVER['REDIRECT_givenName'] ?? '';
        $name = $_SERVER['REDIRECT_sn'] ?? '';
        $unit = $_SERVER['REDIRECT_eduPersonOrgUnitDN'] ?? '';
        $openidModel = new CoreOpenId();
        $oidUser = $openidModel->getByOid('shibboleth', $oid);

        $m = new CoreUser();

        if(!$oidUser) {
            Configuration::getLogger()->debug('[ooc][shibboleth] oid not found');
            $userExists = $m->getUserByEmail($email);
            Configuration::getLogger()->debug('[ooc][shibboleth] check email exists', ['user' => $userExists]);
            if($userExists) {
                $openidModel->add('shibboleth', $oid, $userExists['id']);
                $_SESSION['flash'] = 'Account linked to '.$userExists['login'];
                $_SESSION['flashClass'] = 'success';
            } else {
                Configuration::getLogger()->debug('[ooc][shibboleth] create user', ['oid' => $oid]);
                // create it
                $pwd = $m->generateRandomPassword();
                $login = $firstName[0].$name;
                if(isset($_SERVER['uid'])) {
                    $login = $_SERVER['uid'];
                }
                $login = preg_replace('/[^a-zA-Z0-9\-_]/', '', $login);
                $login = substr($login, 0, 100);
                $count = 1;
                while($m->isLogin($login)) {
                    $login = $login.$count;
                    if($count > 100) {
                        throw new PfmException('Could not get a valid login');
                    }
                }
                $userId = $m->createAccount($login, $pwd, $name, $firstName, $email);
                $modelUsersInfo = new UsersInfo();
                $modelUsersInfo->set(
                    $userId,
                    '',
                    '',
                    $unit
                );
                $openidModel->add('shibboleth', $oid, $userId);
                $userFullName = $m->getUserFUllName($userId);

                $em = new Email();
                $mailParams = [
                    "email" => $email,
                    "login" => $login,
                    "fullName" => $userFullName,
                    "name" => $name,
                    "pwd" => $pwd
                ];
                $em->notifyUserByEmail($mailParams, "add_new_user", $lang);
                $_SESSION['flash'] = 'Account created: '.$login;
                $_SESSION['flashClass'] = 'success';
                Configuration::getLogger()->debug('[ooc][shibboleth] created user', ['oid' => $oid, 'user' => $login]);
            }
            $oidUser = $openidModel->getByOid('shibboleth', $oid);
        }
        $user = $m->getUser($oidUser['user']);
        $m->editBaseInfo($user['id'], $name, $firstName, $email);
        $login = $user['login'];
        $user = $this->initSession($login);
        Configuration::getLogger()->debug('[oid][orcid] open session for user', ['oid' => $oid, 'user' => $user, 'login' => $login]);
        $this->request->getSession()->setAttribut("oid", $oid);
        return $this->redirect('coretiles');
    }

}