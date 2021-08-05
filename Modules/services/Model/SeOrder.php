<?php

require_once 'Framework/Model.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';
/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeOrder extends Model {

    public function createTable() {
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

    public function setCreatedBy($id_space, $id, $id_user){
        $sql = "UPDATE se_order SET created_by_id=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_user, $id, $id_space));
    }

    public function setModifiedBy($id_space, $id, $id_user){
        $sql = "UPDATE se_order SET modified_by_id=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_user, $id, $id_space));
    }

    public function setInvoiceID($id_space, $id, $id_invoice){
        $sql = "UPDATE se_order SET id_invoice=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $id, $id_space));
    }

    public function setService($id_space, $id_order, $id_service, $quantity) {
        if ($this->isOrderService($id_space, $id_order, $id_service)) {
            $sql = "UPDATE se_order_service SET quantity=? WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($quantity, $id_order, $id_service, $id_space));
        } else {
            $sql = "INSERT INTO se_order_service (id_order, id_service, quantity, id_space) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_order, $id_service, $quantity, $id_space));
        }
    }

    public function isOrderService($id_space, $id_order, $id_service) {
        $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_order, $id_service, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function getOrderServices($id_space, $id_order) {
        $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_order, $id_space))->fetchAll();
        $services = array();
        $quantities = array();
        foreach($data as $d){
            $services[] = $d["id_service"];
            $quantities[] = $d["quantity"];
        }
        return array("services" => $services, "quantities" => $quantities);
    }

    public function getOrderServiceQuantity($id_space, $id_order, $id_service){
        $sql = "SELECT quantity FROM se_order_service WHERE id_order=? AND id_service=? AND id_space=? AND deleted=0";

        $req =  $this->runRequest($sql, array($id_order, $id_service, $id_space));
        if ($req->rowCount() == 1){
            return $req->fetch();
        }
        return 0;
    }

    public function setOrder($id, $id_space, $id_user, $no_identification, $id_creator, $date_open, $date_last_modified = "", $date_close = ""){
        $id_status = 0;
        if($date_close == "") {
            $date_close = null;
        }
        if ($date_close==null){
            $id_status = 1;
        }

        if($date_open == "") {
            $date_open = null;
        }

        if($date_last_modified == "") {
            $date_last_modified = null;
        }

        if ($this->isOrder($id_space, $id)){
            $this->updateEntry($id, $id_space, $id_user, $no_identification, $id_status, $date_open, $date_last_modified, $date_close);
            return $id;
        }
        else{
            $idNew = $this->addEntry($id_space, $id_user, $no_identification, $id_status, $date_open, $date_last_modified, $date_close);
            $this->setCreatedBy($id_space, $idNew, $id_creator);
            return $idNew;
        }
    }

    public function isOrder($id_space, $id){
        $sql = "SELECT * FROM se_order WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }

    public function addEntry($id_space, $id_user, $no_identification, $id_status, $date_open, $date_last_modified = "", $date_close = "") {
        if($date_close == "") {
            $date_close = null;
        }

        if($date_open == "") {
            $date_open = null;
        }

        if($date_last_modified == "") {
            $date_last_modified = null;
        }

        $sql = "INSERT INTO se_order (id_space, id_user, no_identification, id_status, date_open, date_last_modified, date_close)
				 VALUES(?,?,?,?,?,?,?)";
        $this->runRequest($sql, array(
            $id_space, $id_user, $no_identification, $id_status, $date_open, $date_last_modified, $date_close
        ));
        return $this->getDatabase()->lastInsertId();
    }

    public function updateEntry($id, $id_space, $id_user, $no_identification, $id_status, $date_open, $date_last_modified = "", $date_close = "") {
        if($date_close == "") {
            $date_close = null;
        }
        if ($date_close==null){
            $id_status = 1;
        }

        if($date_open == "") {
            $date_open = null;
        }

        if($date_last_modified == "") {
            $date_last_modified = null;
        }
        $sql = "UPDATE se_order set id_user=?, no_identification=?, id_status=?, date_open=?, date_last_modified=?, date_close=?
		        where id=? AND id_space=?";
        $this->runRequest($sql, array($id_user, $no_identification, $id_status, $date_open, $date_last_modified, $date_close, $id, $id_space));
    }

    public function entries($id_space, $sortentry = 'id') {

        $sql = "SELECT * from se_order WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($id_space));
        $entries = $req->fetchAll();
        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFUllName($entries[$i]['id_user']);
        }
        return $entries;
    }

    // @bug refers to ec_j_user_responsible
    public function openedForResp($id_space, $id_resp){
        $sql = "select * from se_order where deleted=0 AND id_status=1 AND id_user IN (SELECT id_user FROM ec_j_user_responsible WHERE id_resp=? AND id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($id_resp, $id_space));
        return $req->fetchAll();
    }

    // @bug refers to ec_j_user_responsible
    public function openedForRespPeriod($dateBegin, $dateEnd, $id_resp, $id_space){
        $sql = "SELECT * FROM se_order WHERE id_status=1 "
                . "AND id_user IN (SELECT id_user FROM ec_j_user_responsible WHERE id_resp=? AND id_space=? AND deleted=0) "
                . "AND date_open>=? "
                . "AND date_close<=? "
                . "AND id_space=? AND deleted=0";

        $req = $this->runRequest($sql, array($id_resp, $id_space, $dateBegin, $dateEnd, $id_space));
        return $req->fetchAll();
    }

    public function openedEntries($id_space, $sortentry = 'id') {
        $sql = "select * from se_order where deleted=0 AND id_space=? AND id_status=1 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($id_space));

        $entries = $req->fetchAll();
        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFUllName($entries[$i]['id_user']);
        }
        return $entries;
    }

    public function closedEntries($id_space, $sortentry = 'id') {
        $sql = "select * from se_order where id_space=? AND deleted=0 AND id_status=0 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($id_space));

        $entries = $req->fetchAll();

        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFUllName($entries[$i]['id_user']);
        }
        return $entries;
    }

    public function defaultEntryValues() {

        $entry["id"] = "";
        $entry["id_user"] = "";
        $entry["id_space"] = 0;
        $entry["id_status"] = 1;
        $entry["date_open"] = date("Y-m-d", time());
        $entry["date_last_modified"] = "";
        $entry["date_close"] = "";
        $entry["orders"] = array();
        $entry["no_identification"] = "";
        return $entry;
    }

    public function getEntry($id_space, $id) {
        $sql = "SELECT * from se_order where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        $entry = $req->fetch();

        return $entry;
    }

    public function setEntryCloded($id_space, $id) {
        $sql = "UPDATE se_order set id_status=0, date_close=?
		        where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array(date("Y-m-d", time()), $id, $id_space));
    }

    public function reopenEntry($id_space, $id){
        $sql = "UPDATE se_order set id_status=1, date_close is null
		        where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id, $id_space));
    }

    // @bug refers to ec_j_user_responsible
    public function openedItemsForResp($id_space, $id_resp){

        $userList = " SELECT id_user FROM ec_j_user_responsible WHERE id_resp=? AND id_space=? AND deleted=0 ";
        $orderList = " SELECT id FROM se_order WHERE id_user IN (".$userList.") AND id_status=1 AND id_space=? AND deleted=0";
        $sql = "SELECT * FROM se_order_service WHERE id_order IN (".$orderList.")";
        return $this->runRequest($sql, array($id_resp, $id_space, $id_space))->fetchAll();
    }

    // @bug refers to ec_j_user_responsible
    public function getOrdersOpenedPeriod($id_space, $periodStart, $periodEnd){
        $sql = "SELECT * FROM se_order WHERE deleted=0 AND id_space = ? AND date_open >= ? AND date_open <= ?";
        $req = $this->runRequest($sql, array($id_space, $periodStart, $periodEnd));
        $orders = $req->fetchAll();

        for($i = 0 ; $i < count($orders) ; $i++){
            if ($orders[$i]["id_resp"] == 0){
                $sql = "SELECT id_resp FROM ec_j_user_responsible WHERE id_user=? AND id_space=? AND deleted=0";
                $resp_id = $this->runRequest($sql, array($orders[$i]["id_user"], $id_space))->fetch();
                $orders[$i]["id_resp"] = $resp_id[0];
            }
        }
        return $orders;
    }

    public function getPeriodeServicesBalancesOrders($id_space, $periodStart, $periodEnd){
        $sql = "select * from se_order where deleted=0 AND id_space=? AND date_open>=? OR date_open=?";
        $req = $this->runRequest($sql, array($id_space, $periodStart, $periodEnd));
        $orders = $req->fetchAll();

        //$items = array();
        $modelServices = new SeService();
        $items = $modelServices->getBySpace($id_space);
        //print_r($items);

        $modelClientUser = new ClClientUser();
        $modelClient = new ClClient();
        for ($i = 0; $i < count($orders); $i++) {

            if( !isset($orders[$i]["id_resp"]) || $orders[$i]["id_resp"] == 0 ){
                //echo "id user = " . $orders[$i]["id_user"] . "<br/>";
                $resps = $modelClientUser->getUserClientAccounts($orders[$i]["id_user"], $id_space);
                //echo "coucou 1 <br/>";echo "resps = <br/>";
                //print_r($resps);
                if (count($resps) > 0){

                    $orders[$i]["id_resp"] = $resps[0]["id"];
                }
                else{
                    $orders[$i]["id_resp"] = 0;
                }
            }
            $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_space=? AND deleted=0";
            $itemsSummary = $this->runRequest($sql, array($orders[$i]["id"], $id_space));

            //print_r($itemsSummary);

            $orders[$i]["entries"] = $itemsSummary;
            //print_r($itemsSummary);
            //$items = $this->getProjectServices($projects[$i]["id"]);

            $LABpricingid = $modelClient->getPricingID($id_space, $orders[$i]["id_resp"]);
            $orders[$i]["total"] = $this->calculateOrderTotal($id_space, $itemsSummary, $LABpricingid);
        }

        return array("items" => $items, "orders" => $orders);
    }

    protected function calculateOrderTotal($id_space, $itemsSummary, $LABpricingid){

        $itemPricing = new SePrice();
        $totalHT = 0;
        foreach($itemsSummary as $item){
            if($item["quantity"] > 0){
                $unitaryPrice = $itemPricing->getPrice($id_space, $item["id_service"], $LABpricingid);
                //print_r($unitaryPrice);
                $totalHT += (float) $item["quantity"] * (float) $unitaryPrice;
            }
        }
        return $totalHT;
    }

    // @bug refers to ecunit
    public function getPeriodeBilledServicesBalancesOrders($id_space, $periodStart, $periodEnd){
        // get the projects
        $sql1 = "SELECT * FROM se_order WHERE deleted=0 AND id_space=? AND id_invoice > 0 AND date_open >= ? AND date_open <= ?";
        $req1 = $this->runRequest($sql1, array($id_space, $periodStart, $periodEnd));
        $orders = $req1->fetchAll();

        $items = array();
        $modelUser = new CoreUser();
        $modelUserClient = new ClClientUser();
        $modelUnit = new EcUnit();
        $modelClient = new ClClient();
        for ($i = 0; $i < count($orders); $i++) {

            if( !isset($orders[$i]["id_resp"]) || $orders[$i]["id_resp"] == 0 ){
                $resps = $modelUserClient->getUserClientAccounts($orders[$i]["id_user"], $id_space);
                if (count($resps) > 0){

                    $orders[$i]["id_resp"] = $resps[0]["id"];
                }
                else{
                    $orders[$i]["id_resp"] = 0;
                }
            }
            $sql = "SELECT * FROM se_order_service WHERE id_order=? AND id_space=? AND deleted=0";
            $itemsSummary = $this->runRequest($sql, array($orders[$i]["id"], $id_space));

            $orders[$i]["entries"] = $itemsSummary;

            $id_unit = $modelClient->getInstitution($id_space, $orders[$i]["id_resp"]);
            $LABpricingid = $modelUnit->getBelonging($id_space, $id_unit);
            $orders[$i]["total"] = $this->calculateOrderTotal($id_space, $itemsSummary, $LABpricingid);
        }

        return array("items" => $items, "orders" => $orders);
    }
    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE se_order SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM se_order WHERE id = ? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
