<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreUser.php';

class Quote extends Model {

    public function __construct() {
        $this->tableName = "qo_quotes";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("recipient", "varchar(100)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("id_belonging", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("date_open", "date", "");
        $this->setColumnsInfo("date_last_modified", "date", "");
        $this->primaryKey = "id";
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM qo_quotes WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id_space, $id) {
        if(!$id) {
            return [
                "id" => 0,
                "recipient" => "",
                "address" => "",
                "id_belonging" => 0,
                "id_user" => 0,
                "date_open" => "",
                "date_last_modified" => ""
            ];
        }
        $sql = "SELECT * FROM qo_quotes WHERE id=? AND id_space=0 AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getAllInfo($id_space, $id) {
        $sql = "SELECT * FROM qo_quotes WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();

        if ($data['id_user'] != 0) {
            $modelUser = new CoreUser();
            $modelUserClient = new ClClientUser();
            $modelClient = new ClClient();

            $data["recipient"] = $modelUser->getUserFUllName($data["id_user"]);
            $resps = $modelUserClient->getUserClientAccounts($data["id_user"], $id_space);
            if (count($resps) > 0) {
                $data["address"] = $modelClient->getAddressInvoice($id_space, $resps[0]["id"]);
                $data["id_belonging"] = $resps[0]["id"];
                $data["id_pricing"] = $resps[0]["pricing"];
            }
        }
        return $data;
    }

    public function set($id, $id_space, $recipient, $address, $id_belonging, $id_user, $date_open) {
        if($date_open == "") {
            $date_open = null;
        }
        $date_last_modified = date('Y-m-d');
        if (!$id) {
            $sql = 'INSERT INTO qo_quotes (id_space, recipient, address, id_belonging, id_user, date_open, date_last_modified) VALUES (?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $recipient, $address, $id_belonging, $id_user, $date_open, $date_last_modified));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE qo_quotes SET recipient=?, address=?, id_belonging=?, id_user=?, date_open=?, date_last_modified=? WHERE id=? AND id_space=?';
            $this->runRequest($sql, array($recipient, $address, $id_belonging, $id_user, $date_open, $date_last_modified, $id, $id_space));
            return $id;
        }
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE qo_quotes SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM qo_quotes WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id));
    }

}
