<?php

require_once 'Framework/Model.php';
require_once 'Modules/booking/Model/BkBookingAbstractSups.php';

/**
 * Class defining the area model
 *
 * @author Sylvain Prigent
 */
class BkPackage extends BkBookingAbstractSups
{
    public function __construct()
    {
        $this->tableName = "bk_packages";
    }

    /**
     * Create the area table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
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

        $sql2 = "CREATE TABLE IF NOT EXISTS `bk_j_packages_prices` (
        `id_package` int(11) NOT NULL,
        `id_pricing` int(11) NOT NULL,
        `price` decimal(10,2) NOT NULL
        );";
        $this->runRequest($sql2);

        // delete package with zero id
        $sql3 = "DELETE FROM bk_j_packages_prices WHERE id_package IN(SELECT id FROM bk_packages WHERE id_package=0)";
        $this->runRequest($sql3);

        $sql4 = "DELETE FROM bk_packages WHERE id_package = 0";
        $this->runRequest($sql4);
    }


    /**
     * check if a package is deleted
     */
    public function isDeleted($id_space, $id_pkg)
    {
        $sql = "SELECT * FROM bk_packages WHERE id=? AND id_space=? AND deleted=1";
        $req = $this->runRequest($sql, array($id_pkg, $id_space));
        return $req->rowCount() > 0;
    }

    public function getAll($id_space, $sortentrey)
    {
        $sql = "SELECT * FROM bk_packages WHERE deleted=0 AND id_space=? ORDER BY " . $sortentrey . " ASC;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getName($id_space, $id, $include_deleted = false)
    {
        $sql = "SELECT name FROM bk_packages WHERE id=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        $req = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $req ? $req[0] : null;
    }

    public function getById($id_space, $id_pk)
    {
        $sql = "SELECT * FROM bk_packages WHERE id=? AND id_space=?";
        return $this->runRequest($sql, array($id_pk, $id_space))->fetch();
    }

    /**
     * @deprecated
     */
    public function getPackagePrice($id_space, $id_package, $id_pricing)
    {
        $sql = 'SELECT price FROM bk_j_packages_prices WHERE id_package=? AND id_pricing=? AND deleted=0 AND id_space=?';
        $req = $this->runRequest($sql, array($id_package, $id_pricing, $id_space));
        return $req->fetch();
    }

    /**
     * @deprecated
     */
    public function getPrices($id_space, $resourceID)
    {
        $sql = "SELECT id, id_package, name, duration FROM bk_packages WHERE id_resource=? AND deleted=0 AND id_space=? ORDER BY id_package ASC;";
        $data = $this->runRequest($sql, array(
            $resourceID,
            $id_space
        ));

        if ($data->rowCount() < 1) {
            return array();
        }

        $packages = $data->fetchAll();

        for ($p = 0; $p < count($packages); $p++) {
            $sql = "SELECT * FROM bk_j_packages_prices WHERE id_package=? AND deleted=0 AND id_space=?";
            $data = $this->runRequest($sql, array($packages[$p]["id"], $id_space));
            $prices = $data->fetchAll();
            foreach ($prices as $price) {
                $packages[$p]["price_" . $price["id_pricing"]] = $price["price"];
            }
        }
        return $packages;
    }

    public function getPackageDuration($id_space, $id, $include_deleted = false)
    {
        $sql = "SELECT duration FROM bk_packages WHERE id=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        $req = $this->runRequest($sql, array($id, $id_space));
        $duration = $req->fetch();
        return $duration ? $duration[0] : null;
    }

    public function setSupplementary($id_space, $id_package, $id_resource, $name, $mandatory, $isInvoicingUnit, $duration)
    {
        $id = $this->getPackageID($id_space, $id_package, $id_resource);
        if ($id > 0) {
            $this->updatePackage($id_space, $id, $id_package, $id_resource, $duration, $name);
            return $id;
        } else {
            return $this->addPackage($id_space, $id_package, $id_resource, $duration, $name);
        }
    }

    public function getPackageID($id_space, $id_package, $id_resource)
    {
        $sql = "SELECT id FROM bk_packages WHERE id_package=? AND id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_package, $id_resource, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getBySupID($id_space, $id_package, $id_resource)
    {
        $sql = "SELECT * FROM bk_packages WHERE id_package=? AND id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_package, $id_resource, $id_space));
        if ($req->rowCount() == 1) {
            return $req->fetch();
        } else {
            return null;
        }
    }

    public function addPackage($id_space, $id_package, $id_resource, $duration, $name)
    {
        $sql = "insert into bk_packages(id_package, id_resource, duration, name, id_space)"
                . " values(?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id_package, $id_resource, (float) ($duration), $name, $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    public function updatePackage($id_space, $id, $id_package, $id_resource, $duration, $name)
    {
        $sql = "UPDATE bk_packages SET id_package=?, id_resource=?, duration=?, name=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id_package, $id_resource, $duration, $name, $id, $id_space));
    }

    public function isPackage($id_space, $id)
    {
        $sql = "SELECT * FROM bk_packages WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return ($req->rowCount() == 1);
    }

    /**
     * @deprecated
     */
    public function setPrice($id_space, $id_package, $id_pricing, $price)
    {
        if ($this->isPackagePrice($id_space, $id_package, $id_pricing)) {
            $this->updatePackagePrice($id_space, $id_package, $id_pricing, $price);
        } else {
            $this->addPackagePrice($id_space, $id_package, $id_pricing, $price);
        }
    }

    /**
     * @deprecated
     */
    public function isPackagePrice($id_space, $id_package, $id_pricing)
    {
        $sql = "SELECT * FROM bk_j_packages_prices WHERE id_package=? AND id_pricing=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_package, $id_pricing, $id_space));
        return ($req->rowCount() == 1);
    }

    /**
     * @deprecated
     */
    public function updatePackagePrice($id_space, $id_package, $id_pricing, $price)
    {
        $sql = "UPDATE bk_j_packages_prices SET price=? WHERE id_package=? AND id_pricing=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($price, $id_package, $id_pricing, $id_space));
    }

    /**
     * @deprecated
     */
    public function addPackagePrice($id_space, $id_package, $id_pricing, $price)
    {
        $sql = "INSERT INTO bk_j_packages_prices(id_package, id_pricing, price, id_space)"
                . " values(?, ?, ?, ?)";
        $this->runRequest($sql, array($id_package, $id_pricing, $price, $id_space));
    }

    public function removeUnlisted($id_space, $ids, $idIsPackage=false)
    {
        $id_column = $idIsPackage ? "id_package" : "id";
        $sql = "SELECT id, id_package FROM bk_packages WHERE deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        $databasePackages = $req->fetchAll();

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($ids as $pid) {
                if ($dbPackage[$id_column] == $pid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->deletePackage($id_space, $dbPackage["id"]);
            }
        }
    }

    public function deletePackage($id_space, $id)
    {
        $sql = "UPDATE bk_packages SET deleted=1,deleted_at=NOW() WHERE id = ? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));

        // @deprecated
        $sql2 = "UPDATE bk_j_packages_prices SET deleted=1,deleted_at=NOW() WHERE id_package = ? AND id_space=?";
        $this->runRequest($sql2, array($id, $id_space));
    }
}
