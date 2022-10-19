<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class BkPrice extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_prices";
    }

    public function createTable()
    {
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

    public function getPrice($idSpace, $id_resource, $id_belongings)
    {
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getDayPrice($idSpace, $id_resource, $id_belongings)
    {
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "day", $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getNightPrice($idSpace, $id_resource, $id_belongings)
    {
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "night", $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getWePrice($idSpace, $id_resource, $id_belongings)
    {
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, "we", $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getPackagePrice($idSpace, $package_id, $resources_id, $belonging_id)
    {
        $sql = "SELECT price FROM bk_prices WHERE id_resource=? AND id_belonging=? AND id_package=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($resources_id, $belonging_id, $package_id, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function setPriceDay($idSpace, $id_resource, $id_belongings, $price)
    {
        if ($this->isPriceDay($idSpace, $id_resource, $id_belongings, "day")) {
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "day", $idSpace));
        } else {
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "day", $idSpace));
        }
    }

    public function import(
        $idSpace,
        $id_resource,
        $id_package,
        $day_night_we,
        $id_belonging,
        $price
    )
    {
        $sql = "INSERT INTO bk_prices (id_resource, id_package, id_belonging, price, day_night_we, id_space) VALUES (?,?,?,?,?,?)";
        $this->runRequest($sql, array($id_resource, $id_package, $id_belonging, $price, $day_night_we, $idSpace));
    }

    public function setPriceNight($idSpace, $id_resource, $id_belongings, $price)
    {
        if ($this->isPriceDay($idSpace, $id_resource, $id_belongings, "night")) {
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "night", $idSpace));
        } else {
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "night", $idSpace));
        }
    }

    public function setPriceWe($idSpace, $id_resource, $id_belongings, $price)
    {
        if ($this->isPriceDay($idSpace, $id_resource, $id_belongings, "we")) {
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, "we", $idSpace));
        } else {
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, "we", $idSpace));
        }
    }

    public function setPricePackage($idSpace, $id_resource, $id_belongings, $id_package, $price)
    {
        if ($this->isPricePackage($idSpace, $id_resource, $id_belongings, $id_package)) {
            $sql = "UPDATE bk_prices SET price=? WHERE id_resource=? AND id_belonging=? AND id_package=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_belongings, $id_package, $idSpace));
        } else {
            $sql = "INSERT INTO bk_prices (id_resource, id_belonging, price, id_package, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_belongings, $price, $id_package, $idSpace));
        }
    }

    public function isPricePackage($idSpace, $id_resource, $id_belongings, $id_package)
    {
        $sql = "SELECT id FROM bk_prices WHERE id_resource=? AND id_belonging=? AND id_package=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, $id_package, $idSpace));
        if ($req->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function isPriceDay($idSpace, $id_resource, $id_belongings, $day)
    {
        $sql = "SELECT id FROM bk_prices WHERE id_resource=? AND id_belonging=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_belongings, $day, $idSpace));
        return ($req->rowCount() > 0);
    }
}
