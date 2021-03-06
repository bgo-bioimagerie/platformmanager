<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Sygrrif pricing model
 *
 * @author Sylvain Prigent
 */
class BkNightWE extends Model {

    public function __construct() {

        $this->tableName = "bk_nightwe";
        $this->setColumnsInfo("id", "int(11)", 0);
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

    public function isNight($id) {
        $sql = "SELECT tarif_night FROM bk_nightwe WHERE id_belonging=? AND tarif_night=?";
        $req = $this->runRequest($sql, array($id, 1));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function isWe($id) {
        $sql = "SELECT tarif_night FROM bk_nightwe WHERE id_belonging=? AND tarif_we=?";
        $req = $this->runRequest($sql, array($id, 1));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get all the prices info
     * @param string $sortEntry
     * @return multitype:
     */
    public function getSpacePrices($id_space, $sortEntry = 'id') {
        $sql = "select * from bk_nightwe WHERE id_space=? order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    /**
     * get pricing ID from ID
     * @param unknown $id
     * @throws Exception
     * @return mixed
     */
    public function getPricing($id, $id_space) {
        $sql = "select * from bk_nightwe where id_belonging=? AND id_space=?";
        $data = $this->runRequest($sql, array($id, $id_space));
        if ($data->rowCount() > 0) {
            return $data->fetch();  // get the first line of the result
        } else {
            return array();
            //throw new Exception("Cannot find the pricing using the given id:" . $id);
        }
    }

    /**
     * add a unique pricing
     * @param unknown $id
     * @return PDOStatement
     */
    public function addUnique($id, $id_space) {
        $sql = "INSERT INTO bk_nightwe (id_belonging, id_space) VALUES(?,?)";
        $pdo = $this->runRequest($sql, array($id, $id_space));
        return $pdo;
    }
    
    public function addBelongingIfNotExists($id_space, $belongings){
        foreach($belongings as $b){
            if (!$this->isPricing($b["id"], $id_space)){
                $this->addUnique($b["id"], $id_space);
            }
        }
    }

    /**
     * add a pricing
     * @param unknown $id
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     * @return PDOStatement
     */
    public function addPricing($id_belonging, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char) {
        $sql = "INSERT INTO bk_nightwe (id_belonging, id_space, tarif_unique, tarif_night, night_start,
				                        night_end, tarif_we, choice_we ) VALUES(?,?,?,?,?,?,?,?)";
        $pdo = $this->runRequest($sql, array($id_belonging, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char));
        return $pdo;
    }

    /**
     * Update a pricing infos
     * @param unknown $id_belonging
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     */
    public function editPricing($id_belonging, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char) {
        $sql = "update bk_nightwe set tarif_unique=?, tarif_night=?, night_start=?,
				                      night_end=?, tarif_we=?, choice_we=?
									  where id_belonging=? AND id_space=?";
        $this->runRequest($sql, array($tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char, $id_belonging, $id_space));
    }

    /**
     * Check if a pricing exists
     * @param unknown $name
     * @return boolean
     */
    private function isPricing($id_belonging, $id_space) {
        $sql = "select * from bk_nightwe where id_belonging=? AND id_space=?;";
        $data = $this->runRequest($sql, array($id_belonging, $id_space));
        if ($data->rowCount() == 1){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Add pricing if pricing name does not exists
     * @param unknown $nom
     * @param unknown $tarif_unique
     * @param unknown $tarif_nuit
     * @param unknown $night_start
     * @param unknown $night_end
     * @param unknown $tarif_we
     * @param unknown $we_char
     */
    public function setPricing($id_belonging, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char) {
        if (!$this->isPricing($id_belonging, $id_space)) {
            $this->addPricing($id_belonging, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char);
        }
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id) {
        $sql = "DELETE FROM bk_nightwe WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
