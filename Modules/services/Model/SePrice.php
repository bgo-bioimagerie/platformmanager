<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class SePrice extends Model {

    public function __construct() {
        $this->tableName = 'se_prices';
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `se_prices` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_service` int(11) NOT NULL DEFAULT 0,
                `id_belonging` int(11) NOT NULL DEFAULT 0,
                `price` varchar(128) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
        
    }
    
    public function getPrice($id_space, $id_service, $id_belongings){
        $sql = "SELECT price FROM se_prices WHERE id_service=? AND id_belonging=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_service, $id_belongings, $id_space));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function setPrice($id_space, $id_service, $id_belongings, $price){
        if ($this->isPrice($id_space, $id_service, $id_belongings)){
            $sql = "UPDATE se_prices SET price=? WHERE id_service=? AND id_belonging=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($price, $id_service, $id_belongings, $id_space));
        }
        else{
            $sql = "INSERT INTO se_prices (id_service, id_belonging, price, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_service, $id_belongings, $price, $id_space));
        }
    }
    
    public function isPrice($id_space, $id_service, $id_belongings){
        $sql = "SELECT id FROM se_prices WHERE id_service=? AND id_belonging=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_service, $id_belongings, $id_space));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }


}
