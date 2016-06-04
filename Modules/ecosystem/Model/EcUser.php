<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';

/**
 * Class defining the User model
 *
 * @author Sylvain Prigent
 */
class EcUser extends Model {

    /**
     * Create the user table
     *
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `ec_users` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`tel` varchar(30) NOT NULL DEFAULT '',
		`id_unit` int(11) NOT NULL DEFAULT 1,
		`id_status` int(11) NOT NULL DEFAULT 1,
		`convention` int(11) NOT NULL DEFAULT 0,		
		`date_convention` DATE NOT NULL DEFAULT '0000-00-00',									
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

}
