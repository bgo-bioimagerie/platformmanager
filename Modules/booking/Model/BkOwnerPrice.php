<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class BkOwnerPrice extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_owner_prices` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_resource` int(11) NOT NULL DEFAULT 0,
                `id_package` int(11) NOT NULL DEFAULT 0,
                `day_night_we` varchar(6) NOT NULL DEFAULT '',
                `id_unit` int(11) NOT NULL DEFAULT 0,
                `price` varchar(128) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function removeNotListed($id_resource, $units) {

        $sql = "SELECT * FROM bk_owner_prices";
        $data = $this->runRequest($sql)->fetchAll();
        foreach ($data as $d) {
            $found = false;
            for ($i = 0; $i < count($id_resource); $i++) {
                $residArray = explode("_", $id_resource[$i]);
                if ($residArray[1] == "day") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["day_night_we"] == "day") {
                        $found = true;
                        break;
                    }
                } else if ($residArray[1] == "night") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["day_night_we"] == "night") {
                        $found = true;
                        break;
                    }
                } else if ($residArray[1] == "we") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["day_night_we"] == "we") {
                        $found = true;
                        break;
                    }
                } else if ($residArray[1] == "pk") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["id_package"] == $residArray[2]) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found){
                $sql = "DELETE FROM bk_owner_prices WHERE id=?";
                $this->runRequest($sql, array($d["id"]));
            }
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM bk_owner_prices";
        return $this->runRequest($sql)->fetchAll();
    }

    public function getPrice($id_resource, $id_units) {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getDayPrice($id_resource, $id_units) {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, "day"));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getNightPrice($id_resource, $id_units) {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, "night"));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getWePrice($id_resource, $id_units) {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, "we"));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getPackagePrice($package_id, $resources_id, $belonging_id) {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND id_package=?";
        $req = $this->runRequest($sql, array($resources_id, $belonging_id, $package_id));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function setPriceDay($id_resource, $id_units, $price) {
        if ($this->isPriceDay($id_resource, $id_units, "day")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "day"));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "day"));
        }
    }

    public function setPriceNight($id_resource, $id_units, $price) {
        if ($this->isPriceDay($id_resource, $id_units, "night")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "night"));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "night"));
        }
    }

    public function setPriceWe($id_resource, $id_units, $price) {
        if ($this->isPriceDay($id_resource, $id_units, "we")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "we"));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "we"));
        }
    }

    public function setPricePackage($id_resource, $id_units, $id_package, $price) {
        if ($this->isPricePackage($id_resource, $id_units, $id_package)) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND id_package=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, $id_package));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, id_package) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, $id_package));
        }
    }

    public function isPricePackage($id_resource, $id_units, $id_package) {
        $sql = "SELECT id FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND id_package=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, $id_package));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function isPriceDay($id_resource, $id_units, $day) {
        $sql = "SELECT id FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, $day));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

}
