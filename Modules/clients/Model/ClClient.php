<?php

require_once 'Framework/Model.php';
require_once 'Modules/clients/Model/ClPricing.php';

class ClClient extends Model {

    public function __construct() {
        $this->tableName = "cl_clients";

        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("contact_name", "varchar(255)", "");
        $this->setColumnsInfo("address_delivery", "int(11)", 0);
        $this->setColumnsInfo("address_invoice", "int(11)", 0);
        $this->setColumnsInfo("phone", "varchar(20)", "");
        $this->setColumnsInfo("email", "varchar(255)", "");
        $this->setColumnsInfo("pricing", "int(11)", "");
        $this->setColumnsInfo("invoice_send_preference", "int(11)", 0); // 1 email; 2 postal
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `cl_clients` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL DEFAULT 0,
            `name` varchar(255) DEFAULT NULL,
            `contact_name` varchar(255) DEFAULT NULL,
            `address_delivery` int NOT NULL DEFAULT 0,
            `address_invoice` int NOT NULL DEFAULT 0,
            `phone` varchar(20) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `pricing` int DEFAULT NULL,
            `invoice_send_preference` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        )';
        $this->runRequest($sql);
    }
    
    public function getInstitution($id_space, $id){
        $sql = "SELECT * FROM cl_addresses WHERE id=(SELECT address_invoice FROM cl_clients WHERE id=? AND id_space=? AND deleted=0)";
        $address = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $address ? $address["institution"] : "";
    }
    
    public function getAddressInvoice($id_space ,$id){
        $sql = "SELECT * FROM cl_addresses WHERE id=(SELECT address_invoice FROM cl_clients WHERE id=? AND id_space=? AND deleted=0)";
        $address = $this->runRequest($sql, array($id, $id_space))->fetch();
        if ($address) {
            $formattedAddress = "";
            $addressAttrToPrint = ['institution', 'building_floor', 'service', 'address', 'zip_code', 'city', 'country'];
            foreach($addressAttrToPrint as $addressAttr) {
                if ($addressAttr && $addressAttr != "") {
                    $formattedAddress .= $address[$addressAttr];
                    $formattedAddress .= ($addressAttr != 'zip_code') ? "\n" : " "; 
                }
            }
            $result = $formattedAddress;
        } else {
            $result = $address;
        }
        
        return $result;
    }
    
    public function setAddressDelivery($id_space, $id, $id_addressdelivery) {
        $sql = "UPDATE cl_clients SET address_delivery=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_addressdelivery, $id, $id_space));
    }
    
    public function setAddressInvoice($id_space, $id, $id_addressinvoice) {
        $sql = "UPDATE cl_clients SET address_invoice=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_addressinvoice, $id, $id_space));
    }

    public function getPricingID($id_space, $id) {
        $sql = "SELECT pricing FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        $modelPricing = new ClPricing();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["pricing_name"] = $modelPricing->getName($id_space, $data[$i]["pricing"]);
        }
        return $data;
    }

    public function count($id_space) {
        $sql = "SELECT count(*) FROM cl_clients WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetch();
        return $data ? $data[0]: 0;
    }

    public function getName($id_space, $id) {
        $sql = "SELECT name FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $data ? $data[0] : "";
    }
    
    
    public function getContactName($id_space, $id) {
        $sql = "SELECT contact_name FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $data ? $data[0] : "";
    }

    public function getIdFromName($id_space, $name) {
        $sql = "SELECT id FROM cl_clients WHERE name=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($name, $id_space))->fetch();
        return $data ? $data[0] : null;
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0 ORDER BY name ASC;";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function get($id_space, $id) {
        if (!$id) {
            return array(
                "id" => 0,
                "name" => "",
                "contact_name" => "",
                "address_delivery" => 0,
                "address_invoice" => 0,
                "phone" => "",
                "email" => "",
                "pricing" => 0,
                "invoice_send_preference" => 1
            );
        }

        $sql = "SELECT * FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function set($id, $id_space, $name, $contact_name, $phone, $email, $pricing, $invoice_send_preference) {
        if (!$id) {
            $sql = 'INSERT INTO cl_clients (id_space, name, contact_name, phone, email, pricing, invoice_send_preference) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $name, $contact_name, $phone, $email, $pricing, $invoice_send_preference));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_clients SET name=?, contact_name=?, phone=?, email=?, pricing=?, invoice_send_preference=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $contact_name, $phone, $email, $pricing, $invoice_send_preference, $id, $id_space));
        }
        Events::send([
            "action" => Events::ACTION_CUSTOMER_EDIT,
            "space" => ["id" => intval($id_space)],
            "client" => ["id" => $id]
        ]);
        return $id;
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE cl_clients SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=? AND deleted=0";
        //$sql = "DELETE FROM cl_clients WHERE id=?  AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id, $id_space));
        Events::send([
            "action" => Events::ACTION_CUSTOMER_DELETE,
            "space" => ["id" => intval($id_space)],
            "client" => ["id" => $id]
        ]);
        return $id;
    }

}
