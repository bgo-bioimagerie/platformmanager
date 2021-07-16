<?php
require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Virtual counter
 */
class CoreVirtual extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `core_virtual` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(150) NOT NULL,
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    /**
     * Create an new virtual entry
     */
    public function new($name) {
        $sql = 'INSERT INTO core_virtual (name) VALUES (?)';
        $this->runRequest($sql, array($name));
        return $this->getDatabase()->lastInsertId();
    }

}


?>