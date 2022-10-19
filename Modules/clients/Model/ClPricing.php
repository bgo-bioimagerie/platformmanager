<?php

require_once 'Framework/Model.php';
require_once 'Framework/Constants.php';

class ClPricing extends Model
{
    public function __construct()
    {
        $this->tableName = "cl_pricings";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("color", "varchar(7)", "");
        $this->setColumnsInfo("txtcolor", "varchar(7)", "");
        $this->setColumnsInfo("type", "int(1)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);

        $this->primaryKey = "id";
    }

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function get($idSpace, $id)
    {
        if (!$id) {
            return [
                "id" => 0,
                "name" => "",
                "color" => Constants::COLOR_WHITE,
                "txtcolor" => Constants::COLOR_BLACK,
                "type" => 0,
                "display_order" => 0
            ];
        }
        $sql = "SELECT * FROM cl_pricings WHERE id=?  AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "SELECT id FROM cl_pricings WHERE name=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($name, $idSpace));
        if ($tmp->rowCount() > 0) {
            $tm = $tmp->fetch();
            return $tm[0];
        }
        return 0;
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM cl_pricings WHERE id=? AND id_space=? AND deleted=0";
        $d = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $d ? $d[0] : null;
    }



    public function set($id, $idSpace, $name, $color, $type, $display_order, $txtcolor="#000000")
    {
        if (!$id) {
            $sql = 'INSERT INTO cl_pricings (id_space, name, color, type, display_order, txtcolor) VALUES (?,?,?,?,?, ?)';
            $this->runRequest($sql, array($idSpace, $name, $color, $type, $display_order, $txtcolor));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_pricings SET name=?, color=?, type=?, display_order=?, txtcolor=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $color, $type, $display_order, $txtcolor, $id, $idSpace));
            return $id;
        }
    }

    public function getForList($idSpace)
    {
        $sql = "SELECT * FROM cl_pricings WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * Get a client's pricing
     *
     * @param int|string $idSpace
     * @param int|string $id_client
     *
     * @return array(string) pricings
     */
    public function getPricingByClient($idSpace, $id_client)
    {
        $sql =
            "SELECT cl_pricings.* FROM cl_pricings
            INNER JOIN cl_clients
            ON cl_clients.pricing = cl_pricings.id
            WHERE cl_clients.id_space = ?
            AND cl_clients.id = ?
            AND cl_pricings.deleted = 0";
        return $this->runRequest($sql, array($idSpace, $id_client))->fetchAll();
    }

    /**
     * Check if pricing in use
     *
     * @param int|string $idSpace
     * @param int|string $id of pricing
     *
     * @return bool
     */
    public function hasClients($idSpace, $id)
    {
        $sql = "SELECT * FROM cl_clients WHERE pricing=? AND id_space=? AND deleted=0";
        $res = $this->runRequest($sql, [$id, $idSpace]);
        if ($res->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE cl_pricings SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
