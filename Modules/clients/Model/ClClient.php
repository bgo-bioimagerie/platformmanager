<?php

require_once 'Framework/Model.php';
require_once 'Modules/clients/Model/ClPricing.php';

class ClClient extends Model
{
    public function __construct()
    {
        $this->tableName = "cl_clients";
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
    }

    public function getInstitution($idSpace, $id)
    {
        $sql = "SELECT * FROM cl_addresses WHERE id=(SELECT address_invoice FROM cl_clients WHERE id=? AND id_space=? AND deleted=0)";
        $address = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $address ? $address["institution"] : "";
    }

    public function getAddressInvoice($idSpace, $id)
    {
        $sql = "SELECT * FROM cl_addresses WHERE id=(SELECT address_invoice FROM cl_clients WHERE id=? AND id_space=? AND deleted=0)";
        $address = $this->runRequest($sql, array($id, $idSpace))->fetch();
        if ($address) {
            $formattedAddress = "";
            $addressAttrToPrint = ['institution', 'building_floor', 'service', 'address', 'zip_code', 'city', 'country'];
            foreach ($addressAttrToPrint as $addressAttr) {
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

    public function setAddressDelivery($idSpace, $id, $id_addressdelivery)
    {
        $sql = "UPDATE cl_clients SET address_delivery=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_addressdelivery, $id, $idSpace));
    }

    public function setAddressInvoice($idSpace, $id, $id_addressinvoice)
    {
        $sql = "UPDATE cl_clients SET address_invoice=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_addressinvoice, $id, $idSpace));
    }

    public function getPricingID($idSpace, $id)
    {
        $sql = "SELECT pricing FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getAll($idSpace, $sort='name')
    {
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0 ORDER BY ".$sort." ASC";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();

        $modelPricing = new ClPricing();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["pricing_name"] = $modelPricing->getName($idSpace, $data[$i]["pricing"]);
        }
        return $data;
    }

    public function count($idSpace)
    {
        $sql = "SELECT count(*) FROM cl_clients WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace))->fetch();
        return $data ? $data[0] : 0;
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $data ? $data[0] : "";
    }


    public function getContactName($idSpace, $id)
    {
        $sql = "SELECT contact_name FROM cl_clients WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $data ? $data[0] : "";
    }

    public function getIdFromName($idSpace, $name)
    {
        $sql = "SELECT id FROM cl_clients WHERE name=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($name, $idSpace))->fetch();
        return $data ? $data[0] : null;
    }

    public function getForList($idSpace)
    {
        $sql = "SELECT * FROM cl_clients WHERE id_space=? AND deleted=0 ORDER BY name ASC;";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function get($idSpace, $id)
    {
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
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function set($id, $idSpace, $name, $contact_name, $phone, $email, $pricing, $invoice_send_preference)
    {
        if (!$id) {
            $sql = 'INSERT INTO cl_clients (id_space, name, contact_name, phone, email, pricing, invoice_send_preference) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($idSpace, $name, $contact_name, $phone, $email, $pricing, $invoice_send_preference));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_clients SET name=?, contact_name=?, phone=?, email=?, pricing=?, invoice_send_preference=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $contact_name, $phone, $email, $pricing, $invoice_send_preference, $id, $idSpace));
        }
        Events::send([
            "action" => Events::ACTION_CUSTOMER_EDIT,
            "space" => ["id" => intval($idSpace)],
            "client" => ["id" => $id]
        ]);
        return $id;
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE cl_clients SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=? AND deleted=0";
        //$sql = "DELETE FROM cl_clients WHERE id=?  AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id, $idSpace));
        Events::send([
            "action" => Events::ACTION_CUSTOMER_DELETE,
            "space" => ["id" => intval($idSpace)],
            "client" => ["id" => $id]
        ]);
        return $id;
    }
}
