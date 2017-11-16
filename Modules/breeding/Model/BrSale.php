<?php

require_once 'Framework/Model.php';

class BrSale extends Model {

    public function __construct() {
        $this->tableName = "br_sales";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("id_enterd_by", "int(11)", 0);
        $this->setColumnsInfo("id_client", "int(11)", 0);
        $this->setColumnsInfo("id_delivery_method", "int(11)", 0);
        $this->setColumnsInfo("id_status", "int(11)", 0); // saisie, validé, envoyé, annulé
        $this->setColumnsInfo("delivery_expected", "date", "0000-00-00");
        $this->setColumnsInfo("delivery_date", "date", "0000-00-00");
        $this->setColumnsInfo("purchase_order_num", "varchar(255)", "");
        $this->setColumnsInfo("id_contact_type", "int(11)", 0);
        $this->setColumnsInfo("further_information", "text", "");
        $this->setColumnsInfo("cancel_reason", "varchar(255)", "");
        $this->setColumnsInfo("cancel_date", "date", "0000-00-00");
        $this->setColumnsInfo("packing_price", "varchar(255)", "");
        $this->setColumnsInfo("delivery_price", "varchar(255)", "");

        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_sales WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getInProgress($id_space) {
        $sql = "SELECT * FROM br_sales WHERE id_space=? AND id_status<=2";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getSent($id_space) {
        $sql = "SELECT * FROM br_sales WHERE id_space=? AND id_status=3 OR id_status=4";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getCanceled($id_space) {
        $sql = "SELECT * FROM br_sales WHERE id_space=? AND id_status=5";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_sales WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function add($id_space, $id_user, $id_client, $id_delivery_method, $delivery_expected, $id_contact_type, $further_information) {

        $sql = "INSERT INTO br_sales (id_space, id_enterd_by, id_client, "
                . "id_delivery_method, delivery_expected, id_contact_type, further_information) "
                . "VALUES (?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($id_space, $id_user, $id_client, $id_delivery_method,
            $delivery_expected, $id_contact_type, $further_information));
        return $this->getDatabase()->lastInsertId();
    }

    public function editInfo($id, $id_status, $purchase_order_num, $id_delivery_method, $delivery_expected, $id_contact_type, $cancel_reason, $cancel_date, $further_information) {

        $sql = "UPDATE br_sales SET id_status=?, purchase_order_num=?, id_delivery_method=?,"
                . " delivery_expected=?, id_contact_type=?, cancel_reason=?, cancel_date=?, further_information=? WHERE id=?";
        $this->runRequest($sql, array($id_status, $purchase_order_num, $id_delivery_method,
            $delivery_expected, $id_contact_type, $cancel_reason, $cancel_date, $further_information, $id));
    }

    public function set($id, $id_space, $id_enterd_by, $id_client, $id_delivery_method, $id_status, $delivery_expected, $purchase_order_num, $id_contact_type, $further_information) {
        if ($id == 0) {
            $sql = 'INSERT INTO br_sales (id_space, id_enterd_by, id_client, id_delivery_method, id_status,
                    delivery_expected, purchase_order_num, id_contact_type, further_information) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $id_enterd_by, $id_client, $id_delivery_method, $id_status,
                $delivery_expected, $purchase_order_num, $id_contact_type, $further_information));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_sales SET id_space=?, id_enterd_by=?, id_client=?, id_delivery_method=?, id_status=?,
                    delivery_expected=?, purchase_order_num=?, id_contact_type=?, further_information=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $id_enterd_by, $id_client, $id_delivery_method, $id_status,
                $delivery_expected, $purchase_order_num, $id_contact_type, $further_information, $id));
            return $id;
        }
    }

    public function setPackingPrice($id, $packing_price) {
        $sql = "UPDATE br_sales SET packing_price=? WHERE id=?";
        $this->runRequest($sql, array($packing_price, $id));
    }

    public function setStatus($id, $id_status) {
        $sql = "UPDATE br_sales SET id_status=? WHERE id=?";
        $this->runRequest($sql, array($id_status, $id));
    }

    public function setDeliveryPrice($id, $delivery_price) {
        $sql = "UPDATE br_sales SET delivery_price=? WHERE id=?";
        $this->runRequest($sql, array($delivery_price, $id));
    }

    public function setDeliveryDate($id, $delivery_date) {
        $sql = "UPDATE br_sales SET delivery_date=? WHERE id=?";
        $this->runRequest($sql, array($delivery_date, $id));
    }

    public function cancel($id, $cancel_reason, $cancel_date) {
        $sql = "UPDATE br_sales SET cancel_reason=? AND cancel_date=? WHERE id=?";
        $this->runRequest($sql, array($cancel_reason, $cancel_date, $id));
    }

    public function delete($id) {
        $sql = "DELETE FROM br_sales WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
