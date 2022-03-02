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

       return $this->runRequest($sql);
    }

    /**
     * get the supplementaries infos
     * @param unknown $sortEntry
     * @return multitype:
     */
    public function calSupInfos($id_space, $sortEntry) {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=? ORDER BY " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function calSupInfosByResource($id_space, $id_resource) {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id_resource=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getForSpace($id_space, $sort) {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=? ORDER BY $sort ASC";
        //$sql = "select * from bk_calsupinfo WHERE deleted=0 AND id_space=? AND id_resource IN (SELECT id FROM re_info WHERE id_space=?) ORDER BY ".$sort." ASC;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getForResource($id_space, $id_resource, $sort = "name"){
        $sql = "select * from bk_calsupinfo WHERE id_resource=? AND deleted=0 AND id_space=? ORDER BY ".$sort." ASC;";
        return $this->runRequest($sql, array($id_resource, $id_space))->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalSupInfos($id_space, $id) {

        $sql = "SELECT * FROM bk_calsupinfo WHERE id=? AND deleted=0 AND id_space=?;";
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
    public function getcalSupInfoName($id_space, $id) {

        $sql = "select name from bk_calsupinfo where id=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($id, $id_space));
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
    public function addCalSupInfo($id_space, $id_supinfo, $id_resource, $name, $mandatory) {

        $sql = "insert into bk_calsupinfo(id_supinfo, id_resource, name, mandatory, id_space)"
                . " values(?,?,?,?,?)";
        $this->runRequest($sql, array($id_supinfo, $id_resource, $name, $mandatory, $id_space));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function setCalSupInfo($id_space, $id_supinfo, $id_resource, $name, $mandatory) {

        if ($this->isCalSupInfoId($id_space, $id_supinfo, $id_resource)) {
            $this->updateCalSupInfo($id_space, $id_supinfo, $id_resource, $name, $mandatory);
        } else {
            $this->addCalSupInfo($id_space, $id_supinfo, $id_resource, $name, $mandatory);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalSupInfoId($id_space, $id_supinfo, $id_resource) {
        $sql = "SELECT id FROM bk_calsupinfo WHERE id_supinfo=? AND id_resource=? AND deleted=0 AND id_space=?";
        $unit = $this->runRequest($sql, array($id_supinfo, $id_resource, $id_space));
        return ($unit->rowCount() == 1);
    }

    /**
     * Update a supplementary
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function updateCalSupInfo($id_space, $id_supinfo, $id_resource, $name, $mandatory) {
        $sql = "UPDATE bk_calsupinfo SET name= ?, mandatory=? WHERE id_supinfo=? AND id_resource=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($name, $mandatory, $id_supinfo, $id_resource, $id_space));
    }

    /**
     * REmove a supplemenary from it ID
     * @param unknown $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_calsupinfo SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param array $calsupNames
     * @param array $calsupValues
     */
    public function setEntrySupInfoData($id_space, $calsupNames, $calsupValues, $reservation_id) {

        $supData = "";
        for ($i = 0; $i < count($calsupNames); $i++) {
            $supData .= $calsupNames[$i] . ":=" . $calsupValues[$i] . ";";
        }

        $sql = "UPDATE bk_calendar_entry
                SET supplementary=?
				WHERE id=? AND id_space=?";
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
        $supData = $this->getSupInfoData($id_space, $entryID);
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
    public function getSupInfoData($id_space, $id) {
        $sql = "SELECT supplementaries FROM bk_calendar_entry WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        if(!$req) {
            return array();
        }
        $tmp = $req->fetch();
        $sups = explode(";", $tmp[0]);
        $supData = array();
        foreach ($sups as $sup) {
            $sup2 = explode("=", $sup);
            if (count($sup2) == 2) {
                $sql = "SELECT name FROM bk_calsupinfo WHERE id=? AND deleted=0 AND id_space=?";
                $name = $this->runRequest($sql, array($sup2[0], $id_space))->fetch();
                $supData[$name[0]] = $sup2[1];
            }
        }
        return $supData;
    }

    public function removeUnlistedSupInfos($id_space, $packageID) {

        $sql = "SELECT id, id_supinfo FROM bk_calsupinfo WHERE deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        if (!$req) {
            return;
        }
        $databasePackages = $req->fetchAll();

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage["id_supinfo"] == $pid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->delete($id_space, $dbPackage["id"]);
            }
        }
    }

}
