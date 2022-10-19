<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class SeService extends Model
{
    public function __construct()
    {
        $this->tableName = "se_services";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `se_services` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_space` int(11) NOT NULL DEFAULT 0,
            `name` varchar(100) NOT NULL,
            `description` varchar(250) NOT NULL,
            `display_order` int(11) NOT NULL DEFAULT 0,		
            `is_active` int(1) NOT NULL DEFAULT 1,	 
            `type_id` int(11) NOT NULL DEFAULT 1,
            `quantity` varchar(128) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function getName($idSpace, $id, $deleted=false)
    {
        $sql = "SELECT name FROM se_services WHERE id=? AND id_space=?";
        if (!$deleted) {
            $sql .= "AND deleted=0";
        }
        $tmp = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "SELECT id FROM se_services WHERE name=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($name, $idSpace));
        if ($data->rowCount() > 0) {
            $tmp = $data->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function getItemType($idSpace, $id)
    {
        $sql = "SELECT type_id FROM se_services WHERE id=? AND id_space=?";
        $data = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $data ? $data[0] : null;
    }

    public function setQuantity($idSpace, $id, $quantity)
    {
        $sql = "UPDATE se_services SET quantity=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($quantity, $id, $idSpace));
    }

    // @bug possible collision between select and update
    public function editquantity($idSpace, $id, $quantity, $operation = "add")
    {
        $sql = "SELECT quantity FROM se_services WHERE id=? AND id_space=? AND deleted=0";
        $q = $this->runRequest($sql, array($id, $idSpace))->fetch();

        if ($operation == "add") {
            $this->setQuantity($idSpace, $id, $quantity + $q[0]);
        } else {
            $this->setQuantity($idSpace, $id, $q[0] - $quantity);
        }
    }

    /**
     * add an item to the table
     *
     * @param string $name name of the unit
     */
    public function addItem($idSpace, $name, $description, $display_order, $type_id = 1)
    {
        if ($display_order == '') {
            $display_order = 0;
        }
        $sql = "insert into se_services(name, description, display_order, type_id, id_space)"
                . " values(?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($name, $description, $display_order, $type_id, $idSpace));
        return $this->getDatabase()->lastInsertId();
    }

    public function setService($id, $idSpace, $name, $description, $display_order, $type_id)
    {
        if ($display_order == '') {
            $display_order = 0;
        }
        if ($this->isService($idSpace, $id)) {
            $sql = "UPDATE se_services SET name=?, description=?, display_order=?, type_id=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $description, $display_order, $type_id, $id, $idSpace));
        } else {
            $sql = "INSERT INTO se_services (name, id_space, description, display_order, type_id) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($name, $idSpace, $description, $display_order, $type_id));
            $id = $this->getDatabase()->lastInsertId();
        }
        Events::send([
            "action" => Events::ACTION_SERVICE_EDIT,
            "space" => ["id" => intval($idSpace)],
            "service" => ["id" => $id]
        ]);
        return $id;
    }

    public function isService($idSpace, $id)
    {
        $sql = "SELECT id FROM se_services WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function setActive($idSpace, $id, $active)
    {
        $sql = "update se_services set is_active=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($active, $id, $idSpace));
    }

    /**
     * get items informations
     *
     * @param string $sortentry Entry that is used to sort the units
     * @return multitype: array
     */
    public function getItems($idSpace, $sortentry = 'id')
    {
        $sql = "SELECT * from se_services WHERE AND id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    public function getBySpace($idSpace)
    {
        $sql = "SELECT * FROM se_services WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        return $req->fetchAll();
    }

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM se_services WHERE se_services.id_space=? AND se_services.deleted=0;";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getForList($idSpace)
    {
        $sql = "select * from se_services WHERE id_space=? AND deleted=0 ORDER BY name ASC;";
        $req = $this->runRequest($sql, array($idSpace))->fetchAll();
        $ids = array();
        $names = array();
        foreach ($req as $r) {
            $ids[] = $r["id"];
            $names[] = $r["name"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get items informations
     *
     * @param string $sortentry Entry that is used to sort the units
     * @return multitype: array
     */
    public function getActiveItems($idSpace, $sortentry = 'id')
    {
        $sql = "select * from se_services where is_active=1 AND id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    /**
     * get the informations of an item
     *
     * @param int $id Id of the item to query
     * @throws Exception id the item is not found
     * @return mixed array
     */
    public function getItem($idSpace, $id)
    {
        $sql = "select * from se_services where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();  // get the first line of the result
        } else {
            throw new PfmParamException("Cannot find the item using the given id = " . $id, 404);
        }
    }

    public function getItemName($idSpace, $id, $deleted=false)
    {
        $sql = "select name from se_services where id=? AND id_space=?";
        if (!$deleted) {
            $sql .= "AND deleted=0";
        }
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp['name'];  // get the first line of the result
        } else {
            return null;
        }
    }

    /**
     * update the information of an item
     *
     * @param int $id Id of the item to update
     * @param string $name New name of the item
     */
    public function editItem($idSpace, $id, $name, $description, $display_order, $type_id)
    {
        $sql = "update se_services set name=?, description=?, display_order=?, type_id=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array("" . $name . "", $description, $display_order, $type_id, $id, $idSpace));
        Events::send([
            "action" => Events::ACTION_SERVICE_EDIT,
            "space" => ["id" => intval($idSpace)],
            "service" => ["id" => $id]
        ]);
    }

    /**
     * Remove an item from the database
     * @param number $id item ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE se_services SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
        Events::send([
            "action" => Events::ACTION_SERVICE_DELETE,
            "space" => ["id" => intval($idSpace)],
            "service" => ["id" => $id]
        ]);
    }
}
