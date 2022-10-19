<?php

require_once 'Framework/Model.php';

/**
 * Model for calendar suplementary informations
 *
 * @author Sylvain Prigent
 */
class BkCalSupInfo extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_calsupinfo";
    }

    /**
     * Create the calsupplementaries table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
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
    public function calSupInfos($idSpace, $sortEntry)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=? ORDER BY " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    public function getByResource($idSpace, $id_resource, $include_deleted = false)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id_resource=? AND id_space=?";
        if (!$include_deleted) {
            $sql .= " AND deleted=0";
        }
        return $this->runRequest($sql, array($id_resource, $idSpace))->fetchAll();
    }

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getForSpace($idSpace, $sort)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE deleted=0 AND id_space=? ORDER BY $sort ASC";
        //$sql = "select * from bk_calsupinfo WHERE deleted=0 AND id_space=? AND id_resource IN (SELECT id FROM re_info WHERE id_space=?) ORDER BY ".$sort." ASC;";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function getForResource($idSpace, $id_resource, $sort = "name")
    {
        $sql = "select * from bk_calsupinfo WHERE id_resource=? AND deleted=0 AND id_space=? ORDER BY ".$sort." ASC;";
        return $this->runRequest($sql, array($id_resource, $idSpace))->fetchAll();
    }

    /**
     * get a supplementary info from it ID
     * @param unknown $id
     * @return mixed|string
     */
    public function getcalSupInfos($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id=? AND deleted=0 AND id_space=?;";
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
    public function getcalSupInfoName($idSpace, $id)
    {
        $sql = "select name from bk_calsupinfo where id=? AND deleted=0 AND id_space=?;";
        $data = $this->runRequest($sql, array($id, $idSpace));
        if ($data->rowCount() == 1) {
            $tmp = $data->fetch();
            return $tmp[0];
        } else {
            return "not found";
        }
    }

    /**
     * Add a supplementary
     * @param String $name
     * @param String|Int $mandatory
     */
    public function addCalSupInfo($idSpace, $id_supinfo, $id_resource, $name, $mandatory)
    {
        $sql = "insert into bk_calsupinfo(id_supinfo, id_resource, name, mandatory, id_space)"
                . " values(?,?,?,?,?)";
        $this->runRequest($sql, array($id_supinfo, $id_resource, $name, $mandatory, $idSpace));
    }

    /**
     * Set a supplementaty (add if not exists update otherwise)
     * @param String|Int $id
     * @param String $name
     * @param String|Int $mandatory
     */
    public function setSupplementary($idSpace, $id_supinfo, $id_resource, $name, $mandatory, $is_invoicing_unit, $duration)
    {
        if ($this->isCalSupInfoId($idSpace, $id_supinfo, $id_resource)) {
            $this->updateCalSupInfo($idSpace, $id_supinfo, $id_resource, $name, $mandatory);
        } else {
            $this->addCalSupInfo($idSpace, $id_supinfo, $id_resource, $name, $mandatory);
        }
    }

    /**
     * Check if a suplementary exists from ID
     * @param unknown $id
     * @return boolean
     */
    public function isCalSupInfoId($idSpace, $id_supinfo, $id_resource)
    {
        $sql = "SELECT id FROM bk_calsupinfo WHERE id_supinfo=? AND id_resource=? AND deleted=0 AND id_space=?";
        $unit = $this->runRequest($sql, array($id_supinfo, $id_resource, $idSpace));
        return ($unit->rowCount() == 1);
    }

    public function getBySupID($idSpace, $id_supinfo, $id_resource)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id_supinfo=? AND id_resource=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_supinfo, $id_resource, $idSpace));
        if ($req->rowCount() == 1) {
            return $req->fetch();
        } else {
            return null;
        }
    }

    public function getById($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return $req->fetch();
        } else {
            return null;
        }
    }

    public function isDeleted($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_calsupinfo WHERE id=? AND id_space=? AND deleted=1";
        $req = $this->runRequest($sql, array($id, $idSpace));
        return $req->rowCount() > 0;
    }

    /**
     * Update a supplementary
     * @param unknown $id
     * @param unknown $name
     * @param unknown $mandatory
     */
    public function updateCalSupInfo($idSpace, $id_supinfo, $id_resource, $name, $mandatory)
    {
        $sql = "UPDATE bk_calsupinfo SET name= ?, mandatory=? WHERE id_supinfo=? AND id_resource=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($name, $mandatory, $id_supinfo, $id_resource, $idSpace));
    }

    /**
     * Remove a supplemenary from its ID
     * @param String|Int $id
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE bk_calsupinfo SET deleted=1,deleted_at=NOW(), mandatory=0 WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

    /**
     * Set the supplementary of a calendar entry
     * @param array $calsupNames
     * @param array $calsupValues
     */
    public function setEntrySupInfoData($idSpace, $calsupNames, $calsupValues, $reservation_id)
    {
        $supData = "";
        for ($i = 0; $i < count($calsupNames); $i++) {
            $supData .= $calsupNames[$i] . ":=" . $calsupValues[$i] . ";";
        }

        $sql = "UPDATE bk_calendar_entry
                SET supplementary=?
				WHERE id=? AND id_space=?";
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
        $supData = $this->getSupInfoData($idSpace, $entryID);
        foreach ($supData as $key => $value) {
            if ($value !== '') {
                $text .= "<strong>" . $key . ": </strong>" . $value . '<br/>';
            }
        }

        return $text;
    }

    /**
     * Get the sypplementary data of a given calendar entry
     * @param number $id
     * @return array supplementary
     */
    public function getSupInfoData($idSpace, $id)
    {
        $sql = "SELECT supplementaries FROM bk_calendar_entry WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if (!$req) {
            return array();
        }
        $tmp = $req->fetch();
        $sups = explode(";", $tmp[0]);
        // removes last element (systematically empty)
        array_pop($sups);
        $supData = array();
        foreach ($sups as $sup) {
            $sup2 = explode("=", $sup);
            if (count($sup2) == 2) {
                $sql = "SELECT name FROM bk_calsupinfo WHERE id=? AND id_space=?";
                $name = $this->runRequest($sql, array($sup2[0], $idSpace))->fetch();
                $supData[$name[0]] = $sup2[1];
            }
        }
        return $supData;
    }

    public function removeUnlisted($idSpace, $packageID, $idIsSup=false)
    {
        $id_column = $idIsSup ? "id_supinfo" : "id";
        $sql = "SELECT id, id_supinfo FROM bk_calsupinfo WHERE deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($idSpace));
        if (!$req) {
            return;
        }
        $databasePackages = $req->fetchAll();

        foreach ($databasePackages as $dbPackage) {
            $found = false;
            foreach ($packageID as $pid) {
                if ($dbPackage[$id_column] == $pid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->delete($idSpace, $dbPackage["id"]);
            }
        }
    }
}
