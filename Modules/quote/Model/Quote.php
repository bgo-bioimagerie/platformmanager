<?php

require_once 'Framework/Model.php';

class Quote extends Model {

    public function __construct() {
        $this->tableName = "qo_quotes";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("recipient", "varchar(100)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("id_belonging", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("date_open", "DATE", "");
        $this->setColumnsInfo("date_last_modified", "DATE", 0);
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM qo_quotes WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM qo_quotes WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getAllInfo($id_space, $id) {
        $sql = "SELECT * FROM qo_quotes WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();

        if ($data['id_user'] != 0) {
            $modelUser = new EcUser();
            $modelUnit = new EcUnit();

            $data["recipient"] = $modelUser->getUserFUllName($data["id_user"]);
            $resps = $modelUser->getUserResponsibles($data["id_user"]);
            if (count($resps) > 0) {
                $unitID = $modelUser->getUnit($resps[0]['id_resp']);
                $data["address"] = $modelUnit->getUnitName($unitID) . "\n" . $modelUnit->getAdress($unitID);
                $data["id_belonging"] = $modelUnit->getBelonging($unitID, $id_space);
            }
        }
        return $data;
    }

    public function set($id, $id_space, $recipient, $address, $id_belonging, $id_user, $date_open) {
        $date_last_modified = date('Y-m-d');
        if ($id == 0) {
            $sql = 'INSERT INTO qo_quotes (id_space, recipient, address, id_belonging, id_user, date_open, date_last_modified) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $recipient, $address, $id_belonging, $id_user, $date_open, $date_last_modified));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE qo_quotes SET id_space=?, recipient=?, address=?, id_belonging=?, id_user=?, date_open=?, date_last_modified=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $recipient, $address, $id_belonging, $id_user, $date_open, $date_last_modified, $id));
            return $id;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM qo_quotes WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
