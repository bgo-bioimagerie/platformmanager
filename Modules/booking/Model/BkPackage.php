<?php

require_once 'Framework/Model.php';

/**
 * Class defining the area model
 *
 * @author Sylvain Prigent
 */
class BkPackage extends Model {

    /**
     * Create the area table
     *
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_packages` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_package` int(11) NOT NULL,
		`id_resource` int(11) NOT NULL,
		`duration` decimal(10,2) NOT NULL,
		`name` varchar(100) NOT NULL,			
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);

        $this->addColumn("bk_packages", "id_package", "int(11)", 0);

        $sql2 = "CREATE TABLE IF NOT EXISTS `sy_j_packages_prices` (
		`id_package` int(11) NOT NULL,
		`id_pricing` int(11) NOT NULL,
		`price` decimal(10,2) NOT NULL
		);";
        $this->runRequest($sql2);

        // delete package with zero id
        $sql3 = "DELETE FROM sy_j_packages_prices WHERE id_package IN(SELECT id FROM bk_packages WHERE id_package=0)";
        $this->runRequest($sql3);

        $sql4 = "DELETE FROM bk_packages WHERE id_package = 0";
        $this->runRequest($sql4);
    }

    public function getByResource($id_resource){
        $sql = "SELECT * FROM bk_packages WHERE id_resource=?";
        $req = $this->runRequest($sql, array($id_resource));
        return $req->fetchAll();
    }
    
    public function getAll($sortentrey) {
        $sql = "SELECT * FROM bk_packages ORDER BY " . $sortentrey . " ASC;";
        $req = $this->runRequest($sql, array($sortentrey));
        return $req->fetchAll();
    }

    public function getPrices($resourceID) {
        $sql = "SELECT id, id_package, name, duration FROM bk_packages WHERE id_resource=? ORDER BY id_package ASC;";
        $data = $this->runRequest($sql, array(
            $resourceID
        ));

        if ($data->rowCount() < 1) {
            return array();
        }

        $packages = $data->fetchAll();
        //print_r($packages);

        for ($p = 0; $p < count($packages); $p++) {

            $sql = "select * from sy_j_packages_prices where id_package=?";
            $data = $this->runRequest($sql, array($packages[$p]["id"]));
            $prices = $data->fetchAll();
            foreach ($prices as $price) {
                $packages[$p]["price_" . $price["id_pricing"]] = $price["price"];
            }
        }

        //print_r($packages);
        return $packages;
    }

    public function getPackageDuration($id) {
        $sql = "select duration from bk_packages where id=?";
        $req = $this->runRequest($sql, array($id));
        $duration = $req->fetch();
        return $duration[0];
    }

    public function setPackage($id_package, $id_resource, $name, $duration) {

        $id = $this->getPackageID($id_package, $id_resource);
        if ($id > 0) {
            $this->updatePackage($id, $id_package, $id_resource, $duration, $name);
            return $id;
        } else {
            return $this->addPackage($id_package, $id_resource, $duration, $name);
        }
    }

    public function getPackageID($id_package, $id_resource) {
        $sql = "select id from bk_packages where id_package=? and id_resource=?";
        $req = $this->runRequest($sql, array($id_package, $id_resource));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function addPackage($id_package, $id_resource, $duration, $name) {

        $sql = "insert into bk_packages(id_package, id_resource, duration, name)"
                . " values(?, ?, ?, ?)";
        $this->runRequest($sql, array($id_package, $id_resource, (float) ($duration), $name));
        return $this->getDatabase()->lastInsertId();
    }

    public function updatePackage($id, $id_package, $id_resource, $duration, $name) {

        $sql = "update bk_packages set id_package=?, id_resource=?, duration=?, name=? where id=?";
        $this->runRequest($sql, array($id_package, $id_resource, $duration, $name, $id));
    }

    public function isPackage($id) {
        $sql = "select * from bk_packages where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function setPrice($id_package, $id_pricing, $price) {
        if ($this->isPackagePrice($id_package, $id_pricing)) {
            $this->updatePackagePrice($id_package, $id_pricing, $price);
        } else {
            $this->addPackagePrice($id_package, $id_pricing, $price);
        }
    }

    public function isPackagePrice($id_package, $id_pricing) {
        $sql = "select * from sy_j_packages_prices where id_package=? AND id_pricing=?";
        $req = $this->runRequest($sql, array($id_package, $id_pricing));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePackagePrice($id_package, $id_pricing, $price) {
        $sql = "update sy_j_packages_prices set price=? where id_package=? AND id_pricing=?";
        $this->runRequest($sql, array($price, $id_package, $id_pricing));
    }

    public function addPackagePrice($id_package, $id_pricing, $price) {
        $sql = "insert into sy_j_packages_prices(id_package, id_pricing, price)"
                . " values(?, ?, ?)";
        $this->runRequest($sql, array($id_package, $id_pricing, $price));
    }

    public function removeUnlistedPackages($packageID) {

        $sql = "select id, id_package from bk_packages";
        $req = $this->runRequest($sql);
        $databasePackages = $req->fetchAll();

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage["id_package"] == $pid) {
                    //echo "found package " . $pid . "in the database <br/>";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                //echo "delete pacjkage id = " . $dbPackage["id"] . " package-id = " . $dbPackage["id_package"] . "<br/>"; 
                $this->deletePackage($dbPackage["id"]);
            }
        }
    }

    public function deletePackage($id) {
        $sql = "DELETE FROM bk_packages WHERE id = ?";
        $this->runRequest($sql, array($id));

        $sql2 = "DELETE FROM sy_j_packages_prices WHERE id_package = ?";
        $this->runRequest($sql2, array($id));
    }

}
