<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model
 * @author Sylvain Prigent
 */
class ResourceInfo extends Model
{
    /**
     * Create the unit table
     *
     * @return PDOStatement
     */
    public function __construct()
    {
        $this->tableName = "re_info";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("name", "varchar(150)", "");
        $this->setColumnsInfo("brand", "varchar(250)", "");
        $this->setColumnsInfo("type", "varchar(250)", "");
        $this->setColumnsInfo("description", "varchar(500)", "");
        $this->setColumnsInfo("long_description", "text", "");
        $this->setColumnsInfo("id_category", "int(11)", 0);
        $this->setColumnsInfo("id_area", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("display_order", "int(11)", 0);
        $this->setColumnsInfo("image", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    public function getDefault()
    {
        return array(
            "id" => 0,
            "name" => "",
            "brand" => "",
            "type" => "",
            "description" => "",
            "long_description" => "",
            "id_category" => 0,
            "id_area" => 0,
            "id_space" => 0,
            "display_order" => 0,
            "image" => ""
        );
    }

    public function setImage($idSpace, $id, $image)
    {
        $sql = "UPDATE re_info SET image=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($image, $id, $idSpace));
    }

    public function getAll($sort = "name")
    {
        $sql = "SELECT * FROM re_info WHERE deleted=0 ORDER BY " . $sort . " ASC";
        return $this->runRequest($sql)->fetchAll();
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM re_info WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getForList($idSpace)
    {
        $data = $this->getForSpace($idSpace);
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] =  $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function getAllForSelect($idSpace, $sort = "name")
    {
        $sql = "SELECT * FROM re_info WHERE id_space=? AND deleted=0 ORDER BY " . $sort . " ASC";
        $resources = $this->runRequest($sql, array($idSpace))->fetchAll();
        $names = array();
        $ids = array();
        foreach ($resources as $res) {
            $names[] = $res["name"];
            $ids[] = $res["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM re_info WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() > 0) {
            return $req->fetch();
        }
        return null;
    }

    public function getBySpace($idSpace)
    {
        $sql = "SELECT re_info.*, re_category.name as category "
                . "FROM re_info "
                . "INNER JOIN re_category ON re_info.id_category = re_category.id "
                . "WHERE re_info.id_space=? AND re_info.deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getBySpaceWithoutCategory($idSpace)
    {
        $sql = "SELECT * FROM re_info WHERE re_info.id_space=? AND re_info.deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    /**
    * @deprecated
    */
    public function getIdByName($idSpace, $name)
    {
        $sql = "SELECT id FROM re_info WHERE name=? AND deleted=0 AND id_space=?";
        $tmp = $this->runRequest($sql, array($name, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    /**
    * @deprecated
    */
    public function getIdByNameSpace($name, $idSpace)
    {
        $sql = "SELECT id FROM re_info WHERE name=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($name, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getAreaID($idSpace, $id)
    {
        $sql = "SELECT id_area FROM re_info WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM re_info WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function set($id, $name, $brand, $type, $description, $long_description, $id_category, $id_area, $idSpace, $display_order)
    {
        if (!$this->exists($idSpace, $id)) {
            $sql = "INSERT INTO re_info (name, brand, type, description, long_description, id_category, id_area, id_space, display_order) "
                    . "VALUES (?,?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($name, $brand, $type, $description, $long_description, $id_category, $id_area, $idSpace, $display_order));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE re_info SET name=?, brand=?, type=?, description=?, long_description=?, id_category=?, id_area=?, display_order=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $brand, $type, $description, $long_description, $id_category, $id_area, $display_order, $id, $idSpace));
        }
        Events::send([
            "action" => Events::ACTION_RESOURCE_EDIT,
            "space" => ["id" => intval($idSpace)],
            "resource" => ["id" => $id]
        ]);
        return $id;
    }

    public function exists($idSpace, $id)
    {
        $sql = "SELECT id FROM re_info WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get the first resource ID for a given area
     * @param unknown $areaId
     * @return mixed
     */
    public function firstResourceIDForArea($idSpace, $areaId)
    {
        $sql = "select id from re_info where id_area=? AND id_space=? AND deleted=0 ORDER BY display_order ASC;";
        $req = $this->runRequest($sql, array($areaId, $idSpace));
        $tmp = $req->fetch();
        return $tmp ? $tmp[0] : null;
    }

    /**
     * Get the resources IDs and names for a given Area
     * @param unknown $areaId
     * @return array
     */
    public function resourceIDNameForArea($idSpace, $areaId): array
    {
        $sql = "SELECT id, name from re_info where id_area=? AND id_space=? AND deleted=0 ORDER BY display_order";
        $data = $this->runRequest($sql, array($areaId, $idSpace));
        return $data->fetchAll();
    }

    /**
     * Get the resources info for a given area
     * @param unknown $areaId
     * @return array
     */
    public function resourcesForArea($idSpace, $areaId): array
    {
        $sql = "SELECT * from re_info where id_area=? AND id_space=? AND deleted=0 ORDER BY display_order";
        $data = $this->runRequest($sql, array($areaId, $idSpace));
        return $data->fetchAll();
    }

    /**
     * Get the resources info for a given category
     * @param unknown $areaId
     * @return multitype:
     */
    public function resourcesForCategory($idSpace, $categoryId)
    {
        $sql = "SELECT * from re_info where id_category=? AND id_space=? AND deleted=0 ORDER BY display_order";
        $data = $this->runRequest($sql, array($categoryId, $idSpace));
        return $data->fetchAll();
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE re_info SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
        Events::send([
            "action" => Events::ACTION_RESOURCE_DELETE,
            "space" => ["id" => intval($idSpace)],
            "quote" => ["id" => $id]
        ]);
    }
}
