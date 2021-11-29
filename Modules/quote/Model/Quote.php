<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreUser.php';

class Quote extends Model {

    public function __construct() {
        $this->tableName = "qo_quotes";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("recipient", "varchar(100)", "");
        $this->setColumnsInfo("recipient_email", "varchar(100)", "");
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("id_belonging", "int(11)", "");
        $this->setColumnsInfo("id_user", "int(11)", "");
        $this->setColumnsInfo("id_client", "int(11)", "");
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
                "recipient_email" => "",
                "address" => "",
                "id_belonging" => 0,
                "id_user" => 0,
                "id_client" => 0,
                "date_open" => "",
                "date_last_modified" => ""
            ];
        }
        $sql = "SELECT * FROM qo_quotes WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getAllInfo($id_space, $id) {
        $sql = "SELECT * FROM qo_quotes WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();

        $modelUser = new CoreUser();
        $modelUserClient = new ClClientUser();
        $modelClient = new ClClient();

        if ($data['id_user'] == 0) {
            // quote edited with "new user quote"
            $data['client'] = $modelClient->get($id_space, $data["id_client"]);
            $data['id_pricing'] = $data['id_belonging'];
        } else {
            $data["recipient"] = $modelUser->getUserFUllName($data["id_user"]);
            if ($data["id_client"] && $data["id_client"] != 0) {
                // quote was edited with a client => get its client
                $client = $modelClient->get($id_space, $data["id_client"]);
            } else {
                // get first client from user's clients (original behaviour)
                $client = $modelUserClient->getUserClientAccounts($data["id_user"], $id_space)[0];
            }
            if (!$data['address'] || $data['address'] === "") {
                $data["address"] = $modelClient->getAddressInvoice($id_space, $client["id"]);
            }
            $data["id_belonging"] = $client["id"];
            $data["id_pricing"] = $client["pricing"];
            $data["client"] = $client;
        }
        return $data;
    }

    public function set($id, $id_space, $recipient, $recipient_email, $address, $id_belonging, $id_user, $id_client, $date_open) {
        if($date_open == "") {
            $date_open = null;
        }
        $date_last_modified = date('Y-m-d');
        if($id_client == "") {
            $id_client = 0;
        }
        if (!$id) {
            $sql = 'INSERT INTO qo_quotes (id_space, recipient, recipient_email, address, id_belonging, id_user, id_client, date_open, date_last_modified) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $recipient, $recipient_email, $address, $id_belonging, $id_user, $id_client, $date_open, $date_last_modified));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE qo_quotes SET recipient=?, recipient_email=?, address=?, id_belonging=?, id_user=?, id_client=?, date_open=?, date_last_modified=? WHERE id=? AND id_space=?';
            $this->runRequest($sql, array($recipient, $recipient_email, $address, $id_belonging, $id_user, $id_client, $date_open, $date_last_modified, $id, $id_space));
            return $id;
        }
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE qo_quotes SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
