<?php
require_once 'Framework/Request.php';
require_once 'Framework/Errors.php';

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpace.php';


use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    

    private static mixed $context = [
        'users' => ['admin1', 'manager1', 'user1', 'admin2', 'manager2', 'user2'],
        'spaces' => [
            'space1' => [
                "plan" => 1, "admins" => ["admin1"], "managers" => ["manager1"], "users" => ["user1", "user11"]
            ],
            'space2' => [
                "plan" => 0, "admins" => ["admin2"], "managers" => ["manager2"], "users" => ["user2"]
            ]
        ]
    ];

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '';
    }

    public function request(array $params): Request
    {
        $req = new Request($params, true);
        $req->getSession()->setAttribut("id_user", $_SESSION['id_user']);
        $req->getSession()->setAttribut("login", $_SESSION['login']);
        $req->getSession()->setAttribut("email", 'fake@pfm.org');
        $req->getSession()->setAttribut("company", Configuration::get("name"));
        $req->getSession()->setAttribut("user_status", $_SESSION['user_status']);
        return $req;
    }

    public function Context():mixed
    {
        return self::$context;
    }

    protected function asAdmin(int $id_space=0): mixed
    {
        $u = $this->asUser(Configuration::get('admin_user', 'pfmadmin'), $id_space);
        $_SESSION['user_status'] = CoreStatus::$ADMIN;
        return $u;
    }

    protected function asAnon(): void
    {
        $_SESSION['user_status'] = CoreStatus::$USER;
        $_SESSION['id_user'] = -1;
        $_SESSION['login'] = 'anonymous';
    }

    protected function asUser(string $name, int $id_space=0): mixed
    {
        Configuration::getLogger()->debug("[runas:$name]");
        $m = new CoreUser();
        $user = $m->getUserByLogin($name);
        if (!$user) {
            throw new PfmException("user not found ".$name);
        }
        $_SESSION['user_status'] = CoreStatus::$USER;
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['id_space'] = $id_space;
        $_SESSION['user_settings'] = ["language" => "en"];
        $_SESSION['login'] = $name;
        return $user;
    }

    protected function user(string $name): mixed
    {
        $m = new CoreUser();
        $u = $m->getUserByLogin($name);
        return $u;
    }

    protected function space(string $name): mixed
    {
        $spaces = $this->spaces();
        foreach ($spaces as $space) {
            if ($space['name'] == $name) {
                return $space;
            }
        }
        throw new PfmException('space not found '.$name);
    }

    protected function spaces(): array
    {
        $m = new CoreSpace();
        return $m->getSpaces('id');
    }
 
}