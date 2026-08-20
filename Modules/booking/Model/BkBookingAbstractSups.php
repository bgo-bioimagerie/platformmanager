<?php
require_once 'Framework/Model.php';


abstract class BkBookingAbstractSups extends Model
{
    public function getForSpace($id_space, $sort)
    {
        if (!$this->tableName) {
            throw new PfmException("invalid access, table name not defined for ".get_class($this), 500);
        }
        $sql = "SELECT * FROM ".$this->tableName." WHERE deleted=0 AND id_space=? ORDER BY " . $sort . " ASC;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    abstract public function setSupplementary(
        $id_space,
        $id_quantity,
        $id_resource,
        $name,
        $mandatory,
        $is_invoicing_unit,
        $duration
    );

    abstract public function setOptions($id_space, $id_supinfo, $id_resource, $choices);

    abstract public function getBySupID($id_space, $id_quantity, $id_resource);

    abstract public function removeUnlisted($id_space, $ids, $idIsSup=false);

    public function getByResource($id_space, $id_resource, $include_deleted=false, $sort=false)
    {
        if (!$this->tableName) {
            throw new PfmException("invalid access, table name not defined for ".get_class($this), 500);
        }
        $sql = "SELECT * from ".$this->tableName." WHERE id_resource=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        if ($sort) {
            $sql .= " ORDER BY deleted ASC, id DESC";
        }
        return $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();
    }
}
