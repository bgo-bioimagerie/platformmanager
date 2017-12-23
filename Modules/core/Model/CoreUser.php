<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreLdap.php';

class CoreUser extends Model {

    public function __construct() {
        $this->tableName = "core_users";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("login", "varchar(100)", "");
        $this->setColumnsInfo("pwd", "varchar(100)", "");
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->setColumnsInfo("firstname", "varchar(100)", "");
        $this->setColumnsInfo("email", "varchar(255)", "");
        $this->setColumnsInfo("status_id", "int(2)", 0);
        $this->setColumnsInfo("source", "varchar(30)", "local");
        $this->setColumnsInfo("is_active", "int(1)", 1);
        $this->setColumnsInfo("date_created", "date", "");
        $this->setColumnsInfo("date_end_contract", "date", "");
        $this->setColumnsInfo("date_last_login", "date", "");
        $this->setColumnsInfo("remember_key", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function mergeUsers($users) {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "DELETE FROM core_users WHERE id=?";
            $this->runRequest($sql, array($users[$i]));
        }
    }

    public function disableUsers($desactivateSetting) {


        $date = date('Y-m-d', time());
        $oneyearago = date('Y-m-d', strtotime($date . ' -1 year'));


        if ($desactivateSetting == 6) {
            $sql = "SELECT * FROM core_users WHERE (date_last_login!=? "
                    . "AND date_last_login<? ) "
                    . "OR (date_end_contract!=? AND date_end_contract < ?)";

            $req = $this->runRequest($sql, array('0000-00-00', $oneyearago, '0000-00-00', $date))->fetchAll();

            foreach ($req as $r) {
                $sql = "UPDATE core_j_spaces_user SET status=0 WHERE id_user=?";
                $this->runRequest($sql, array($r['id']));
            }
        }
        /// \todo implement other cases
    }

    public function getRemeberKey($id) {
        $sql = "SELECT remember_key FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        $data = $req->fetch();
        return $data[0];
    }

    public function setRememberKey($id, $key) {
        $sql = "UPDATE core_users SET remember_key=? WHERE id=?";
        $this->runRequest($sql, array($key, $id));
    }

    public function getUserLogin($id) {
        $sql = "select login from core_users where id=?";
        $user = $this->runRequest($sql, array(
            $id
        ));
        if ($user->rowCount() == 1) {
            $userf = $user->fetch();
            return $userf[0];
        } else {
            return "";
        }
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM core_users WHERE email=?";
        $user = $this->runRequest($sql, array($email));
        //echo 'found ' . $user->rowCount() . "users <br/>";
        if ($user->rowCount() == 1) {
            $userf = $user->fetch();
            return $userf;
        } else {
            return false;
        }
    }

    public function getUserInitiales($id) {
        $sql = "select firstname, name from core_users where id=?";
        $user = $this->runRequest($sql, array(
            $id
        ));

        if ($user->rowCount() == 1) {
            $userf = $user->fetch();
            return substr(ucfirst($userf ['name']), 0, 1) . " " . substr(ucfirst($userf ['firstname']), 0, 1);
        } else {
            return "";
        }
    }

    public function getStatus($id_user) {
        $sql = "SELECT status_id FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($id_user));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getEmail($id_user) {
        $sql = "SELECT email FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($id_user));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function installDefault() {
        if (!$this->exists(1)) {
            $sql = "INSERT INTO core_users (login, pwd, name, firstname, email, status_id, source, date_created) VALUES(?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array("admin", md5("admin"), "admin", "admin", "admin@admin.com", 5, "local", date("Y-m-d")));
        }
    }

    public function exists($id) {
        $sql = "select id from core_users where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function add($login, $pwd, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $encrypte = true) {

        $pwde = $pwd;
        if ($encrypte) {
            $pwde = md5($pwd);
        }

        $datecreated = date("Y-m-d", time());

        $sql = "INSERT INTO core_users (login, pwd, name, firstname, email, status_id, date_end_contract, is_active, date_created) VALUES(?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($login, $pwde, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $datecreated));
        return $this->getDatabase()->lastInsertId();
    }

    public function edit($id, $login, $name, $firstname, $email, $status_id, $date_end_contract, $is_active) {

        $sql = "UPDATE core_users SET login=?, name=?, firstname=?, email=?, status_id=?, date_end_contract=?, is_active=? WHERE id=?";
        $this->runRequest($sql, array($login, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $id));
    }

    public function isUserId($id) {
        $sql = "SELECT * FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function importUser($login, $pwd, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $source) {
        $sql = "SELECT id FROM core_users WHERE login=?";
        $req = $this->runRequest($sql, array($login));
        //echo "import user " . $login . "row count " . $req->rowCount();
        if ($req->rowCount() == 0) {
            $sql = "INSERT INTO core_users (login, pwd, name, firstname, email, status_id, date_end_contract, is_active, source) VALUES(?,?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($login, $pwd, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $source));
            return $this->getDatabase()->lastInsertId();
        } else {
            $u = $req->fetch();
            return $u[0];
        }
    }

    public function getUser($id) {
        $sql = "select * from core_users where id=?";
        $req = $this->runRequest($sql, array($id));
        return $req->fetch();
    }

    public function getpwd($id) {
        $sql = "select pwd from core_users where id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req;
    }

    public function getEmpty() {
        return array("id" => 0,
            "login" => "",
            "pwd" => "",
            "name" => "",
            "firstname" => "",
            "email" => "",
            "status_id" => 0,
            "source" => "local",
            "is_active" => 1,
            "date_last_login" => "",
            "date_end_contract" => "",
            "date_created" => "");
    }

    /**
     * Check if a local user with a given login exists
     * @param string $login Local login
     * @return boolean
     */
    public function isLocalUser($login) {
        $sql = "select id from core_users where login=? AND source=?";
        $user = $this->runRequest($sql, array(
            $login, "local"
        ));
        if ($user->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verify that a user is in the database
     *
     * @param string $login
     *        	the login
     * @param string $pwd
     *        	the password
     * @return boolean True if the user is in the database
     */
    public function connect($login, $pwd) {
        $sql = "select id, is_active from core_users where login=? and pwd=?";
        $user = $this->runRequest($sql, array(
            $login,
            md5($pwd)
        ));
        if ($user->rowCount() == 1) {
            $req = $user->fetch();
            if ($req ["is_active"] == 1) {
                return "allowed";
            } else {
                return "Your account is not active";
            }
        } else {
            return "Login or password not correct";
        }
    }

    /**
     * Get the user informations from login
     * @param string $login User login
     * @throws Exception
     * @return array User info (id, login, pwd, id_status, is_active)
     */
    public function getUserByLogin($login) {
        $sql = "select id as idUser, login as login, pwd as pwd, status_id, is_active
            from core_users where login=?";
        $user = $this->runRequest($sql, array(
            $login
        ));
        if ($user->rowCount() == 1) {
            return $user->fetch(); // get the first line of the result
        } else {
            throw new Exception("Cannot find the user using the given parameters");
        }
    }

    public function getUserIDByLogin($login) {
        $sql = "select id from core_users where login=?";
        $user = $this->runRequest($sql, array(
            $login
        ));
        if ($user->rowCount() > 0) {
            $tmp = $user->fetch();
            return $tmp[0]; // get the first line of the result
        } else {
            return 0;
        }
    }

    /**
     * 
     * @param type $login
     * @return int
     */
    public function getIdByLogin($login) {
        $sql = "SELECT id FROM core_users WHERE login=?";
        $user = $this->runRequest($sql, array(
            $login
        ));
        if ($user->rowCount() == 1) {
            $data = $user->fetch();
            return $data[0];
        } else {
            return 0;
        }
    }

    /**
     * Verify that a user is in the database
     *
     * @param string $login
     *        	the login
     * @return boolean True if the user is in the database
     */
    public function isUser($login) {
        $sql = "select id from core_users where login=?";
        $user = $this->runRequest($sql, array(
            $login
        ));
        if ($user->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update the last login date attribut to the todau date
     *
     * @param int $userId
     *        	Id of the user to update
     */
    public function updateLastConnection($userId) {
        $sql = "update core_users set date_last_login=? where id=?";
        $this->runRequest($sql, array(
            "" . date("Y-m-d") . "",
            $userId
        ));
    }

    public function setLastConnection($userId, $time) {
        $sql = "update core_users set date_last_login=? where id=?";
        $this->runRequest($sql, array(
            $time, $userId
        ));
    }

    public function getLastConnection($userId) {
        $sql = "SELECT date_last_login FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($userId))->fetch();
        return $req[0];
    }

    /**
     * Update user to active or unactive depending on the settings criteria
     */
    public function updateUsersActive() {

        $modelConfig = new CoreConfig ();
        $desactivateType = $modelConfig->getParam("user_desactivate");

        if ($desactivateType > 1) {
            if ($desactivateType == 2) {
                $this->updateUserActiveContract();
            } else if ($desactivateType == 3) {
                $this->updateUserActiveLastLogin(1);
            } else if ($desactivateType == 4) {
                $this->updateUserActiveLastLogin(2);
            } else if ($desactivateType == 5) {
                $this->updateUserActiveLastLogin(3);
            } else if ($desactivateType == 6) {
                $this->updateUserActiveContract();
                $this->updateUserActiveLastLogin(1);
            }
        }
    }

    /**
     * Set a user active
     * @param number $id User ID
     * @param number $active Active status
     */
    public function setactive($id, $active) {
        $sql = "update core_users set is_active=? where id=?";
        $this->runRequest($sql, array(
            $active,
            $id
        ));
    }

    public function getUserFUllName($id) {
        $sql = "SELECT name, firstname FROM core_users WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $data = $req->fetch();
            return $data["name"] . " " . $data["firstname"];
        }
        return "";
    }

    /**
     * Set unactive user who contract ended
     */
    private function updateUserActiveContract() {
        $sql = "select id, date_end_contract from core_users where is_active=1";
        $req = $this->runRequest($sql);
        $users = $req->fetchAll();

        foreach ($users as $user) {
            $contractDate = $user ["date_end_contract"];
            $today = date("Y-m-d", time());

            if ($contractDate != "0000-00-00") {
                if ($contractDate < $today) {
                    $this->setactive($user["id"], 0);

                    // desactivate authorizations
                    $sql = "UPDATE bk_authorization SET is_active=0, date_desactivation=? WHERE user_id=?";
                    $this->runRequest($sql, array($user ['id'], date("Y-m-s"), time()));
                }
            }
        }
    }

    /**
     * Unactivate users who did not login for a number of year given in $numberYear
     * @param number $numberYear Number of years
     */
    private function updateUserActiveLastLogin($numberYear) {
        $sql = "select id, date_last_login, date_created from core_users where is_active=1";
        $req = $this->runRequest($sql);
        $users = $req->fetchAll();

        foreach ($users as $user) {

            // get the last login date in second
            $lastLoginDate = $user ["date_last_login"];
            if ($lastLoginDate != "0000-00-00") {

                $lastLoginDate = explode("-", $lastLoginDate);
                $timell = mktime(0, 0, 0, $lastLoginDate [1], $lastLoginDate [2], $lastLoginDate [0]);
                $timell = date("Y-m-d", $timell + $numberYear * 31556926);
                $today = date("Y-m-d", time());

                // get the date created in seconds
                $createdDate = $user ["date_created"];
                $createdDate = explode("-", $createdDate);
                $timec = mktime(0, 0, 0, $createdDate [1], $createdDate [2], $createdDate [0]);
                $timec = date("Y-m-d", $timec + $numberYear * 31556926);
                /*
                  if ($user["name"] == "test"){
                  print_r($createdDate);
                  print_r($lastLoginDate);
                  echo "today = " . $today . "<br/>";
                  echo "timell = " . $timell . "<br/>";
                  echo "timec = " . $timec . "<br/>";
                  }
                 */
                $changedUsers = array();
                if ($timec <= $today) {
                    if ($timell <= $today) {
                        $this->setactive($user ['id'], 0);
                        $changedUsers [] = $user ['id'];
                    }
                }
            } else {
                //echo 'try to desactivate ' . $user ['id'] . " with authorizations <br>";
                $sql = "SELECT * FROM bk_authorization WHERE user_id=? ORDER BY date DESC";
                $req = $this->runRequest($sql, array($user ['id']));
                if ($req->rowCount() > 0) {
                    $data = $req->fetch();

                    $date_y = date('Y', time()) - $numberYear;
                    $dateref = $date_y . "-" . date("m-d", time());
                    if ($data["date"] != "0000-00-00" && $data["date"] < $dateref) {
                        //echo 'desactivate ' . $user ['id'] . " with authorizations <br>";
                        $this->setactive($user ['id'], 0);

                        // desactivate authorizations
                        $sql = "UPDATE bk_authorization SET is_active=0, date_desactivation=? WHERE user_id=?";
                        $this->runRequest($sql, array($user ['id'], date("Y-m-s"), time()));
                    }
                }
            }
        }
    }

    /**
     * Change the password of a user
     *
     * @param int $id
     *        	Id of the user to edit
     * @param string $pwd
     *        	new password
     */
    public function changePwd($id, $pwd) {
        $sql = "update core_users set pwd=? where id=?";
        $this->runRequest($sql, array(
            md5($pwd),
            $id
        ));
    }

    public function isLogin($login) {
        $sql = "select * from core_users where login=?";
        $user = $this->runRequest($sql, array($login));
        if ($user->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the users information
     *
     * @param string $sortentry
     *        	column used to sort the users
     * @return multitype:
     */
    public function getActiveUsers($sortentry = 'id', $is_active = 1) {
        $sql = "select * from core_users where is_active=" . $is_active . " order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * Get the users summary (id, name, firstname)
     *
     * @param string $sortentry
     *        	column used to sort the users
     * @return multitype:
     */
    public function getUsersSummary($sortentry = 'id', $active = 1) {
        $sql = "select id, name, firstname from core_users where is_active >= " . $active . " order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * Set (add if not exists, update otherwise) external (i.e. LDAP) user info
     * @param unknown $login
     * @param unknown $name
     * @param unknown $firstname
     * @param unknown $email
     * @param unknown $id_status
     */
    public function setExtBasicInfo($login, $name, $firstname, $email, $id_status) {

        // insert
        if (!$this->isUser($login)) {
            $sql = "insert into core_users(login, firstname, name, email, status_id, source, date_created)" . " values(?, ?, ?, ?, ?, ?, ?)";
            $this->runRequest($sql, array(
                $login,
                $firstname,
                $name,
                $email,
                $id_status,
                "ext",
                "" . date("Y-m-d") . ""
            ));
        }
        // update
        else {
            $sql = "update core_users set firstname=?, name=?, email=?
    			                  where login=?";
            $this->runRequest($sql, array(
                $firstname,
                $name,
                $email,
                $login
            ));
        }
    }

    /**
     * Check if a user is active
     * @param string $login User login
     * @return string Error or success message
     */
    public function isActive($login) {
        $sql = "select id, is_active from core_users where login=?";
        $user = $this->runRequest($sql, array(
            $login
        ));
        if ($user->rowCount() == 1) {
            $req = $user->fetch();
            if ($req ["is_active"] == 1) {
                return "allowed";
            } else {
                return "Your account is not active";
            }
        } else {
            return "Login or password not correct";
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM core_users WHERE id=?";
        $this->runRequest($sql, array($id));
    }

    public function login($login, $pwd) {

        // test if local account
        if ($this->isLocalUser($login)) {
            //echo "found local user <br/>";
            return $this->connect($login, $pwd);
        }

        // search for LDAP account
        else {
            //echo "into LDap <br/>";
            $modelCoreConfig = new CoreConfig();
            if ($modelCoreConfig->getParam("useLdap") == true) {

                $modelLdap = new CoreLdap();
                $ldapResult = $modelLdap->getUser($login, $pwd);
                if ($ldapResult == "error") {
                    return "Cannot connect to ldap using the given login and password";
                } else {
                    // update the user infos
                    $status = $modelCoreConfig->getParam("ldapDefaultStatus");
                    $this->user->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);

                    $userInfo = $this->user->getUserByLogin($login);
                    //print_r($userInfo);

                    $modelSpace = new CoreSpace();
                    $spacesToActivate = $modelSpace->getSpaces('id');
                    foreach ($spacesToActivate as $spa) {
                        $modelSpace->setUserIfNotExist($userInfo['idUser'], $spa['id'], $status);
                    }

                    return $this->user->isActive($login);
                }
            }
        }

        return "Login or password not correct";
    }

}
