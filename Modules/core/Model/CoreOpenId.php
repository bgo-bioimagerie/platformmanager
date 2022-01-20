<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

Class CoreOpenId extends Model {

    /**
     * Create the status table
     *
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_openid` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`provider` varchar(30) NOT NULL DEFAULT '',
        `oid` varchar(255) NOT NULL DEFAULT '',
        `user` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function add($provider, $oid, $user) {
        $sql = "SELECT * FROM core_openid WHERE user=? AND provider=?";
        $exists = $this->runRequest($sql, array(intval($user), $provider));

        if($exists == null || $exists->rowCount() == 0) {
            $sql = "INSERT INTO core_openid (user, provider, oid) VALUES (?, ?, ?)";
            $this->runRequest($sql, array($user, $provider, $oid));
        }
    }

    public function del($provider, $user) {
        $sql = "DELETE FROM core_openid WHERE user=? AND provider=?";
        $this->runRequest($sql, array($user, $provider));
    }

    public function getByOid($provider, $oid) {
        $sql = "SELECT * FROM core_openid WHERE oid=? AND provider=?";
        return $this->runRequest($sql, array($oid, $provider))->fetch();    
    }

    public function list($user_id) {
        $sql = "SELECT * FROM core_openid WHERE user=?";
        return $this->runRequest($sql, array($user_id))->fetchAll();
    }

}

?>
