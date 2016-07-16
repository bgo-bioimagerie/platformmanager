<?php

require_once 'Framework/Model.php';

/**
 * Model for calendar suplementary informations
 *
 * @author Sylvain Prigent
 */
class BkCalSupInfo extends Model {

    /**
     * Create the calsupplementaries table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_calsupinfo` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_supinfo` int(11) NOT NULL,
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
    public function calSupInfos($sortEntry) {
        $sql = "select * from bk_calsupinfo order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql);
        return $data->fetchAll();
    }

    public function calSupInfosByResource($id_resource) {
        $sql = "select * from bk_calsupinfo WHERE id_resource=?";
        return $this->runRequest($sql, array($id_resource))->fetchAll();
    }

    public function getAll() {
        $sql = "select * from bk_calsupinfo";
        return $this->runRequest($sql)->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalSupInfos($id) {

        $sql = "select * from bk_calsupinfo where id=?;";
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
    public function getcalSupInfoName($id) {

        $sql = "select name from bk_calsupinfo where id=?;";
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
    public function addCalSupInfo($id_supinfo, $id_resource, $name, $mandatory) {

        $sql = "insert into bk_calsupinfo(id_supinfo, id_resource, name, mandatory)"
                . " values(?,?,?,?)";
        $this->runRequest($sql, array($id_supinfo, $id_resource, $name, $mandatory));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function setCalSupInfo($id_supinfo, $id_resource, $name, $mandatory) {

        if ($this->isCalSupInfoId($id_supinfo, $id_resource)) {
            $this->updateCalSupInfo($id_supinfo, $id_resource, $name, $mandatory);
        } else {
            $this->addCalSupInfo($id_supinfo, $id_resource, $name, $mandatory);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalSupInfoId($id_supinfo, $id_resource) {
        $sql = "select id from bk_calsupinfo where id_supinfo=? AND id_resource=?";
        $unit = $this->runRequest($sql, array($id_supinfo, $id_resource));
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
    public function updateCalSupInfo($id_supinfo, $id_resource, $name, $mandatory) {
        $sql = "update bk_calsupinfo set name= ?, mandatory=? where id_supinfo=? AND id_resource=?";
        $this->runRequest($sql, array($name, $mandatory, $id_supinfo, $id_resource));
    }

    /**
     * REmove a supplemenary from it ID
     * @param unknown $id
     */
    public function delete($id) {
        $sql = "DELETE FROM bk_calsupinfo WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param unknown $calsupNames
     * @param unknown $calsupValues
     * @param unknown $reservation_id
     */
    public function setEntrySupInfoData($calsupNames, $calsupValues, $reservation_id) {

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
        $supData = $this->getSupInfoData($entryID);
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
    public function getSupInfoData($id) {
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

    public function removeUnlistedSupInfos($packageID) {

        $sql = "select id, id_supinfo from bk_calsupinfo";
        $req = $this->runRequest($sql);
        $databasePackages = $req->fetchAll();

        //echo "databasePackages = ". print_r($databasePackages) . "<br/>";

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage["id_supinfo"] == $pid) {
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
