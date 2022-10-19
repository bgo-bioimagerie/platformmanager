<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Sygrrif pricing model
 *
 * @author Sylvain Prigent
 */
class BkNightWE extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_nightwe";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_belonging", "int(11)", 0);
        $this->setColumnsInfo("tarif_unique", "int(11)", 1);
        $this->setColumnsInfo("tarif_night", "int(3)", 0);
        $this->setColumnsInfo("night_start", "int(3)", 19);
        $this->setColumnsInfo("night_end", "int(11)", 8);
        $this->setColumnsInfo("tarif_we", "int(3)", 0);
        $this->setColumnsInfo("choice_we", "varchar(100)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getDefault()
    {
        return array(
            "id" => "",
            "id_belonging" => 0,
            "tarif_unique" => 1,
            "tarif_night" => 0,
            "night_start" => 19,
            "night_end" => 8,
            "tarif_we" => 0,
            "choice_we" => "0,0,0,0,0,1,1",
            "id_space" => 0,
        );
    }

    public function isNight($idSpace, $id)
    {
        $sql = "SELECT tarif_night FROM bk_nightwe WHERE id_belonging=? AND tarif_night=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, 1, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function isWe($idSpace, $id)
    {
        $sql = "SELECT tarif_night FROM bk_nightwe WHERE id_belonging=? AND tarif_we=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, 1, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get all the prices info
     * @param string $sortEntry
     * @return array multitype:
     */
    public function getSpacePrices($idSpace, $sortEntry = 'id')
    {
        $sql = "select * from bk_nightwe WHERE id_space=? AND deleted=0 order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($idSpace));
        return $data->fetchAll();
    }

    /**
     * get pricing ID from ID
     * @param int $id
     * @param int $idSpace
     * @throws Exception
     * @return array
     */
    public function getPricing($id, $idSpace)
    {
        $sql = "select * from bk_nightwe where id_belonging=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $idSpace));
        if ($data->rowCount() > 0) {
            return $data->fetch();  // get the first line of the result
        } else {
            return $this->getDefault();
        }
    }

    /**
     * add a unique pricing
     * @param int $id
     * @return int|bool
     */
    public function addUnique($id, $idSpace)
    {
        $sql = "INSERT INTO bk_nightwe (id_belonging, id_space) VALUES(?,?)";
        $this->runRequest($sql, array($id, $idSpace));
        return $this->getDatabase()->lastInsertId();
    }

    public function addBelongingIfNotExists($idSpace, $belongings)
    {
        foreach ($belongings as $b) {
            if (!$this->isPricing($b["id"], $idSpace)) {
                $this->addUnique($b["id"], $idSpace);
            }
        }
    }

    /**
     * add a pricing
     * @param int $id
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     * @return PDOStatement
     */
    public function addPricing($id_belonging, $idSpace, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char)
    {
        $sql = "INSERT INTO bk_nightwe (id_belonging, id_space, tarif_unique, tarif_night, night_start,
				                        night_end, tarif_we, choice_we ) VALUES(?,?,?,?,?,?,?,?)";
        return $this->runRequest($sql, array($id_belonging, $idSpace, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char));
    }

    /**
     * Update a pricing infos
     * @param int $id_belonging
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     */
    public function editPricing($id_belonging, $idSpace, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char)
    {
        $sql = "UPDATE bk_nightwe SET tarif_unique=?, tarif_night=?, night_start=?,
				                      night_end=?, tarif_we=?, choice_we=?
									  WHERE id_belonging=? AND id_space=?";
        $this->runRequest($sql, array($tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char, $id_belonging, $idSpace));
    }

    /**
     * Check if a pricing exists
     * @param int id_belonging
     * @param int id_space
     * @return boolean
     */
    private function isPricing($id_belonging, $idSpace)
    {
        $sql = "SELECT * FROM bk_nightwe WHERE id_belonging=? AND id_space=? AND deleted=0;";
        $data = $this->runRequest($sql, array($id_belonging, $idSpace));
        return ($data->rowCount() == 1);
    }

    /**
     * Add pricing if pricing name does not exists
     * @param int $id_belonging
     * @param int $idSpace
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     */
    public function setPricing($id_belonging, $idSpace, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char)
    {
        if (!$this->isPricing($id_belonging, $idSpace)) {
            $this->addPricing($id_belonging, $idSpace, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char);
        }
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE bk_nightwe SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
