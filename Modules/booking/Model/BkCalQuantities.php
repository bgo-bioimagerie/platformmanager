<?php

require_once 'Framework/Model.php';

/**
 * Model for calendar suplementary informations
 *
 * @author Sylvain Prigent
 */
class BkCalQuantities extends Model {

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
		PRIMARY KEY (`id`)
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * get the supplementaries infos
     * @param unknown $sortEntry
     * @return multitype:
     */
    public function calQuantities($sortEntry) {
        $sql = "select * from bk_calquantities order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql);
        return $data->fetchAll();
    }

    public function calQuantitiesByResource($id_resource) {
        $sql = "select * from bk_calquantities WHERE id_resource=?";
        return $this->runRequest($sql, array($id_resource))->fetchAll();
    }

    public function getAll() {
        $sql = "select * from bk_calquantities";
        return $this->runRequest($sql)->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalQuantities($id) {

        $sql = "select * from bk_calquantities where id=?;";
        $data = $this->runRequest($sql, array($id));
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
    public function getcalQuantityName($id) {

        $sql = "select name from bk_calquantities where id=?;";
        $data = $this->runRequest($sql, array($id));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return "not found";
        }
    }

    /**
     * Add a supplementary
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function addCalQuantity($id_quantity, $id_resource, $name, $mandatory) {

        $sql = "insert into bk_calquantities(id_quantity, id_resource, name, mandatory)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($id_quantity, $id_resource, $name, $mandatory));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function setCalQuantity($id_quantity, $id_resource, $name, $mandatory) {

        if ($this->isCalQuantityId($id_quantity, $id_resource)) {
            $this->updateCalQuantity($id_quantity, $id_resource, $name, $mandatory);
        } else {
            $this->addCalQuantity($id_quantity, $id_resource, $name, $mandatory);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalQuantityId($id_quantity, $id_resource) {
        $sql = "select id from bk_calquantities where id_quantity=? AND id_resource=?";
        $unit = $this->runRequest($sql, array($id_quantity, $id_resource));
        if ($unit->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update a supplementary
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function updateCalQuantity($id_quantity, $id_resource, $name, $mandatory) {
        $sql = "update bk_calquantities set name= ?, mandatory=? where id_quantity=? AND id_resource=?";
        $this->runRequest($sql, array($name, $mandatory, $id_quantity, $id_resource));
    }

    /**
     * REmove a supplemenary from it ID
     * @param unknown $id
     */
    public function delete($id) {
        $sql = "DELETE FROM bk_calquantities WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param unknown $calsupNames
     * @param unknown $calsupValues
     * @param unknown $reservation_id
     */
    public function setEntryQuantityData($calsupNames, $calsupValues, $reservation_id) {

        $supData = "";
        for ($i = 0; $i < count($calsupNames); $i++) {
            $supData .= $calsupNames[$i] . ":=" . $calsupValues[$i] . ";";
        }

        $sql = "update bk_calendar_entry set supplementary=?
									  where id=?";
        $this->runRequest($sql, array($supData, $reservation_id));
    }

    /**
     * Get the supplementary summary of calendar entry 
     * @param unknown $entryID
     * @return string
     */
    public function getSummary($entryID) {

        $text = "";
        // get the entry sup entries
        $supData = $this->getQuantityData($entryID);
        foreach ($supData as $key => $value) {
            $text .= "<b>" . $key . ": </b>" . $value;
        }

        return $text;
    }

    /**
     * Get the sypplementary data of a given calendar entry
     * @param number $id
     * @return array supplementary
     */
    public function getQuantityData($id) {
        $sql = "select supplementary from bk_calendar_entry where id=?";
        $req = $this->runRequest($sql, array($id));
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

    public function removeUnlistedQuantities($packageID) {

        $sql = "select id, id_quantity from bk_calquantities";
        $req = $this->runRequest($sql);
        $databasePackages = $req->fetchAll();

        //echo "databasePackages = ". print_r($databasePackages) . "<br/>";

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage["id_quantity"] == $pid) {
                    //echo "found package " . $pid . "in the database <br/>";
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                //echo "delete pacjkage id = " . $dbPackage["id"] . " package-id = " . $dbPackage["id_package"] . "<br/>";
                $this->delete($dbPackage["id"]);
            }
        }
    }

}
