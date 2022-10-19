<?php

require_once 'Framework/Model.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';
/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeOrder extends Model
{
    public function __construct()
    {
        $this->tableName = "se_order";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `se_order` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
        `id_resp` int(11) NOT NULL,
        `id_space` int(11) NOT NULL,
        `id_user` int(11) NOT NULL,
		`id_status` int(1) NOT NULL,
		`date_open` DATE,
		`date_last_modified` DATE,
		`date_close` DATE,
        `no_identification` varchar(150) NOT NULL DEFAULT '',
        `id_invoice` int(11) NOT NULL DEFAULT 0,
        `created_by_id` int(11) NOT NULL DEFAULT 0,
        `modified_by_id` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);

        $this->addColumn("se_order", "id_resp", "int(11)", 0);
        $this->addColumn("se_order", "id_invoice", "int(11)", 0);
        $this->addColumn("se_order", "created_by_id", "int(11)", 0);
        $this->addColumn("se_order", "modified_by_id", "int(11)", 0);

        $sql2 = "CREATE TABLE IF NOT EXISTS `se_order_service` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_order` int(11) NOT NULL,
                `id_service` int(11) NOT NULL,
		`quantity` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql2);
    }

    public function setCreatedBy($idSpace, $id, $idUser)
    {
        $sql = "UPDATE se_order SET created_by_id=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($idUser, $id, $idSpace));
    }

    public function setModifiedBy($idSpace, $id, $idUser)
    {
        $sql = "UPDATE se_order SET modified_by_id=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($idUser, $id, $idSpace));
    }

    public function setInvoiceID($idSpace, $id, $id_invoice)
    {
        $sql = "UPDATE se_order SET id_invoice=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $id, $idSpace));
    }

    public function setInvoiceIDByNum($idSpace, $no_identification, $id_invoice)
    {
        $sql = "UPDATE se_order SET id_invoice=? WHERE no_identification=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $no_identification, $idSpace));
    }

    public function setService($idSpace, $id_order, $id_service, $quantity)
    {
        $orderHasService = $this->isOrderService($idSpace, $id_order, $id_service);
        if ($orderHasService == 1) {
            $sql = "UPDATE se_order_service SET quantity=? WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($quantity, $id_order, $id_service, $idSpace));
        } else {
            // if multiple instance of service were registered (backward fix), remove them before setting new service quantity
            if ($orderHasService > 1) {
                $sql = 'UPDATE se_order_service SET deleted=1,deleted_at=NOW() WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0';
                $this->runRequest($sql, array($id_order, $id_service, $idSpace));
            }
            $sql = "INSERT INTO se_order_service (id_order, id_service, quantity, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_order, $id_service, $quantity, $idSpace));
        }
    }

    public function isOrderService($idSpace, $id_order, $id_service): int
    {
        $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_order, $id_service, $idSpace));
        return $req->rowCount();
    }

    public function getOrderServices($idSpace, $id_order)
    {
        $sql = "SELECT orders.*, services.type_id as quantity_type "
                . "FROM se_order_service as orders "
                . "INNER JOIN se_services as services ON orders.id_service = services.id "
                //. "INNER JOIN se_service_types as types ON services.type_id = types.id "
                . "WHERE orders.id_order=? AND orders.id_space=? AND orders.deleted=0";
        $data = $this->runRequest($sql, array($id_order, $idSpace))->fetchAll();
        $services = array();
        $quantities = array();
        $quantity_types = array();
        foreach ($data as $d) {
            $services[] = $d["id_service"];
            $quantities[] = $d["quantity"];
            $quantity_types[] = $d["quantity_type"];
        }
        return array("services" => $services, "quantities" => $quantities, "quantity_types" => $quantity_types);
    }

    public function getOrderServiceQuantity($idSpace, $id_order, $id_service)
    {
        $sql = "SELECT sum(quantity) as total FROM se_order_service WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";

        $req =  $this->runRequest($sql, array($id_order, $id_service, $idSpace));
        if ($req->rowCount() == 1) {
            return $req->fetch()['total'];
        }
        return 0;
    }

    public function setOrder($id, $idSpace, $idUser, $id_client, $no_identification, $id_creator, $date_open, $date_last_modified = "", $date_close = "")
    {
        $id_status = 0;

        if ($date_close == "") {
            $date_close = null;
            $id_status = 1;
        }

        if ($date_open == "") {
            $date_open = null;
        }

        if ($date_last_modified == "") {
            $date_last_modified = null;
        }

        if ($this->isOrder($idSpace, $id)) {
            $this->updateEntry($id, $idSpace, $idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified, $date_close);
            return $id;
        } else {
            $idNew = $this->addEntry($idSpace, $idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified, $date_close);
            $this->setCreatedBy($idSpace, $idNew, $id_creator);
            return $idNew;
        }
    }

    public function isOrder($idSpace, $id)
    {
        $sql = "SELECT * FROM se_order WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function addEntry($idSpace, $idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified = "", $date_close = "")
    {
        if ($date_close == "") {
            $date_close = null;
        }

        if ($date_open == "") {
            $date_open = null;
        }

        if ($date_last_modified == "") {
            $date_last_modified = null;
        }

        $sql = "INSERT INTO se_order (id_space, id_user, id_resp, no_identification, id_status, date_open, date_last_modified, date_close)
				 VALUES(?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array(
            $idSpace, $idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified, $date_close
        ));
        return $this->getDatabase()->lastInsertId();
    }

    public function updateEntry($id, $idSpace, $idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified = "", $date_close = "")
    {
        if ($date_close == "") {
            $date_close = null;
            $id_status = 1;
        }

        if ($date_open == "") {
            $date_open = null;
        }

        if ($date_last_modified == "") {
            $date_last_modified = null;
        }
        $sql = "UPDATE se_order set id_user=?, id_resp=?, no_identification=?, id_status=?, date_open=?, date_last_modified=?, date_close=?
		        where id=? AND id_space=?";
        $this->runRequest($sql, array($idUser, $id_client, $no_identification, $id_status, $date_open, $date_last_modified, $date_close, $id, $idSpace));
    }

    public function entries($idSpace, $sortentry = 'id')
    {
        $sql = "SELECT se_order.*, core_users.firstname as firstname, core_users.name  as name, cl_clients.name as client_name from se_order LEFT JOIN core_users ON se_order.id_user=core_users.id LEFT JOIN cl_clients ON cl_clients.id=se_order.id_resp WHERE se_order.id_space=? AND se_order.deleted=0 order by se_order." . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($idSpace));
        $entries = $req->fetchAll();
        // $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            // $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
            $entries[$i]["user_name"] = $entries[$i]["name"] . " " . $entries[$i]["firstname"];
        }
        return $entries;
    }

    public function openedForClientPeriod($dateBegin, $dateEnd, $id_client, $idSpace)
    {
        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'id_client' => $id_client, 'id_space' => $idSpace);
        $sql = "SELECT * FROM se_order WHERE id_status=1 "
                . "AND id_resp=:id_client "
                . "AND date_open>=:start "
                . "AND (date_close IS NULL OR date_close<=:end) "
                . "AND id_space=:id_space AND deleted=0";
        $req = $this->runRequest($sql, $q);
        return $req->fetchAll();
    }

    public function openedEntries($idSpace, $sortentry = 'id')
    {
        $sql = "SELECT se_order.*, core_users.firstname as firstname, core_users.name as name, cl_clients.name as client_name from se_order LEFT JOIN core_users ON se_order.id_user=core_users.id LEFT JOIN cl_clients ON cl_clients.id=se_order.id_resp WHERE se_order.id_status=1 AND se_order.id_space=? AND se_order.deleted=0 order by se_order." . $sortentry . " ASC;";
        //$sql = "select * from se_order where deleted=0 AND id_space=? AND id_status=1 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($idSpace));

        $entries = $req->fetchAll();
        // $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $entries[$i]["name"] . " " . $entries[$i]["firstname"];
            // $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
        }
        return $entries;
    }

    public function closedEntries($idSpace, $sortentry = 'id')
    {
        $sql = "SELECT se_order.*, core_users.firstname as firstname, core_users.name as name, cl_clients.name as client_name from se_order LEFT JOIN core_users ON se_order.id_user=core_users.id LEFT JOIN cl_clients ON cl_clients.id=se_order.id_resp WHERE se_order.id_status=0 AND se_order.id_space=? AND se_order.deleted=0 order by se_order." . $sortentry . " ASC;";
        // $sql = "select * from se_order where id_space=? AND deleted=0 AND id_status=0 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($idSpace));

        $entries = $req->fetchAll();

        // $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $entries[$i]["name"] . " " . $entries[$i]["firstname"];
            // $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
        }
        return $entries;
    }

    public function defaultEntryValues()
    {
        $entry["id"] = "";
        $entry["id_user"] = "";
        $entry["id_resp"] = "";
        $entry["id_space"] = 0;
        $entry["id_status"] = 1;
        $entry["date_open"] = date("Y-m-d", time());
        $entry["date_last_modified"] = "";
        $entry["date_close"] = "";
        $entry["orders"] = array();
        $entry["no_identification"] = "";
        return $entry;
    }

    public function getEntry($idSpace, $id)
    {
        $sql = "SELECT * from se_order where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        return $req->fetch();
    }

    public function setEntryClosed($idSpace, $id)
    {
        $sql = "UPDATE se_order set id_status=0, date_close=?
		        where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array(date("Y-m-d", time()), $id, $idSpace));
    }

    public function reopenEntry($idSpace, $no_identification)
    {
        $sql = "UPDATE se_order set id_status=1, date_close=null
		        where no_identification=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($no_identification, $idSpace));
    }

    public function openedOrdersItems($idSpace, $id_orders)
    {
        $sql = "SELECT * FROM se_order_service WHERE id_order IN (".implode(',', $id_orders).") AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function openedItemsForClient($idSpace, $id_client)
    {
        //$userList = " SELECT id_user FROM cl_j_client_user WHERE id_client=? AND id_space=? AND deleted=0 ";
        //$orderList = " SELECT id FROM se_order WHERE id_user IN (".$userList.") AND id_status=1 AND id_space=? AND deleted=0";
        $orderList = " SELECT id FROM se_order WHERE id_resp=? AND id_status=1 AND id_space=? AND deleted=0";
        $sql = "SELECT * FROM se_order_service WHERE id_order IN (".$orderList.")";
        return $this->runRequest($sql, array($id_client, $idSpace))->fetchAll();
        //return $this->runRequest($sql, array($id_client, $idSpace, $idSpace))->fetchAll();
    }

    public function getOrdersOpenedPeriod($idSpace, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM se_order WHERE deleted=0 AND id_space = ? AND date_open >= ? AND date_open <= ?";
        $req = $this->runRequest($sql, array($idSpace, $periodStart, $periodEnd));
        $orders = $req->fetchAll();
        for ($i = 0 ; $i < count($orders) ; $i++) {
            if ($orders[$i]["id_resp"] == 0) {
                $sql = "SELECT id_client FROM cl_j_client_user WHERE id_user=? AND id_space=? AND deleted=0";
                $res = $this->runRequest($sql, array($orders[$i]["id_user"], $idSpace));
                if ($res->rowCount() == 1) {
                    $resp_id = $res->fetch();
                    $orders[$i]["id_resp"] = $resp_id[0];
                } else {
                    Configuration::getLogger()->error('Client is unknown and cannot be guessed!', ['user' => $orders[$i]["id_user"], 'order' => $orders[$i]['id']]);
                }
            }
        }
        return $orders;
    }

    public function getPeriodeServicesBalancesOrders($idSpace, $periodStart, $periodEnd)
    {
        $sql = "select * from se_order where deleted=0 AND id_space=? AND date_open>=? OR date_open=?";
        $req = $this->runRequest($sql, array($idSpace, $periodStart, $periodEnd));
        $orders = $req->fetchAll();

        $modelServices = new SeService();
        $items = $modelServices->getBySpace($idSpace);

        $modelClientUser = new ClClientUser();
        $modelClient = new ClClient();
        $filteredOrders = [];
        for ($i = 0; $i < count($orders); $i++) {
            $order = $orders[$i];
            if (!isset($order["id_resp"]) || $order["id_resp"] == 0) {
                $resps = $modelClientUser->getUserClientAccounts($order["id_user"], $idSpace);
                if ($resps && count($resps) == 1) {
                    $order["id_resp"] = $resps[0]['id'];
                } else {
                    Configuration::getLogger()->error('Client is unknown and cannot be guessed!', ['user' => $orders[$i]["id_user"], 'order' => $orders[$i]['id']]);
                    continue;
                }
            }

            $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_space=? AND deleted=0";
            $itemsSummary = $this->runRequest($sql, array($orders[$i]["id"], $idSpace));

            $order["entries"] = $itemsSummary;
            $LABpricingid = $modelClient->getPricingID($idSpace, $orders[$i]["id_resp"]);
            $order["total"] = $this->calculateOrderTotal($idSpace, $itemsSummary, $LABpricingid);
            $filteredOrders[] = $order;
        }

        return array("items" => $items, "orders" => $filteredOrders);
    }

    protected function calculateOrderTotal($idSpace, $itemsSummary, $LABpricingid)
    {
        $itemPricing = new SePrice();
        $totalHT = 0;
        foreach ($itemsSummary as $item) {
            if ($item["quantity"] > 0) {
                $unitaryPrice = $itemPricing->getPrice($idSpace, $item["id_service"], $LABpricingid);
                $totalHT += (float) $item["quantity"] * (float) $unitaryPrice;
            }
        }
        return $totalHT;
    }

    public function getPeriodeBilledServicesBalancesOrders($idSpace, $periodStart, $periodEnd)
    {
        // get the projects
        $sql1 = "SELECT * FROM se_order WHERE deleted=0 AND id_space=? AND id_invoice > 0 AND date_open >= ? AND date_open <= ?";
        $req1 = $this->runRequest($sql1, array($idSpace, $periodStart, $periodEnd));
        $orders = $req1->fetchAll();

        $items = array();
        $modelUserClient = new ClClientUser();
        $modelClient = new ClClient();
        $filteredOrders = [];
        for ($i = 0; $i < count($orders); $i++) {
            $order = $orders[$i];

            if (!isset($order["id_resp"]) || $order["id_resp"] == 0) {
                $resps = $modelUserClient->getUserClientAccounts($orders[$i]["id_user"], $idSpace);
                if ($resps && count($resps) == 1) {
                    $order["id_resp"] = $resps[0]['id'];
                } else {
                    Configuration::getLogger()->error('Client is unknown and cannot be guessed!', ['user' => $orders[$i]["id_user"], 'order' => $orders[$i]['id']]);
                    continue;
                }
            }

            $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_space=? AND deleted=0";
            $itemsSummary = $this->runRequest($sql, array($orders[$i]["id"], $idSpace));

            $order["entries"] = $itemsSummary;
            $LABpricingid = $modelClient->getPricingID($idSpace, $order["id_resp"]);
            $order["total"] = $this->calculateOrderTotal($idSpace, $itemsSummary, $LABpricingid);
            $filteredOrders[] = $order;
        }

        return array("items" => $items, "orders" => $filteredOrders);
    }
    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE se_order SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

    /**
     * Delete se_order_service entry
     * @param number $idSpace
     * @param number $id se_order_service
     */
    public function deleteOrderService($idSpace, $id_service, $id_order)
    {
        $sql = "UPDATE se_order_service SET deleted=1, deleted_at=NOW() WHERE id_service=? AND id_order=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_service, $id_order, $idSpace));
    }
}
