<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class BkOwnerPrice extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_owner_prices";
    }

    public function createTable()
    {
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

    public function removeNotListed($id_space, $id_resource, $units)
    {
        $sql = "SELECT * FROM bk_owner_prices WHERE id_space=? AND deleted=0";
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
                } elseif ($residArray[1] == "night") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["day_night_we"] == "night") {
                        $found = true;
                        break;
                    }
                } elseif ($residArray[1] == "we") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["day_night_we"] == "we") {
                        $found = true;
                        break;
                    }
                } elseif ($residArray[1] == "pk") {
                    if ($d["id_resource"] == $residArray[0] && $d["id_unit"] == $units[$i] && $d["id_package"] == $residArray[2]) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $sql = "UPDATE bk_owner_prices SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
                $this->runRequest($sql, array($d["id"], $id_space));
            }
        }
    }

    public function getAll($id_space)
    {
        $sql = "SELECT * FROM bk_owner_prices WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getPrice($id_space, $id_resource, $id_units)
    {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getDayPrice($id_space, $id_resource, $id_client)
    {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_client, "day", $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getNightPrice($id_space, $id_resource, $id_client)
    {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_client, "night", $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getWePrice($id_space, $id_resource, $id_client)
    {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_client, "we", $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function getPackagePrice($id_space, $package_id, $resources_id, $id_client)
    {
        $sql = "SELECT price FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND id_package=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($resources_id, $id_client, $package_id, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return -1;
    }

    public function setPriceDay($id_space, $id_resource, $id_units, $price)
    {
        if ($this->isPriceDay($id_space, $id_resource, $id_units, "day")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "day", $id_space));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "day", $id_space));
        }
    }

    public function setPriceNight($id_space, $id_resource, $id_units, $price)
    {
        if ($this->isPriceDay($id_space, $id_resource, $id_units, "night")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "night", $id_space));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "night", $id_space));
        }
    }

    public function setPriceWe($id_space, $id_resource, $id_units, $price)
    {
        if ($this->isPriceDay($id_space, $id_resource, $id_units, "we")) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, "we", $id_space));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, day_night_we, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, "we", $id_space));
        }
    }

    public function setPricePackage($id_space, $id_resource, $id_units, $id_package, $price)
    {
        if ($this->isPricePackage($id_space, $id_resource, $id_units, $id_package)) {
            $sql = "UPDATE bk_owner_prices SET price=? WHERE id_resource=? AND id_unit=? AND id_package=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($price, $id_resource, $id_units, $id_package, $id_space));
        } else {
            $sql = "INSERT INTO bk_owner_prices (id_resource, id_unit, price, id_package, id_space) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_resource, $id_units, $price, $id_package, $id_space));
        }
    }

    public function isPricePackage($id_space, $id_resource, $id_units, $id_package)
    {
        $sql = "SELECT id FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND id_package=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, $id_package, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function isPriceDay($id_space, $id_resource, $id_units, $day)
    {
        $sql = "SELECT id FROM bk_owner_prices WHERE id_resource=? AND id_unit=? AND day_night_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_resource, $id_units, $day, $id_space));
        return ($req->rowCount() == 1);
    }
}
