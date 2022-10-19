<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class StockShelf extends Model
{
    public function __construct()
    {
        $this->tableName = "stock_shelf";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(255)", "");
        $this->setColumnsInfo("id_cabinet", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getAll($idSpace)
    {
        $sql  = " SELECT stock_shelf.*, stock_cabinets.name as cabinet, stock_cabinets.room_number as room ";
        $sql .= " FROM stock_shelf ";
        $sql .= " INNER JOIN stock_cabinets ON stock_shelf.id_cabinet=stock_cabinets.id ";
        $sql .= " WHERE stock_shelf.id_space=? AND stock_shelf.deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getFullName($idSpace, $id)
    {
        if (!isset($id) || is_null($id) || $id == "") {
            return "";
        }

        $sql  = " SELECT stock_shelf.name as shelf, stock_cabinets.name as cabinet, stock_cabinets.room_number as room ";
        $sql .= " FROM stock_shelf ";
        $sql .= " INNER JOIN stock_cabinets ON stock_shelf.id_cabinet=stock_cabinets.id ";
        $sql .= " WHERE stock_shelf.id = ?  AND stock_shelf.id_space=? AND stock_shelf.deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));

        if ($req->rowCount() > 0) {
            $data = $req->fetch();
            return $data["room"] . " - " .$data["cabinet"] . " - " . $data["shelf"];
        }
        return "";
    }

    public function getOne($idSpace, $id)
    {
        $sql = "SELECT * FROM stock_shelf WHERE id=?  AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getAllForProjectSelect($idSpace)
    {
        $data = $this->getAll($idSpace);

        $names = array();
        $ids = array();
        $ids[] = 0;
        $names[] = "";
        for ($i = 0 ; $i < count($data) ; $i++) {
            $ids[] = $data[$i]["id"];
            $names[] = $data[$i]["cabinet"] . ": " . $data[$i]["name"];
        }
        return array( "names" => $names, "ids" => $ids );
    }

    public function set($idSpace, $id, $name, $id_cabinet)
    {
        if ($id > 0) {
            $sql = "UPDATE stock_shelf SET name=?, id_cabinet=? WHERE id=?  AND id_space=? AND deleted=0";
            $this->runRequest($sql, array(
                $name, $id_cabinet, $id, $idSpace
            ));
            return $id;
        } else {
            $sql = "INSERT INTO stock_shelf (name, id_cabinet, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $id_cabinet, $idSpace));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE stock_shelf SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
