<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CorePendingAccount.php';

require_once 'Framework/Events.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreStar extends Model {

    public function __construct() {
        $this->tableName = 'core_star';
    }

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_star` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) NOT NULL,
            `id_space` int(11) NOT NULL,
            PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
        $this->baseSchema();
    }
    
    public function stars($id_user){
        $sql = "SELECT * FROM core_star WHERE id_user=?";
        return $this->runRequest($sql, array($id_user))->fetchAll();
    }

    public function star($id_user, $id_space){
        $sql = "SELECT * FROM core_star WHERE id_user=? AND id_space=?";
        $exists = $this->runRequest($sql, array($id_user, $id_space))->fetch();
        if(!$exists) {
            $sql = "INSERT INTO core_star (id_user, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($id_user, $id_space));
        }
    }

    public function delete($id_user, $id_space) {
        $sql = "DELETE FROM core_star WHERE id_user=? AND id_space=?";
        $this->runRequest($sql, array($id_user, $id_space));
    }

    public function deleteSpace($id_space) {
        $sql = "DELETE FROM core_star WHERE id_space=?";
        $this->runRequest($sql, array($id_space));
    }

}
