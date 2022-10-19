<?php

require_once 'Framework/Model.php';

/**
 * Model for calendar suplementary informations
 *
 * @author Sylvain Prigent
 */
class BkCalQuantities extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_calquantities";
    }

    /**
     * Create the calsupplementaries table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_calquantities` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
        `id_quantity` int(11) NOT NULL,
        `id_resource` int(11) NOT NULL,
        `name` varchar(30) NOT NULL DEFAULT '',
		`mandatory` int(1) NOT NULL,
        `is_invoicing_unit` int(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";

        return $this->runRequest($sql);
    }

    /**
     * get the supplementaries infos
     * @param unknown $sortEntry
     * @return multitype:
     */
    public function calQuantities($idSpace, $sortEntry)
    {
        $sql = "select * from bk_calquantities WHERE deleted=0 AND id_space=? order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    public function getByResource($idSpace, $id_resource, $include_deleted=false, $sort=false)
    {
        $sql = "SELECT * from bk_calquantities WHERE id_resource=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        if ($sort) {
            $sql .= " ORDER BY deleted ASC, id DESC";
        }
        return $this->runRequest($sql, array($id_resource, $idSpace))->fetchAll();
    }

    public function getById($idSpace, $id_qte)
    {
        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND id_space=?";
        return $this->runRequest($sql, array($id_qte, $idSpace))->fetch();
    }

    /**
     * check if a quantity is deleted
     */
    public function isDeleted($idSpace, $id_qte)
    {
        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND id_space=? AND deleted=1";
        $req = $this->runRequest($sql, array($id_qte, $idSpace));
        return $req->rowCount() > 0;
    }

    public function getAll($idSpace)
    {
        $sql = "select * from bk_calquantities WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getForSpace($idSpace, $sort)
    {
        //$sql = "select * from bk_calquantities WHERE id_resource IN (SELECT id FROM re_info WHERE id_space=?) ORDER BY " . $sort . " ASC;";
        $sql = "SELECT * FROM bk_calquantities WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalQuantities($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND deleted=0 AND id_space=?";
        $data = $this->runRequest($sql, array($id, $idSpace));
        if ($data->rowCount() == 1) {
            return $data->fetch();
        } else {
            return "not found";
        }
    }

    /**
     * get a supplementary name from it ID
     * @param unknown $id
     * @return string
     */
    public function getcalQuantityName($idSpace, $id)
    {
        $sql = "select name from bk_calquantities where id=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($id, $idSpace));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return "not found";
        }
    }

    public function getIdByName($idSpace, $name)
    {
        $sql = "select id from bk_calquantities where name=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($name, $idSpace));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getBySupID($idSpace, $id_quantity, $id_resource)
    {
        $sql = "SELECT * FROM bk_calquantities WHERE id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_quantity, $id_resource, $idSpace));
        if ($req->rowCount() == 1) {
            return $req->fetch();
        } else {
            return null;
        }
    }

    /**
     * Add a supplementary
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function addCalQuantity($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit = 0)
    {
        $sql = "insert into bk_calquantities(id_space, id_quantity, id_resource, name, mandatory, is_invoicing_unit)"
                . " values(?,?,?,?,?,?)";
        $this->runRequest($sql, array($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function setSupplementary($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit, $duration)
    {
        if ($this->isCalQuantityId($idSpace, $id_quantity, $id_resource)) {
            $this->updateCalQuantity($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit);
        } else {
            $this->addCalQuantity($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalQuantityId($idSpace, $id_quantity, $id_resource)
    {
        $sql = "select id from bk_calquantities where id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $unit = $this->runRequest($sql, array($id_quantity, $id_resource, $idSpace));
        return ($unit->rowCount() == 1);
    }

    /**
     * Update a supplementary
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     * @param int $is_invoicing_unit
     */
    public function updateCalQuantity($idSpace, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit = 0)
    {
        $sql = "update bk_calquantities set name= ?, mandatory=?, is_invoicing_unit = ? where id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($name, $mandatory, $is_invoicing_unit, $id_quantity, $id_resource, $idSpace));
    }

    /**
     * REmove a supplemenary from it ID
     * @param unknown $id
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE bk_calquantities SET deleted=1,deleted_at=NOW(), mandatory=0 WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param array $calsupNames
     * @param unknown $calsupValues
     * @param unknown $reservation_id
     */
    public function setEntryQuantityData($idSpace, $calsupNames, $calsupValues, $reservation_id)
    {
        $supData = "";
        for ($i = 0; $i < count($calsupNames); $i++) {
            $supData .= $calsupNames[$i] . ":=" . $calsupValues[$i] . ";";
        }

        $sql = "UPDATE bk_calendar_entry
                SET supplementary=?
				WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($supData, $reservation_id, $idSpace));
    }

    /**
     * Get the supplementary summary of calendar entry
     * @param unknown $entryID
     * @return string
     */
    public function getSummary($idSpace, $entryID)
    {
        $text = "";
        // get the entry sup entries
        $supData = $this->getQuantityData($idSpace, $entryID);
        foreach ($supData as $key => $value) {
            $text .= "<strong>" . $key . ": </strong>" . $value;
        }

        return $text;
    }

    /**
     * Get the sypplementary data of a given calendar entry
     * @param number $id
     * @return array supplementary
     */
    public function getQuantityData($idSpace, $id)
    {
        $sql = "select supplementary from bk_calendar_entry where id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $idSpace));
        $tmp = $req->fetch();
        $sups = explode(";", $tmp[0]);
        $supData = array();
        foreach ($sups as $sup) {
            $sup2 = explode(":=", $sup);
            if (count($sup2) == 2) {
                $supData[$sup2[0]] = $sup2[1];
            }
        }
        return $supData;
    }

    public function removeUnlisted($idSpace, $ids, $idIsSup=false)
    {
        $id_column = $idIsSup ? "id_quantity" : "id";
        $sql = "SELECT id, id_quantity FROM bk_calquantities WHERE deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($idSpace));
        $databaseQuantities = $req->fetchAll();

        foreach ($databaseQuantities as $dbQte) {
            $found = false;
            foreach ($ids as $id) {
                if ($dbQte[$id_column] == $id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->delete($idSpace, $dbQte["id"]);
            }
        }
    }
}
