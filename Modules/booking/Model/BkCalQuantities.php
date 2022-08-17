<?php

require_once 'Framework/Model.php';

/**
 * Model for calendar suplementary informations
 *
 * @author Sylvain Prigent
 */
class BkCalQuantities extends Model {

    public function __construct() {
        $this->tableName = "bk_calquantities";
    }

    /**
     * Create the calsupplementaries table
     *
     * @return PDOStatement
     */
    public function createTable() {

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
    public function calQuantities($id_space, $sortEntry) {
        $sql = "select * from bk_calquantities WHERE deleted=0 AND id_space=? order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function getByResource($id_space, $id_resource, $include_deleted=false) {
        $sql = "SELECT * from bk_calquantities WHERE id_resource=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        return $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();
    }

    public function getById($id_space, $id_qte) {
        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND id_space=?";
        return $this->runRequest($sql, array($id_qte, $id_space))->fetch();
    }

    /**
     * check if a quantity is deleted
     */
    public function isDeleted($id_space, $id_qte) {
        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND id_space=? AND deleted=1";
        $req = $this->runRequest($sql, array($id_qte, $id_space));
        return $req->rowCount() > 0;
    }

    public function getAll($id_space) {
        $sql = "select * from bk_calquantities WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getForSpace($id_space, $sort) {
        //$sql = "select * from bk_calquantities WHERE id_resource IN (SELECT id FROM re_info WHERE id_space=?) ORDER BY " . $sort . " ASC;";
        $sql = "SELECT * FROM bk_calquantities WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalQuantities($id_space, $id) {

        $sql = "SELECT * FROM bk_calquantities WHERE id=? AND deleted=0 AND id_space=?";
        $data = $this->runRequest($sql, array($id, $id_space));
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
    public function getcalQuantityName($id_space, $id) {

        $sql = "select name from bk_calquantities where id=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($id, $id_space));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return "not found";
        }
    }

    public function getIdByName($id_space, $name) {
        $sql = "select id from bk_calquantities where name=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($name, $id_space));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getBySupID($id_space, $id_quantity, $id_resource) {
        $sql = "SELECT * FROM bk_calquantities WHERE id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_quantity, $id_resource, $id_space));
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
    public function addCalQuantity($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit = 0) {
        $sql = "insert into bk_calquantities(id_space, id_quantity, id_resource, name, mandatory, is_invoicing_unit)"
                . " values(?,?,?,?,?,?)";
        $this->runRequest($sql, array($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function setSupplementary($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit, $duration) {

        if ($this->isCalQuantityId($id_space, $id_quantity, $id_resource)) {
            $this->updateCalQuantity($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit);
        } else {
            $this->addCalQuantity($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalQuantityId($id_space, $id_quantity, $id_resource) {
        $sql = "select id from bk_calquantities where id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $unit = $this->runRequest($sql, array($id_quantity, $id_resource, $id_space));
        return ($unit->rowCount() == 1);
    }

    /**
     * Update a supplementary
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     * @param int $is_invoicing_unit
     */
    public function updateCalQuantity($id_space, $id_quantity, $id_resource, $name, $mandatory, $is_invoicing_unit = 0) {
        $sql = "update bk_calquantities set name= ?, mandatory=?, is_invoicing_unit = ? where id_quantity=? AND id_resource=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($name, $mandatory, $is_invoicing_unit, $id_quantity, $id_resource, $id_space));
    }

    /**
     * REmove a supplemenary from it ID
     * @param unknown $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_calquantities SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param array $calsupNames
     * @param unknown $calsupValues
     * @param unknown $reservation_id
     */
    public function setEntryQuantityData($id_space, $calsupNames, $calsupValues, $reservation_id) {

        $supData = "";
        for ($i = 0; $i < count($calsupNames); $i++) {
            $supData .= $calsupNames[$i] . ":=" . $calsupValues[$i] . ";";
        }

        $sql = "UPDATE bk_calendar_entry
                SET supplementary=?
				WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($supData, $reservation_id, $id_space));
    }

    /**
     * Get the supplementary summary of calendar entry 
     * @param unknown $entryID
     * @return string
     */
    public function getSummary($id_space, $entryID) {

        $text = "";
        // get the entry sup entries
        $supData = $this->getQuantityData($id_space, $entryID);
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
    public function getQuantityData($id_space, $id) {
        $sql = "select supplementary from bk_calendar_entry where id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
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

    public function removeUnlisted($id_space, $ids, $idIsSup=false) {
        $id_column = $idIsSup ? "id_quantity" : "id";
        $sql = "SELECT id, id_quantity FROM bk_calquantities WHERE deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_space));
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
                $this->delete($id_space, $dbQte["id"]);
            }
        }
    }

}
