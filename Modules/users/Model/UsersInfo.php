<?php

require_once 'Framework/Model.php';

class UsersInfo extends Model {

    public function __construct() {
        $this->tableName = "users_info";
        //$this->setColumnsInfo("id", "int(11)", "");
        //$this->setColumnsInfo("id_core", "int(11)", 0);
        //$this->setColumnsInfo("phone", "varchar(100)", "");
        //$this->setColumnsInfo("unit", "varchar(255)", "");
        //$this->setColumnsInfo("avatar", "varchar(255)", "");
        //$this->setColumnsInfo("bio", "text", "");
        //$this->primaryKey = "id";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `users_info` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_core` int NOT NULL DEFAULT '0',
            `phone` varchar(100) DEFAULT NULL,
            `unit` varchar(255) DEFAULT NULL,
            `avatar` varchar(255) DEFAULT NULL,
            `bio` text,
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function get($id_core) {
        $sql = "SELECT * FROM users_info WHERE id_core=?";
        return $this->runRequest($sql, array($id_core))->fetch();
    }

    public function set($id_core, $phone, $unit) {
        if (!$this->exists($id_core)) {
            $sql = 'INSERT INTO users_info (id_core, phone, unit) VALUES (?,?,?)';
            $this->runRequest($sql, array($id_core, $phone, $unit));
        } else {
            $sql = 'UPDATE users_info SET phone=?, unit=? WHERE id_core=?';
            $this->runRequest($sql, array($phone, $unit, $id_core));
        }
    }
    
    public function exists($id_core){
        $sql = "SELECT id FROM users_info WHERE id_core=?";
        $req = $this->runRequest($sql, array($id_core));
        if ($req->rowCount() > 0){
            return true;
        }
        return false;
    }
    
    public function setAvatar($id_core, $avatar){
        $sql = "UPDATE users_info SET avatar=? WHERE id_core=?";
        $this->runRequest($sql, array($avatar, $id_core));
    }
    
    public function setBio($id_core, $bio){
        $sql = "UPDATE users_info SET bio=? WHERE id_core=?";
        $this->runRequest($sql, array($bio, $id_core));
    }

    public function delete($id_core) {
        $sql = "DELETE FROM users_info WHERE id_core=?";
        $this->runRequest($sql, array($id_core));
    }

}
