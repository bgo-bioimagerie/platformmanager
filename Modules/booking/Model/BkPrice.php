<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class BkPrice extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_prices` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_resource` int(11) NOT NULL DEFAULT 0,
                `id_package` int(11) NOT NULL DEFAULT 0,
                `day_night_we` varchar(6) NOT NULL DEFAULT '',
                `id_belonging` int(11) NOT NULL DEFAULT 0,
                `price` varchar(128) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }
    
    public function getPrice($id_resource, $id_belongings){
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getDayPrice($id_resource, $id_belongings){
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "day"));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getNightPrice($id_resource, $id_belongings){
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "night"));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getWePrice($id_resource, $id_belongings){
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "we"));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getPackagePrice($package_id, $resources_id, $belonging_id){
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND id_package=?";
        $req = $this->runRequest($sql, array($resources_id, $belonging_id, $package_id));
        if ($req->rowCount() == 1){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function setPriceDay($id_resource, $id_belongings, $price){
        if ($this->isPriceDay($id_resource, $id_belongings, "day")){
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "day"));
        }
        else{
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "day"));
        }
    }
    
    public function import($id_resource, $id_package, $day_night_we, 
                    $id_belonging, $price){
        $sql = "INSERT INTO bk_prices (id_resource, id_package, id_belonging, price, day_night_we) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_package, $id_belonging, $price, $day_night_we));
    }
    
    public function setPriceNight($id_resource, $id_belongings, $price){
        if ($this->isPriceDay($id_resource, $id_belongings, "night")){
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "night"));
        }
        else{
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "night"));
        }
    }
    
    public function setPriceWe($id_resource, $id_belongings, $price){
        if ($this->isPriceDay($id_resource, $id_belongings, "we")){
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "we"));
        }
        else{
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "we"));
        }
    }
    
    public function setPricePackage($id_resource, $id_belongings, $id_package, $price){
        if ($this->isPricePackage($id_resource, $id_belongings, $id_package)){
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND id_package=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, $id_package));
        }
        else{
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, id_package) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, $id_package));
        }
    }
    
    public function isPricePackage($id_resource, $id_belongings, $id_package){
        $sql = "SELECT id FROM bk_prices WHERE id_resource=? AND id_belonging=? AND id_package=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, $id_package));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }
    
    public function isPriceDay($id_resource, $id_belongings, $day){
        $sql = "SELECT id FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, $day));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }


}
