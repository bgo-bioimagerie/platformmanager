<?php

require_once 'Framework/Model.php';

class EsPrice extends Model {

    public function __construct() {
        $this->tableName = "es_prices";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_product", "int(11)", 0);
        $this->setColumnsInfo("id_pricing", "int(11)", 0);
        $this->setColumnsInfo("price", "varchar(255)", 0);

        $this->primaryKey = "id";
    }

    public function getProductFullName($id_product) {

        $sql2 = "SELECT name FROM es_products WHERE id=?";
        $pname = $this->runRequest($sql2, array($id_product))->fetch();

        return $pname[0];
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM es_products WHERE id_space=?";
        $products = $this->runRequest($sql, array($id_space))->fetchAll();

        $sql2 = "SELECT * FROM cl_pricings WHERE id_space=?";
        $pricings = $this->runRequest($sql2, array($id_space))->fetchAll();

        $data = array();
        foreach ($products as $product) {
            $d = array();

            $d["id"] = $product["id"];
            $d["name"] = $product["name"];
            
            // get unit quantity
            $sql2 = "SELECT unit_quantity FROM es_product_unit_q WHERE id_product=?";
            $q = $this->runRequest($sql2, array($product["id"]))->fetch();
            $d["unit_quantity"] = $q[0];

            // get prices
            foreach ($pricings as $p) {
                $sql3 = "SELECT price FROM es_prices WHERE id_product=? AND id_pricing=?";
                $price = $this->runRequest($sql3, array($product["id"], $p["id"]))->fetch();
                $d["pricing_" . $p["id"]] = $price[0];
            }
            $data[] = $d;
        }
        return $data;
    }

    public function get($id) {
        $sql = "SELECT * FROM es_prices WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id_space, $id_product, $id_pricing, $price) {
        $id = $this->exists($id_product, $id_pricing);
        if (!$id) {
            $sql = "INSERT INTO es_prices (id_space, id_product, id_pricing, price) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_space, $id_product, $id_pricing, $price));
        } else {
            $sql = "UPDATE es_prices SET id_space=?, id_product=?, id_pricing=?, price=? WHERE id=?";
            $this->runRequest($sql, array($id_space, $id_product, $id_pricing, $price, $id));
        }
    }

    public function exists($id_product, $id_pricing) {
        $sql = "SELECT id FROM es_prices WHERE id_product=? AND id_pricing=?";
        $req = $this->runRequest($sql, array($id_product, $id_pricing));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getPrice($id_product, $id_pricing) {
        $sql = "SELECT price FROM es_prices WHERE id_product=? AND id_pricing=?";
        $req = $this->runRequest($sql, array($id_product, $id_pricing));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM es_prices WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
