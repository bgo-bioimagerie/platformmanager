<?php
require_once 'Framework/Request.php';
require_once 'Framework/Errors.php';

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpace.php';


use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase {
    

    private static mixed $context = [
        'users' => ['admin1', 'manager1', 'user1', 'admin2', 'manager2', 'user2'],
        'spaces' => [
            'space1' => [
                "admins" => ["admin1"], "managers" => ["manager1"], "users" => ["user1"]
            ],
            'space2' => [
                "admins" => ["admin2"], "managers" => ["manager2"], "users" => ["user2"]
            ]
        ]
    ];

    protected function setUp(): void {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '';
    }

    public function Context():mixed {
        return self::$context;
    }

    protected function asAdmin(int $id_space=0): mixed {
        $u = $this->asUser(Configuration::get('admin_user', 'pfmadmin'), $id_space);
        $_SESSION['user_status'] = CoreStatus::$ADMIN;
        return $u;
    }

    protected function asUser(string $name, int $id_space=0): mixed {
        Configuration::getLogger()->debug("[runas:$name]");
        $m = new CoreUser();
        $user = $m->getUserByLogin($name);
        if(!$user) {
            throw new PfmException("user not found ".$name);
        }
        $user['id'] = $user['idUser'];
        $_SESSION['user_status'] = CoreStatus::$USER;
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['id_space'] = $id_space;
        $_SESSION["user_settings"] = ["language" => "en"];
        return $user;
    }

    protected function user(string $name): mixed {
        $m = new CoreUser();
        $u = $m->getUserByLogin($name);
        $u['id'] = $u['idUser'];
        return $u;
    }

    protected function space(string $name): mixed {
        $spaces = $this->spaces();
        foreach($spaces as $space) {
            if ($space['name'] == $name) {
                return $space;
            }
        }
        throw new PfmException('space not found '.$name);
    }

    protected function spaces(): array {
        $m = new CoreSpace();
        return $m->getSpaces('id');
    }
 
}


?>