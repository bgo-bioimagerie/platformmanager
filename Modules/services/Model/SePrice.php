<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class SePrice extends Model {

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
    
    public function getPrice($id_service, $id_belongings){
        $sql = "SELECT price FROM se_prices WHERE id_service=? AND id_belonging=?";
        $req = $this->runRequest($sql, array($id_service, $id_belongings));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function setPrice($id_service, $id_belongings, $price){
        if ($this->isPrice($id_service, $id_belongings)){
            $sql = "UPDATE se_prices SET price=? WHERE id_service=? AND id_belonging=?";
            $this->runRequest($sql, array($price, $id_service, $id_belongings));
        }
        else{
            $sql = "INSERT INTO se_prices (id_service, id_belonging, price) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_service, $id_belongings, $price));
        }
    }
    
    public function isPrice($id_service, $id_belongings){
        $sql = "SELECT id FROM se_prices WHERE id_service=? AND id_belonging=?";
        $req = $this->runRequest($sql, array($id_service, $id_belongings));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }


}
