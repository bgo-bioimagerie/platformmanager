<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreUser.php';

class Quote extends Model
{
    public function __construct()
    {
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

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM qo_quotes WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function get($idSpace, $id)
    {
        if (!$id) {
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
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    /**
     * Get all infos relative to a quote.
     * Behaviour depends on how quote has been created (w/wo client_id, belonging_id or user_id)
     *
     * @param int|string $idSpace
     * @param int|string $id id of the quote
     *
     * @return array(string) quote infos
     */
    public function getAllInfo($idSpace, $id)
    {
        $sql = "SELECT * FROM qo_quotes WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $idSpace))->fetch();

        $modelUser = new CoreUser();
        $modelUserClient = new ClClientUser();
        $modelClient = new ClClient();

        $data['id_pricing'] = $data['id_belonging'];
        if ($data["id_client"] && $data["id_client"] != 0) {
            $client = $modelClient->get($idSpace, $data["id_client"]);
        } else {
            $clients = $modelUserClient->getUserClientAccounts($data["id_user"], $idSpace);
            if ($clients && !empty($clients)) {
                $client = $clients[0];
            } else {
                $client = null;
            }
        }

        if ($client != null) {
            $data['id_pricing'] = $client['pricing'];
            if (!$data['address']) {
                $data["address"] = $modelClient->getAddressInvoice($idSpace, $client["id"]) ?? "";
            }
        }
        $data['client'] = $client;

        if ($data['id_user'] != 0) {
            $data["recipient"] = $modelUser->getUserFullName($data["id_user"]);
        }

        return $data;
    }

    public function set($id, $idSpace, $recipient, $recipient_email, $address, $id_belonging, $idUser, $id_client, $date_open)
    {
        if ($date_open == "") {
            $date_open = null;
        }
        $date_last_modified = date('Y-m-d');
        if ($id_client == "") {
            $id_client = 0;
        }
        if (!$id) {
            $sql = 'INSERT INTO qo_quotes (id_space, recipient, recipient_email, address, id_belonging, id_user, id_client, date_open, date_last_modified) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($idSpace, $recipient, $recipient_email, $address, $id_belonging, $idUser, $id_client, $date_open, $date_last_modified));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE qo_quotes SET recipient=?, recipient_email=?, address=?, id_belonging=?, id_user=?, id_client=?, date_open=?, date_last_modified=? WHERE id=? AND id_space=?';
            $this->runRequest($sql, array($recipient, $recipient_email, $address, $id_belonging, $idUser, $id_client, $date_open, $date_last_modified, $id, $idSpace));
        }
        Events::send([
            "action" => Events::ACTION_QUOTE_EDIT,
            "space" => ["id" => intval($idSpace)],
            "quote" => ["id" => $id]
        ]);
        return $id;
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE qo_quotes SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
        Events::send([
            "action" => Events::ACTION_QUOTE_DELETE,
            "space" => ["id" => intval($idSpace)],
            "quote" => ["id" => $id]
        ]);
    }
}
