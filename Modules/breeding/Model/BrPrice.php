<?php

require_once 'Framework/Model.php';

class BrPrice extends Model {

    public function __construct() {
        $this->tableName = "br_prices";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_product_stage", "int(11)", 0);
        $this->setColumnsInfo("id_pricing", "int(11)", 0);
        $this->setColumnsInfo("price", "varchar(255)", 0);

        $this->primaryKey = "id";
    }

    public function getProductFullName($id_product_stage) {

        $sql = "SELECT * FROM br_product_stages WHERE id=?";
        $stage = $this->runRequest($sql, array($id_product_stage))->fetch();

        $sql2 = "SELECT name FROM br_products WHERE id=?";
        $pname = $this->runRequest($sql2, array($stage["id_product"]))->fetch();

        return $pname[0] . " - " . $stage["name"];
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_product_stages WHERE id_product IN (SELECT id FROM br_products WHERE id_space=?) ORDER BY display_order DESC";
        $productSages = $this->runRequest($sql, array($id_space))->fetchAll();

        $sql2 = "SELECT * FROM br_pricings WHERE id_space=?";
        $pricings = $this->runRequest($sql2, array($id_space))->fetchAll();

        $data = array();
        foreach ($productSages as $stage) {
            $d = array();

            // project stage id
            $d["id_product_stage"] = $stage["id"];

            // get product name
            $sql = "SELECT name FROM br_products WHERE id=?";
            $pname = $this->runRequest($sql, array($stage["id_product"]))->fetch();

            $d["name"] = $pname[0] . " - " . $stage["name"];

            // get unit quantity
            $sql2 = "SELECT unit_quantity FROM br_product_unit_q WHERE id_product_stage=?";
            $q = $this->runRequest($sql2, array($stage["id"]))->fetch();
            $d["unit_quantity"] = $q[0];

            // get prices
            foreach ($pricings as $p) {
                $sql3 = "SELECT price FROM br_prices WHERE id_product_stage=? AND id_pricing=?";
                $price = $this->runRequest($sql3, array($stage["id"], $p["id"]))->fetch();
                $d["pricing_" . $p["id"]] = $price[0];
            }
            $data[] = $d;
        }
        return $data;
    }

    public function get($id) {
        $sql = "SELECT * FROM br_prices WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id_space, $id_product_stage, $id_pricing, $price) {
        $id = $this->exists($id_product_stage, $id_pricing);
        if ($id == 0) {
            $sql = "INSERT INTO br_prices (id_space, id_product_stage, id_pricing, price) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_space, $id_product_stage, $id_pricing, $price));
        } else {
            $sql = "UPDATE br_prices SET id_space=?, id_product_stage=?, id_pricing=?, price=? WHERE id=?";
            $this->runRequest($sql, array($id_space, $id_product_stage, $id_pricing, $price, $id));
        }
    }

    public function exists($id_product_stage, $id_pricing) {
        $sql = "SELECT id FROM br_prices WHERE id_product_stage=? AND id_pricing=?";
        $req = $this->runRequest($sql, array($id_product_stage, $id_pricing));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getPrice($id_product_stage, $id_pricing) {
        $sql = "SELECT price FROM br_prices WHERE id_product_stage=? AND id_pricing=?";
        $req = $this->runRequest($sql, array($id_product_stage, $id_pricing));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM br_prices WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
