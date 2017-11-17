<?php

require_once 'Framework/Model.php';

class BrProductStage extends Model {

    public function __construct() {
        $this->tableName = "br_product_stages";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_product", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("display_order", "int(5)", "");
        $this->setColumnsInfo("start_num", 'varchar(255)', '');
        $this->setColumnsInfo("start_unit", 'varchar(255)', ''); // days, weeks, month, years
        $this->setColumnsInfo("end_num", 'varchar(255)', '');
        $this->setColumnsInfo("end_unit", 'varchar(255)', ''); // days, weeks, month, years
        $this->primaryKey = "id";
    }

    public function getUnitsForList($lang) {
        $names = array(
            BreedingTranslator::Days($lang),
            BreedingTranslator::Weeks($lang),
            BreedingTranslator::Month($lang),
            BreedingTranslator::Years($lang),
        );

        $ids = array(
            1, 2, 3, 4
        );

        return array("names" => $names, "ids" => $ids);
    }

    public function getUnitName($id, $lang) {
        if ($id == 1) {
            return BreedingTranslator::Days($lang);
        }
        if ($id == 2) {
            return BreedingTranslator::Weeks($lang);
        }
        if ($id == 3) {
            return BreedingTranslator::Month($lang);
        }
        if ($id == 4) {
            return BreedingTranslator::Years($lang);
        }
    }

    public function getAll($id_product) {
        $sql = "SELECT * FROM br_product_stages WHERE id_product=? ORDER BY display_order";
        return $this->runRequest($sql, array($id_product))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_product_stages WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT name FROM br_product_stages WHERE id=?";
        $d = $this->runRequest($sql, array($id))->fetch();
        return $d[0];
    }

    public function set($id, $id_product, $name, $display_order, $start_num, $start_unit, $end_num, $end_unit) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_product_stages (id_product, name, display_order, start_num, start_unit, end_num, end_unit) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_product, $name, $display_order, $start_num, $start_unit, $end_num, $end_unit));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_product_stages SET id_product=?, name=?, display_order=?, start_num=?, start_unit=?, end_num=?, end_unit=? WHERE id=?';
            $this->runRequest($sql, array($id_product, $name, $display_order, $start_num, $start_unit, $end_num, $end_unit, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM br_product_stages WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
