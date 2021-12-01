<?php

require_once 'Framework/Model.php';
require_once 'Framework/Events.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/clients/Model/ClClientUser.php';

/**
 * Class defining the GRR area model
 *
 * @author Sylvain Prigent
 */
class BkCalendarEntry extends Model {

    public function __construct() {
        $this->tableName = "bk_calendar_entry";
    }

    /**
     * Create the calendar entry table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_calendar_entry` (
		`id` int(11) NOT NULL AUTO_INCREMENT,	
		`start_time` int(11) NOT NULL,	
		`end_time` int(11) NOT NULL,	
		`resource_id` int(11) NOT NULL,	
		`booked_by_id` int(11) NOT NULL,	
		`recipient_id` int(11) NOT NULL,	
		`last_update` timestamp NOT NULL,
		`color_type_id` int(11) NOT NULL,						
		`short_description` varchar(100) NOT NULL,	
		`full_description` text NOT NULL,
		`quantities` text NOT NULL,
		`supplementaries` text NOT NULL,	
		`package_id` int(11) NOT NULL DEFAULT 0,
		`responsible_id` int(11) NOT NULL DEFAULT 0,
        `invoice_id` int(11) NOT NULL DEFAULT 0,
        `period_id` int(11) NOT NULL DEFAULT 0,
        `all_day_long` int(1) NOT NULL DEFAULT 0,
        `deleted` int(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        /*
        $this->addColumn('bk_calendar_entry', 'period_id', 'int(11)', 0);
        $this->addColumn('bk_calendar_entry', 'all_day_long', 'int(1)', 0);
        $this->addColumn('bk_calendar_entry', 'deleted', 'int(1)', 0);
        */
    }
    
    public function getEntriesForUserResource($id_space, $id_user, $id_resource){
        
        $sql = "SELECT * FROM bk_calendar_entry WHERE recipient_id=? AND resource_id=? AND id_space=? AND deleted=0 ORDER BY start_time DESC LIMIT 10";
        $data = $this->runRequest($sql, array($id_user, $id_resource, $id_space))->fetchAll();
        for ($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["hourstart"] = date("H:i", $data[$i]["start_time"]);
            $data[$i]["hourend"] = date("H:i", $data[$i]["end_time"]);
            $data[$i]["datetimebegin"] = date("Y-m-d_H-i", $data[$i]["start_time"]);
            $data[$i]["date"] = date("d/m/Y", $data[$i]["start_time"]);
        }
        return $data;
        
    }

    public function getStatsQuantities($id_space, $dateBegin, $dateEnd) {

        $dateBeginArray = explode("-", $dateBegin);
        $beginTime = mktime(0, 0, 0, $dateBeginArray[1], $dateBeginArray[2], $dateBeginArray[0]);
        $dateEndArray = explode("-", $dateEnd);
        $endTime = mktime(0, 0, 0, $dateEndArray[1], $dateEndArray[2], $dateEndArray[0]);

        // get all the quantity types
        $sql = "SELECT * FROM bk_calquantities WHERE deleted=0 AND id_space=? AND id_resource IN (SELECT id FROM re_info WHERE id_space=? AND deleted=0)";
        $quantities = $this->runRequest($sql, array($id_space, $id_space))->fetchAll();
        for ($i = 0; $i < count($quantities); $i++) {
            $count = 0;
            $sql = "SELECT quantities FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time>=? AND start_time<=? AND resource_id IN (SELECT id FROM re_info WHERE id_space=? AND deleted=0)";
            $dd = $this->runRequest($sql, array($id_space, $beginTime, $endTime, $id_space))->fetchAll();
            foreach ($dd as $dddd) {
                $d = $dddd[0];
                $darray = explode(";", $d);
                foreach ($darray as $di) {
                    $diarray = explode("=", $di);  // @bug was using d and di was not used, to be tested
                    if ($diarray[0] == $quantities[$i]["id"]) {
                        $count += $diarray[1];
                    }
                }
            }

            $quantities[$i]["count"] = $count;
        }

        return $quantities;
    }
    
    public function getStatTimeResps($id_space, $dateBegin, $dateEnd){
        
        //dates to time
        $dateBeginArray = explode("-", $dateBegin);
        $dateBeginTime = mktime(0, 0, 0, $dateBeginArray[1], $dateBeginArray[2], $dateBeginArray[0]);
        
        $dateEndArray = explode("-", $dateEnd);
        $dateEndTime = mktime(0, 0, 0, $dateEndArray[1], $dateEndArray[2], $dateEndArray[0]);
        
        
        // get all resources
        $sql1 = "SELECT id, name FROM re_info WHERE id_space=? AND deleted=0";
        $resources = $this->runRequest($sql1, array($id_space))->fetchAll();
        
        // get all responsibles
        $sql2 = "SELECT DISTINCT responsible_id FROM bk_calendar_entry WHERE deleted=0 AND id_space=? AND resource_id IN (SELECT id FROM re_info WHERE id_space=? AND deleted=0) AND start_time>=? AND start_time<=?";
        $resps = $this->runRequest($sql2, array($id_space, $id_space, $dateBeginTime, $dateEndTime))->fetchAll();
        
        
        $data = array();
        $data["resources"] = $resources;
        $data["count"] = array();
        
        foreach($resps as $resp){
            
            $sqlr = "SELECT name from cl_clients WHERE id=? AND deleted=0 AND id_space=?";
            //$sqlr = "SELECT name, firstname FROM core_users WHERE id=?";

            $respinfo = $this->runRequest($sqlr, array($resp[0], $id_space))->fetch();
            if(!$respinfo) {
                $respinfo = ["name" => "unknown"];
            }
            $resourceCount = array();
            foreach( $resources as $resource){
                $sql3 = "SELECT * FROM bk_calendar_entry WHERE deleted=0 AND id_space=? AND responsible_id=? AND resource_id=? AND start_time>=? AND start_time<=?";
                $res = $this->runRequest($sql3, array($id_space, $resp[0], $resource["id"], $dateBeginTime, $dateEndTime))->fetchAll();
                if(!$res) { continue; }

                $time = 0;
                foreach($res as $r){
                    $time += $r["end_time"] - $r["start_time"];
                }
                $resourceCount[] = array( "resource" => $resource["name"], "time" => round($time/3600, 1) );
            }
            
            $data["count"][] = array( "responsible" => $respinfo["name"], "count" => $resourceCount ); 
        }
        return $data;
        
    }

    public function updateNullResponsibles($id_space) {

        $sql = "SELECT * FROM bk_calendar_entry WHERE responsible_id<=1 AND deleted=0 AND id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $modelUserClient = new ClClientUser();
        
        foreach ($data as $d) {
            //$resps = $modelUserClient->getUserAccounts($id_space, $d["recipient_id"]);
            $resps = $modelUserClient->getUserClientAccounts($d["recipient_id"], $id_space);

            if (!empty($resps)) {
                $sql = "UPDATE bk_calendar_entry SET responsible_id=? WHERE id=? AND deleted=0 AND id_space=?";
                $this->runRequest($sql, array($resps[0]["id"], $d["id"], $id_space));
            }
        }
    }

    public function mergeUsers($users) {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "UPDATE bk_calendar_entry SET recipient_id=? WHERE recipient_id=?";
            $this->runRequest($sql, array($users[0], $users[$i]));

            $sql2 = "UPDATE bk_calendar_entry SET booked_by_id=? WHERE booked_by_id=?";
            $this->runRequest($sql2, array($users[0], $users[$i]));

            $sql3 = "UPDATE bk_calendar_entry SET responsible_id=? WHERE responsible_id=?";
            $this->runRequest($sql3, array($users[0], $users[$i]));
        }
    }

    public function getPeriod($id_space, $id) {
        $sql = "SELECT period_id FROM bk_calendar_entry WHERE id=? AND deleted=0 AND id_space=?";
        $tmp = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function setPeriod($id_space, $id, $period_id) {
        $sql = "UPDATE bk_calendar_entry SET period_id=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($period_id, $id, $id_space));
    }

    public function cleanBadResa() {
        $sql = "DELETE FROM bk_calendar_entry WHERE (start_time>end_time) OR start_time=0 OR end_time=0";
        $this->runRequest($sql);
    }

    public function setReservationInvoice($id_space, $reservation_id, $invoice_id) {
        $sql = "UPDATE bk_calendar_entry SET invoice_id=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($invoice_id, $reservation_id, $id_space));
    }

    public function getInvoiceEntries($id_space, $id_invoice) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_invoice, $id_space))->fetchAll();
    }

    public function getResourcesForInvoice($id_space, $id_invoice) {
        $sql = "SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE invoice_id=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_invoice, $id_space))->fetchAll();
    }

    public function getResourcesUsersForInvoice($id_space, $id_resource, $id_invoice) {
        $sql = "SELECT DISTINCT recipient_id FROM bk_calendar_entry WHERE resource_id=? AND invoice_id=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_resource, $id_invoice, $id_space))->fetchAll();
    }

    public function getResourcesUserResaForInvoice($id_space, $id_resource, $id_user, $id_invoice) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE recipient_id=? AND resource_id=? AND invoice_id=? AND deleted=0 AND id_space=?";
        return $this->runRequest($sql, array($id_user, $id_resource, $id_invoice, $id_space))->fetchAll();
    }

    public function getDefault($id_space, $start_time, $end_time, $resource_id, $id_user) {

        $modelAccount = new ClClientUser();
        
        $resps = $modelAccount->getUserClientAccounts($id_user, $id_space);
        $resps_id = $resps ? $resps[0]["id"] : null;

        return array("id" => 0,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "resource_id" => $resource_id,
            "booked_by_id" => 0,
            "recipient_id" => $id_user,
            "last_update" => time(),
            "color_type_id" => 0,
            "short_description" => "",
            "full_description" => "",
            "quantities" => "",
            "supplementaries" => "",
            "package_id" => 0,
            "responsible_id" => $resps_id,
            "invoice_id" => 0,
            "all_day_long" => 0);
    }

    public function setAllDayLong($id_space, $id, $all_day_long) {
        $sql = "UPDATE bk_calendar_entry SET all_day_long=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($all_day_long, $id, $id_space));
    }

    public function setDeleted($id_space, $id, $deleted = 1) {
        $sql = "UPDATE bk_calendar_entry SET deleted=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($deleted, $id, $id_space));
    }

    public function setEntry($id_space, $id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id) {
        $old = null;
        if (!$id) {
            $sql = "INSERT INTO bk_calendar_entry (start_time, end_time, resource_id, booked_by_id, recipient_id, 
                    last_update, color_type_id, short_description, full_description, quantities, 
                    supplementaries, package_id, responsible_id, id_space) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, ?)";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id, $id_space));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = "SELECT * FROM bk_calendar_entry WHERE id=? AND id_space=?";
            $oldRes = $this->runRequest($sql, array($id, $id_space))->fetch();
            $old = ['start_time' => $oldRes['start_time'], 'resource_id' => $oldRes['resource_id'], 'recipient_id' => $oldRes['recipient_id'], 'booked_by_id' => $oldRes['booked_by_id'], 'responsible_id' => $oldRes['responsible_id']];
            $sql = "UPDATE bk_calendar_entry SET start_time=?, end_time=?, resource_id=?, booked_by_id=?, recipient_id=?, 
                    last_update=?, color_type_id=?, short_description=?, full_description=?, quantities=?, 
                    supplementaries=?, package_id=?, responsible_id=? WHERE id=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id, $id, $id_space));
        }
        Events::send(["action" => Events::ACTION_CAL_ENTRY_EDIT, "bk_calendar_entry_old" => $old, "bk_calendar_entry" => ["id" => intval($id), "id_space" => $id_space]]);

        return $id;

    }

    /**
     * Add a calendar entry
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $resource_id
     * @param unknown $booked_by_id
     * @param unknown $recipient_id
     * @param unknown $last_update
     * @param unknown $color_type_id
     * @param unknown $short_description
     * @param unknown $full_description
     * @param number $quantity
     * @return string
     */
    public function addEntry($id_space, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {

        $sql = "insert into bk_calendar_entry(start_time, end_time, resource_id, booked_by_id, recipient_id, 
							last_update, color_type_id, short_description, full_description, quantities, package_id, id_space)"
                . " values(?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
            $last_update, $color_type_id, $short_description, $full_description, $quantity, $package, $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    public function getAllEntries($id_space, $deleted = 0) {
        $sql = "select * from bk_calendar_entry WHERE deleted=? AND id_space=?";
        $req = $this->runRequest($sql, array($deleted, $id_space));
        return $req->fetchAll();
    }

    public function getZeroRespEntries($id_space) {
        $sql = "select * from bk_calendar_entry WHERE responsible_id=0 AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, $id_space);
        return $req->fetchAll();
    }

    public function getUnpricedReservations($id_space, $beginPeriod, $endPeriod, $id_resource, $id_resp) {

        $beginPeriodArray = explode("-", $beginPeriod);
        $searchDate_start = mktime(0, 0, 0, $beginPeriodArray[1], $beginPeriodArray[2], $beginPeriodArray[0]);
        $endPeriodArray = explode("-", $endPeriod);
        $searchDate_end = mktime(23, 59, 59, $endPeriodArray[1], $endPeriodArray[2], $endPeriodArray[0]);


        $q = array('start' => $searchDate_start, 'end' => $searchDate_end, 'resp' => $id_resp, 'resource' => $id_resource, 'id_space' => $id_space);
        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				start_time >=:start
                AND start_time < :end
                AND resource_id=:resource
                AND responsible_id=:resp
                AND invoice_id=0
                AND deleted=0
                AND id_space=:id_space 
				ORDER BY id';
        $req = $this->runRequest($sql, $q);
        return $req->fetchAll();
    }

    /**
     * Add a calendar entry if not exists  
     * @param unknown $id
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $resource_id
     * @param unknown $booked_by_id
     * @param unknown $recipient_id
     * @param unknown $last_update
     * @param unknown $color_type_id
     * @param unknown $short_description
     * @param unknown $full_description
     * @param number $quantity
     */
    public function setEntryOld($id_space, $id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {

        if (!$this->isCalEntry($id_space, $id)) {
            return $this->addEntry($id_space, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity, $package);
        }
    }

    /**
     * Check if an entry exists
     * @param unknown $id
     * @return boolean
     */
    public function isCalEntry($id_space, $id) {
        $sql = "select * from bk_calendar_entry where id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return ($req->rowCount() == 1);
    }

    /**
     * Set the repeat ID for a series booking
     * @param unknown $id
     * @param unknown $repeat_id
     */
    public function setRepeatID($id_space, $id, $repeat_id) {
        $sql = "update bk_calendar_entry set repeat_id=?
									  where id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($repeat_id, $id, $id_space));
    }

    /**
     * Get the informations of an entry
     * @param unknown $id
     * @return mixed
     */
    public function getEntry($id_space, $id) {
        $sql = "select * from bk_calendar_entry where id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return $req->fetch();
    }

    public function updateEntry($id_space, $id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {
        $sql = "update bk_calendar_entry set start_time=?, end_time=?, resource_id=?, booked_by_id=?, recipient_id=?, 
							last_update=?, color_type_id=?, short_description=?, full_description=?, quantity=?, package_id=?
									  where id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
            $last_update, $color_type_id, $short_description, $full_description, $quantity, $package, $id, $id_space));
    }

    public function setEntryResponsible($id_space, $id, $responsibleId) {
        $sql = "update bk_calendar_entry set responsible_id=? where id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($responsibleId, $id, $id_space));
    }

    /**
     * Get all the entries for a given day
     * @param string $curentDate
     * @return multitype:
     */
    public function getEntriesForDay($id_space, $curentDate) {
        $dateArray = explode("-", $curentDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);

        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'id_space' => $id_space);
        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				(start_time <=:end AND end_time >= :start) 
                AND deleted=0
                AND id_space=:id_space
				ORDER BY start_time';
        $req = $this->runRequest($sql, $q);
        return $req->fetchAll(); // Liste des bénéficiaire dans la période séléctionée
    }

    /**
     * Get all the entries for a given period and a given resource
     * @param $dateBegin Beginning of the periode in linux time
     * @param $dateEnd End of the periode in linux time
     * 
     */
    public function getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $resource_id) {
        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'res' => $resource_id, 'id_space' => $id_space);

        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				(start_time <=:end AND end_time >= :start)
                AND resource_id = :res
                AND deleted=0
                AND id_space=:id_space
				ORDER BY start_time';

        $req = $this->runRequest($sql, $q);
        $data = $req->fetchAll(); // Liste des bénéficiaire dans la période séléctionée

        $modelUser = new CoreUser();
        $modelColor = new BkColorCode();
        for ($i = 0; $i < count($data); $i++) {
            $rid = $data[$i]["recipient_id"];
            if ($rid > 0) {
                $userInfo = $modelUser->userAllInfo($rid);
                $data[$i]["recipient_fullname"] = $userInfo["name"] . " " . $userInfo["firstname"];
                $data[$i]["phone"] = "";
                if (isset($userInfo["phone"])) {
                    $data[$i]["phone"] = $userInfo["phone"];
                }
                $data[$i]["color_bg"] = $modelColor->getColorCodeValue($id_space, $data[$i]["color_type_id"]);
                $data[$i]["color_text"] = $modelColor->getColorCodeText($id_space ,$data[$i]["color_type_id"]);
            } else {
                $data[$i]["recipient_fullname"] = "";
                $data[$i]["phone"] = "";
                $data[$i]["color_bg"] = $modelColor->getColorCodeValue($id_space, $data[$i]["color_type_id"]);
                $data[$i]["color_text"] = $modelColor->getColorCodeText($id_space, $data[$i]["color_type_id"]);
            }
        }

        return $data;
    }

    /**
     * Get entries for a given period and a given area
     * @param unknown $dateBegin
     * @param unknown $dateEnd
     * @param unknown $areaId
     * @return multitype:
     */
    public function getEntriesForPeriodeAndArea($id_space, $dateBegin, $dateEnd, $areaId) {

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($id_space, $areaId);

        $data = array();
        foreach ($resources as $resource) {
            $id = $resource["id"];
            $dataInter = $this->getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $id);
            $data = array_merge($data, $dataInter);
        }

        return $data;
    }

    public function isConflictP($id_space, $start_time, $end_time, $resource_id, $id_period) {
        $sql = "SELECT id, period_id FROM bk_calendar_entry WHERE
			(
                (start_time <=:start AND end_time > :start AND end_time <= :end) OR
                (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                (start_time <=:start AND end_time >= :end) 
            ) 
            AND deleted=0
            AND id_space = :id_space
			AND resource_id = :res;";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => $resource_id, 'id_space' => $id_space);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($id_period > 0 && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $period_id = $tmp['period_id'];
                if ($period_id == $id_period) {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function hasTooManyReservations($id_space, $start_time, $id_user, $id_resource, $reservation_id, $bookingQuota) {

        $year = date('Y', $start_time);
        $month = date('m', $start_time);
        $day = date('d', $start_time);

        $startDayTime = mktime(0, 0, 0, $month, $day, $year);
        $endDayTime = mktime(23, 59, 59, $month, $day, $year);

        $sql = "SELECT id FROM bk_calendar_entry WHERE "
                . "start_time >= ? AND start_time<=? "
                . "AND deleted=0 "
                . "AND resource_id=? "
                . "AND id_space=? "
                . "AND booked_by_id=? ";

        if ($reservation_id != "") {
            $sql .= "AND id!=?";
        }
        $req = $this->runRequest($sql, array($startDayTime, $endDayTime, $id_resource, $id_space, $id_user, $reservation_id));

        return ($req->rowCount() >= $bookingQuota);
    }

    /**
     * Check if a new entry is in conflic with an existing entries
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $resource_id
     * @param string $reservation_id
     * @return boolean
     */
    public function isConflict($id_space, $start_time, $end_time, $resource_id, $reservation_id = "") {
        $sql = "SELECT id FROM bk_calendar_entry WHERE
			  ((start_time <=:start AND end_time > :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                           (start_time <=:start AND end_time >= :end) 
                           ) 
                           AND deleted=0
                           AND id_space = :id_space
			AND resource_id = :res;";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => $resource_id, 'id_space' => $id_space);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($reservation_id != "" && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $id = $tmp[0];
                if ($id == $reservation_id) {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a new entry is in conflic with an existing entries
     * @deprecated
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $resource_id
     * @param string $reservation_id
     * @return boolean
     */
    public function isConflictOld($start_time, $end_time, $resource_id, $reservation_id = "") {
        $sql = "SELECT id FROM bk_calendar_entry WHERE
			  ((start_time >=:start AND start_time < :end) OR	
			  (end_time >:start AND end_time <= :end)) 
                          AND deleted=0
			AND resource_id = :res;";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => $resource_id);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($reservation_id != "" && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $id = $tmp[0];
                if ($id == $reservation_id) {
                    return false;
                } else {
                    return true;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove an entry from it ID
     * @param unknown $id
     */
    public function removeEntry($id_space, $id) {
        $sql = "UPDATE bk_calendar_entry SET deleted=?,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array(1, $id, $id_space));
        Events::send(["action" => Events::ACTION_CAL_ENTRY_REMOVE, "bk_calendar_entry" => ["id" => intval($id), "id_space" => $id_space]]);
    }

    /**
     * Removes all the entries of a series
     * @param unknown $series_id
     */
    public function removeEntriesFromSeriesID($id_space, $series_id) {
        $sql = "UPDATE bk_calendar_entry SET deleted=?,deleted_at=NOW() WHERE repeat_id=? AND id_space=?";
        $this->runRequest($sql, array(1, $series_id, $id_space));
    }

    /**
     * Select entries having a given description
     * @param unknown $desciption
     * @return multitype:
     */
    public function selectEntriesByDescription($id_space, $desciption) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE short_description=? AND id_space=? AND deleted=0 ORDER BY end_time";
        $req = $this->runRequest($sql, array($desciption, $id_space));
        return $req->fetchAll();
    }

    /**
     * Check if a responsible has entries in a given period
     * @param int $resp_id
     * @param string $startdate
     * @param string $enddate
     * @return boolean
     */
    public function hasResponsibleEntry($id_space, $resp_id, $startdate, $enddate) {

        $beginPeriodArray = explode("-", $startdate);
        $searchDate_start = mktime(0, 0, 0, $beginPeriodArray[1], $beginPeriodArray[2], $beginPeriodArray[0]);
        $endPeriodArray = explode("-", $enddate);
        $searchDate_end = mktime(23, 59, 59, $endPeriodArray[1], $endPeriodArray[2], $endPeriodArray[0]);


        $q = array('start' => $searchDate_start, 'end' => $searchDate_end, 'resp' => $resp_id, 'space' => $id_space);
        $sql = 'SELECT id FROM bk_calendar_entry WHERE
				(start_time >=:start AND start_time <= :end) AND (responsible_id = :resp)
                                AND resource_id IN (SELECT id FROM re_info WHERE id_space= :space)
                                AND deleted=0
                                AND id_space=:space
                                AND invoice_id=0';
        $req = $this->runRequest($sql, $q);

        if ($req->rowCount() > 0) {
            return 1;
        }
        return 0;
    }

    /**
     * Check if there are some entries for a unit in a given period
     * @deprecated
     * @bug refer to id_unit in core_users, does not exists!!
     * @param unknown $unit_id
     * @param unknown $startdate
     * @param unknown $enddate
     * @return boolean
     */
    public function hasUnitEntry($id_space, $unit_id, $startdate, $enddate) {
        $q = array('start' => $startdate, 'end' => $enddate, 'id_space' => $id_space);
        $sql = 'SELECT DISTINCT recipient_id, id FROM bk_calendar_entry WHERE
				(start_time >=:start AND start_time <= :end)
                AND deleted=0
                AND id_space=:id_space
                                ';
        $req = $this->runRequest($sql, $q);
        $recs = $req->fetchAll();

        foreach ($recs as $rec) {
            $sql = "select id_unit from core_users where id=?";
            $req = $this->runRequest($sql, array($rec['recipient_id']));
            $resp_id_req = $req->fetch();
            $resp_id_req = $resp_id_req[0];
            //echo "resp_id_req = " . $resp_id_req . "<br />";
            if ($resp_id_req == $unit_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all the entries of a given user
     * @param unknown $user_id
     * @return multitype:
     */
    public function getUserBooking($id_space, $user_id) {
        $sql = "select * from bk_calendar_entry where recipient_id=? AND deleted=0 AND id_space=? order by end_time DESC;";
        $req = $this->runRequest($sql, array($user_id, $id_space));
        return $req->fetchAll();
    }

    /**
     * Get all the entries for a given user and a given resource
     * @param unknown $user_id
     * @param unknown $resource_id
     * @return multitype:
     */
    public function getUserBookingResource($id_space, $user_id, $resource_id) {
        $sql = "select * from bk_calendar_entry where recipient_id=? and resource_id=? AND deleted=0 AND id_space=? order by end_time DESC;";
        $req = $this->runRequest($sql, array($user_id, $resource_id, $id_space));
        return $req->fetchAll();
    }

    /**
     * Get the emails address of the users who booked a resource and still have a role in space
     * @param unknown $resource_id
     * @return multitype:
     */
    public function getEmailsBookerResource($id_space, $resource_id) {

        $sql = "SELECT DISTINCT user.email AS email 
                FROM core_users AS user
                INNER JOIN bk_calendar_entry AS bk_calendar_entry ON user.id = bk_calendar_entry.recipient_id
                INNER JOIN core_j_spaces_user AS core_j_spaces_user ON user.id = core_j_spaces_user.id_user
                WHERE bk_calendar_entry.resource_id=?
                AND core_j_spaces_user.id_space=?
                AND bk_calendar_entry.deleted=0
                AND bk_calendar_entry.id_space=?
                AND user.is_active = 1 
                ;";
        $req = $this->runRequest($sql, array($resource_id, $id_space, $id_space));
        return $req->fetchAll();
    }

    /**
     * Get the emails address of the users who booked resorces of a given area
     * @param unknown $area_id
     * @return multitype:
     */
    public function getEmailsBookerArea($id_space, $area_id) {

        $sql = "SELECT DISTINCT user.email AS email
                FROM core_users AS user
                INNER JOIN bk_calendar_entry AS bk_calendar_entry ON user.id = bk_calendar_entry.recipient_id
                INNER JOIN core_j_spaces_user AS core_j_spaces_user ON user.id = core_j_spaces_user.id_user
                WHERE bk_calendar_entry.resource_id IN (SELECT id FROM re_info WHERE id_area=?)
                AND core_j_spaces_user.id_space=?
                AND user.is_active = 1  
                AND bk_calendar_entry.deleted=0
                AND bk_calendar_entry.id_space=?
                ;";
        $req = $this->runRequest($sql, array($area_id, $id_space, $id_space));
        return $req->fetchAll();
    }


    /**
     * Get user future bookings
     */
    public function getUserFutureBookings($id_space, $id_user, $id_resource=null): array{
        $now = time();
        $q = array('today' => $now, 'id_user' => $id_user);
        $sql = 'SELECT bk_calendar_entry.*, spaces.name as space, resources.name as resource FROM bk_calendar_entry';
        $sql .= ' INNER JOIN core_spaces AS spaces ON spaces.id = bk_calendar_entry.id_space';
        $sql .= ' INNER JOIN re_info AS resources ON resources.id = bk_calendar_entry.resource_id';
        $sql .= ' WHERE start_time >= :today AND recipient_id=:id_user';
        if($id_space && intval($id_space) > 0) {
            $q['id_space'] = $id_space;
            $sql .= ' AND bk_calendar_entry.id_space = :id_space';
        }
        if($id_resource && intval($id_resource) > 0) {
            $q['res'] = $id_resource;
            $sql .= ' AND bk_calendar_entry.resource_id = :res';
        }
        $sql .= ' ORDER BY bk_calendar_entry.start_time ASC LIMIT 20';
        $req = $this->runRequest($sql, $q);
        if($req->rowCount() > 0) {
            return $req->fetchAll();
        }
        return [];
    }

}
