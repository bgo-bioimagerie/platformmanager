<?php

require_once 'Framework/Model.php';
require_once 'Framework/Events.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkNightWE.php';

/**
 * Class defining the booking entries
 *
 * @author Sylvain Prigent
 */
class BkCalendarEntry extends Model {

    public static $REASON_BOOKING = 0;
    public static $REASON_HOLIDAY = 1;
    public static $REASON_MAINTENANCE = 2;

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
        `reason` int NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn('bk_calendar_entry', 'period_id', 'int(11)', 0);
        $this->addColumn('bk_calendar_entry', 'all_day_long', 'int(1)', 0);
        $this->addColumn('bk_calendar_entry', 'deleted', 'int(1)', 0);
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
        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }
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
        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }
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
                $resourceCount[] = array( "resource" => $resource["name"], "time" => round($time/3600, 2) );
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
            "reason" => self::$REASON_BOOKING,
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

    public function setEntry($id_space, $id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id, $reason=0) {
        $old = null;
        if (!$id) {
            $sql = "INSERT INTO bk_calendar_entry (start_time, end_time, resource_id, booked_by_id, recipient_id, 
                    last_update, color_type_id, short_description, full_description, quantities, 
                    supplementaries, package_id, responsible_id, id_space, reason) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id, $id_space, $reason));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = "SELECT * FROM bk_calendar_entry WHERE id=? AND id_space=?";
            $oldRes = $this->runRequest($sql, array($id, $id_space))->fetch();
            $old = ['start_time' => $oldRes['start_time'], 'resource_id' => $oldRes['resource_id'], 'recipient_id' => $oldRes['recipient_id'], 'booked_by_id' => $oldRes['booked_by_id'], 'responsible_id' => $oldRes['responsible_id']];
            $sql = "UPDATE bk_calendar_entry SET start_time=?, end_time=?, resource_id=?, booked_by_id=?, recipient_id=?, 
                    last_update=?, color_type_id=?, short_description=?, full_description=?, quantities=?, 
                    supplementaries=?, package_id=?, responsible_id=?, reason=? WHERE id=? AND deleted=0 AND id_space=?";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id, $reason, $id, $id_space));
        }
        Events::send(["action" => Events::ACTION_CAL_ENTRY_EDIT, "bk_calendar_entry_old" => $old, "bk_calendar_entry" => ["id" => intval($id), "id_space" => $id_space]]);

        return $id;

    }

    /**
     * Add a calendar entry
     * @param int $start_time
     * @param int $end_time
     * @param int $resource_id
     * @param int $booked_by_id
     * @param int $recipient_id
     * @param int $last_update
     * @param int $color_type_id
     * @param string $short_description
     * @param string $full_description
     * @param number $quantity
     * @param int package
     * @return string
     */
    public function addEntry($id_space, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0, $reason=0) {

        $sql = "insert into bk_calendar_entry(start_time, end_time, resource_id, booked_by_id, recipient_id, 
							last_update, color_type_id, short_description, full_description, quantities, package_id, id_space,supplementaries, reason)"
                . " values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
            $last_update, $color_type_id, $short_description, $full_description, $quantity, $package, $id_space, '', $reason));
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
        if($beginPeriod == "") {
            throw new PfmParamException("invalid start date");
        }
        if($endPeriod == "") {
            throw new PfmParamException("invalid end date");
        }
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
     * @param int $id_space
     * @param int $id
     * @param int $start_time
     * @param int $end_time
     * @param int $resource_id
     * @param int $booked_by_id
     * @param int $recipient_id
     * @param int $last_update
     * @param int $color_type_id
     * @param string $short_description
     * @param string $full_description
     * @param number $quantity
     * @param int package
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
        if($curentDate == "") {
            throw new PfmParamException("invalid date");
        }
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
    public function getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $resource_id, $id_user='') {
        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'res' => $resource_id, 'id_space' => $id_space);

        $sql = 'SELECT bk_calendar_entry.* , bk_color_codes.color as color_bg, bk_color_codes.text as color_text, core_users.phone as phone, core_users.name as lastname, core_users.firstname as firstname FROM bk_calendar_entry
                LEFT JOIN bk_color_codes ON bk_color_codes.id=bk_calendar_entry.color_type_id
                INNER JOIN core_users ON core_users.id=bk_calendar_entry.recipient_id
                WHERE
				(bk_calendar_entry.start_time <=:end AND bk_calendar_entry.end_time >= :start)
                AND bk_calendar_entry.resource_id = :res
                AND bk_calendar_entry.deleted=0
                AND bk_calendar_entry.id_space=:id_space';
        if($id_user) {
            $sql .= ' AND bk_calendar_entry.recipient_id=:id_user';
            $q['id_user'] = $id_user;
        }
		$sql .=	 ' ORDER BY bk_calendar_entry.start_time';

        $req = $this->runRequest($sql, $q);
        $data = $req->fetchAll(); // Liste des bénéficiaire dans la période séléctionée

        for ($i = 0; $i < count($data); $i++) {
            $rid = $data[$i]["recipient_id"];
            if ($rid > 0) {
                $data[$i]["recipient_fullname"] = $data[$i]["lastname"] . " " . $data[$i]["firstname"];
            } else {
                $data[$i]["recipient_fullname"] = "";
                $data[$i]["phone"] = "";
            }
            if(!$data[$i]["color_bg"]) {
                $data[$i]["color_bg"] = "#aaaaaa";
            }
            if(!$data[$i]["color_text"]) {
                $data[$i]["color_text"] = "#000000";
            }
        }

        return $data;
    }

    /**
     * Get all the entries for a given period and a given list of resource
     * @param $dateBegin Beginning of the periode in linux time
     * @param $dateEnd End of the periode in linux time
     * 
     */
    public function getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, array $resource_ids, $id_user='') {
        if(empty($resource_ids)) {
            return [];
        }
        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'id_space' => $id_space);

        $sql = 'SELECT bk_calendar_entry.*, bk_color_codes.color as color_bg, bk_color_codes.text as color_text, core_users.phone as phone, core_users.name as lastname, core_users.firstname as firstname FROM bk_calendar_entry
            LEFT JOIN bk_color_codes ON bk_color_codes.id=bk_calendar_entry.color_type_id
            INNER JOIN core_users ON core_users.id=bk_calendar_entry.recipient_id
            WHERE
				(bk_calendar_entry.start_time <=:end AND bk_calendar_entry.end_time >= :start)
                AND bk_calendar_entry.resource_id IN ('.implode(',', $resource_ids).')
                AND bk_calendar_entry.deleted=0
                AND bk_calendar_entry.id_space=:id_space';
        if ($id_user) {
            $sql .= ' AND bk_calendar_entry.recipient_id=:id_user';
            $q['id_user'] = $id_user;
        }
		$sql .= ' ORDER BY bk_calendar_entry.start_time';

        $req = $this->runRequest($sql, $q);
        $data = $req->fetchAll(); // Liste des bénéficiaire dans la période séléctionée
        for ($i = 0; $i < count($data); $i++) {
            $rid = $data[$i]["recipient_id"];
            if ($rid > 0) {
                $data[$i]["recipient_fullname"] = $data[$i]["lastname"] . " " . $data[$i]["firstname"];
            } else {
                $data[$i]["recipient_fullname"] = "";
                $data[$i]["phone"] = "";
            }
            if(!$data[$i]["color_bg"]) {
                $data[$i]["color_bg"] = "#aaaaaa";
            }
            if(!$data[$i]["color_text"]) {
                $data[$i]["color_text"] = "#000000";
            }
        }

        return $data;
    }

    /**
     * Get entries for a given period and a given area
     * @param int $dateBegin
     * @param int $dateEnd
     * @param int $areaId
     * @return multitype:
     */
    public function getEntriesForPeriodeAndArea($id_space, $dateBegin, $dateEnd, $areaId, $id_user='') {

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($id_space, $areaId);

        $rids = [];
        foreach($resources as $r) {
            $rids[] = $r['id'];
        }
       return $this->getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, $rids, $id_user);
    }

    public function isConflictPeriod($id_space, $start_time, $end_time, array $resources_id, $id_period) {
        $sql = "SELECT id, period_id FROM bk_calendar_entry WHERE
			(
                (start_time <=:start AND end_time > :start AND end_time <= :end) OR
                (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                (start_time <=:start AND end_time >= :end) 
            ) 
            AND deleted=0
            AND id_space = :id_space
			AND resource_id in (:res);";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => implode(',', $resources_id), 'id_space' => $id_space);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($id_period > 0 && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $period_id = $tmp['period_id'];
                return ($period_id != $id_period);
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
     * @param int $start_time
     * @param int $end_time
     * @param array $resources_id
     * @param string $reservation_id
     * @return boolean
     */
    public function isConflict($id_space, $start_time, $end_time, array $resources_id, $reservation_id = "") {
        $sql = "SELECT id FROM bk_calendar_entry WHERE
			  ((start_time <=:start AND end_time > :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                           (start_time <=:start AND end_time >= :end) 
                           ) 
                           AND deleted=0
                           AND id_space = :id_space
			AND resource_id in (:res);";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => implode(',', $resources_id), 'id_space' => $id_space);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($reservation_id != "" && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $id = $tmp[0];
                return ($id != $reservation_id);
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
        if($startdate == "") {
            throw new PfmParamException("invalid start date");
        }
        if($enddate == "") {
            throw new PfmParamException("invalid end date");
        }
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
            if ($resp_id_req == $unit_id) {
                return true;
            }
        }
        return false;
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function countForSpace($id_space) {
        $sql = "SELECT count(*) as total FROM bk_calendar_entry WHERE id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space));
        $total = $req->fetch();
        return $total['total'];
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
     * Get all the entries of a given user within period
     * @param unknown $user_id
     * @return multitype:
     */
    public function getUserPeriodBooking($id_space, $id_user, $fromTS, $toTS) {
        $q = array('id_space' => $id_space, 'id_user' => $id_user);
        $sql = "SELECT bk_calendar_entry.*, resources.name as resource_name FROM bk_calendar_entry ";
        $sql .= ' INNER JOIN re_info AS resources ON resources.id = bk_calendar_entry.resource_id';
        $sql .= " WHERE bk_calendar_entry.recipient_id=:id_user AND bk_calendar_entry.deleted=0 AND bk_calendar_entry.id_space=:id_space";
        if($fromTS) {
            $q['start'] = $fromTS;
            $q['end'] = $toTS;
            $sql .= " AND ((bk_calendar_entry.start_time <=:start AND bk_calendar_entry.end_time > :start AND bk_calendar_entry.end_time <= :end) OR
            (bk_calendar_entry.start_time >=:start AND bk_calendar_entry.start_time <=:end AND bk_calendar_entry.end_time >= :start AND bk_calendar_entry.end_time <= :end) OR
            (bk_calendar_entry.start_time >=:start AND bk_calendar_entry.start_time < :end AND bk_calendar_entry.end_time >= :end) OR 
            (bk_calendar_entry.start_time <=:start AND bk_calendar_entry.end_time >= :end)) ";
        }
        $sql .= " order by bk_calendar_entry.end_time ASC;";
        $req = $this->runRequest($sql, $q);
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
     * @param int $resource_id
     * @param int $ts  select reservations after timestamp
     * @return multitype:
     */
    public function getEmailsBookerResource($id_space, $resource_id, $ts=0) {

        $sql = "SELECT DISTINCT user.email AS email 
                FROM core_users AS user
                INNER JOIN bk_calendar_entry AS bk_calendar_entry ON user.id = bk_calendar_entry.recipient_id
                INNER JOIN core_j_spaces_user AS core_j_spaces_user ON user.id = core_j_spaces_user.id_user
                WHERE bk_calendar_entry.resource_id=?
                AND core_j_spaces_user.id_space=?
                AND bk_calendar_entry.deleted=0
                AND bk_calendar_entry.id_space=?
                AND user.is_active = 1
                AND core_j_spaces_user.status>?
                AND bk_calendar_entry.start_time > ?;";
        $params = [$resource_id, $id_space, $id_space, CoreSpace::$VISITOR, $ts];
        $req = $this->runRequest($sql, $params);
        return $req->fetchAll();
    }

    /**
     * Get the emails address of the users who booked resorces of a given area
     * @param int $area_id
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
                AND core_j_spaces_user.status>?
                ;";
        $req = $this->runRequest($sql, array($area_id, $id_space, $id_space, CoreSpace::$VISITOR));
        return $req->fetchAll();
    }


    /**
     * Get user future bookings
     */
    public function getUserFutureBookings($id_space, $id_user, $id_resource=null): ?array{
        $now = time();
        $q = array('today' => $now, 'id_user' => $id_user);
        $sql = 'SELECT bk_calendar_entry.*, spaces.name as space, resources.name as resource FROM bk_calendar_entry';
        $sql .= ' INNER JOIN core_spaces AS spaces ON spaces.id = bk_calendar_entry.id_space';
        $sql .= ' INNER JOIN re_info AS resources ON resources.id = bk_calendar_entry.resource_id';
        $sql .= ' WHERE bk_calendar_entry.start_time >= :today AND bk_calendar_entry.recipient_id=:id_user AND bk_calendar_entry.deleted=0';
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

    /**
     * Get bookings after $from timestamp, defaults to now
     */
    public function journal(int $id_space, int $id_user, int $max=100, int $from=null) {
        if($from==null) {
            $from = time();
        }
        $sql = 'SELECT bk_calendar_entry.*, resources.name as resource FROM bk_calendar_entry';
        $sql .= ' INNER JOIN core_spaces AS spaces ON spaces.id = bk_calendar_entry.id_space';
        $sql .= ' INNER JOIN re_info AS resources ON resources.id = bk_calendar_entry.resource_id';
        $sql .= ' WHERE bk_calendar_entry.id_space=:id_space AND bk_calendar_entry.start_time >= :today AND bk_calendar_entry.recipient_id=:id_user AND bk_calendar_entry.deleted=0';
        $sql .= ' ORDER BY bk_calendar_entry.start_time DESC LIMIT '.$max;
        $q = array('today' => $from, 'id_user' => $id_user, 'id_space' => $id_space);
        $res = $this->runRequest($sql, $q);
        return $res->fetchAll();
    }

    public function blockedEntries($id_space) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE reason>0 AND id_space=? AND deleted=0 ORDER BY start_time DESC";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function computeDuration($id_space, $booking) {
        $modelResource = new ResourceInfo();
        $modelScheduling = new BkScheduling();
        $id_resource = $booking['resource_id'];
        $id_client = $booking['responsible_id'];
        $start_time = $booking['start_time'];
        $end_time = $booking['end_time'];
        $bkScheduling = $modelScheduling->getByReArea($id_space ,$modelResource->getAreaID($id_space, $id_resource));
        $day_begin = $bkScheduling['day_begin'];
        $day_end = $bkScheduling['day_end'];

        $modelClient = new ClCLient();
        $LABpricingid = $modelClient->getPricingID($id_space, $id_client);
        $pricingModel = new BkNightWE();
        $pricingInfo = $pricingModel->getPricing($LABpricingid, $id_space);
        if(!$pricingInfo) {
            throw new PfmException('no pricing found for client '.$id_client);
        }

        $night_begin = $pricingInfo['night_start'];
        $night_end = $pricingInfo['night_end'];
        $we_array = explode(",", $pricingInfo['choice_we']);
        $night_rate = $pricingInfo['tarif_night'] == 1 ? true : false;
        $we_rate = $pricingInfo['tarif_we'] == 1 ? true : false;
        
        $searchDate_start = $start_time;
        $searchDate_end = $end_time;

        // $booking_time_scale = 2;
        // $resa_block_size = 3600;
        $booking_time_scale = 1;
        $resa_block_size = 60;
        switch ($booking_time_scale) {
            case '1':
                $gap = $resa_block_size;
                break;
            case '2':
                $gap = 3600;
                break;
            case '3':
                $gap = 3600 * 24;
                break;
            default:
                $gap = 3600;
                break;
        }

        $gaps = [];
        $gapDuration = 0;
        $timeStep = $searchDate_start;
        $kind = null;

        while ($timeStep < $searchDate_end) {
            $is_open = true; // is open ? (is_monday etc...)
            $d = strtolower(date('l', $timeStep));
            if(!array_key_exists('is_'.$d, $bkScheduling) || !$bkScheduling['is_'.$d]) {
                $is_open = false;
            }
            if(date('G', $timeStep) >= $day_end || date('G', $timeStep) < $day_begin || !$is_open) { // after day end continue till open
                if($kind && $kind!="closed") {
                    $gaps[] = ['kind' => $kind, 'start' => $searchDate_start, 'end' => $timeStep, 'duration' => $gapDuration];
                    $gapDuration = 0;
                    $searchDate_start = $timeStep;
                } 
                    $gapDuration += $gap;
                
                $kind = 'closed';
            } else if($we_rate && $we_array[date('N', $timeStep)-1] == 1) {
                // weekend
                if($kind && $kind != "we") {
                    $gaps[] = ['kind' => $kind, 'start' => $searchDate_start, 'end' => $timeStep, 'duration' => $gapDuration];
                    $gapDuration = 0;
                    $searchDate_start = $timeStep;
                } 
                    $gapDuration += $gap;
                
                $kind = 'we';
            } else if($night_rate && (date('G', $timeStep) < $night_end || date('G', $timeStep) >= $night_begin)) {
                if($kind && $kind != "night") {
                    $gaps[] = ['kind' => $kind, 'start' => $searchDate_start, 'end' => $timeStep, 'duration' => $gapDuration];
                    $gapDuration = 0;
                    $searchDate_start = $timeStep;
                } 
                    $gapDuration += $gap;
                
                $kind = 'night';
            } else {
                if($kind && $kind != 'day') {
                    $gaps[] = ['kind' => $kind, 'start' => $searchDate_start, 'end' => $timeStep, 'duration' => $gapDuration];
                    $gapDuration = 0;
                    $searchDate_start = $timeStep;
                }
                    $gapDuration += $gap;
                
                $kind = 'day';
            }

            $timeStep += $gap;
        }
        if($gapDuration > 0) {
            $gaps[] = ['kind' => $kind, 'start' => $searchDate_start, 'end' => $timeStep, 'duration' => $gapDuration];
        }

        $total_duration = 0;
        $nb_day = 0;
        $nb_night = 0;
        $nb_we = 0;
        $nb_closed = 0;
        foreach ($gaps as $gap) {
            if($gap['kind'] == 'closed') {
                $nb_closed += $gap['duration'];
                continue;
            }
            $total_duration += $gap['duration'];
            switch ($gap['kind']) {
                case 'day':
                    $nb_day += $gap['duration'];
                    break;
                case 'night':
                    $nb_night += $gap['duration'];
                    break;
                case 'we':
                    $nb_we += $gap['duration'];
                    break;
                default:
                    Configuration::getLogger()->error('[compute] unknown kind', ['kind' => $gap['kind']]);
            }
        }

        $nb_hours_closed = round($nb_closed / 3600, 2);
        $nb_hours_day = round($nb_day / 3600, 2);
        $nb_hours_night = round($nb_night / 3600, 2);
        $nb_hours_we = round($nb_we / 3600, 2);
        $totalHours = $nb_hours_day + $nb_hours_night + $nb_hours_we;
        $ratio_bookings_day = 0;
        $ratio_bookings_night = 0;
        $ratio_bookings_we = 0;
        if($totalHours > 0) {
            $ratio_bookings_day = round($nb_hours_day / $totalHours, 2);
            $ratio_bookings_night = round($nb_hours_night / $totalHours, 2);
            $ratio_bookings_we = round($nb_hours_we / $totalHours, 2);
        }

        $result = [
            'total' => $total_duration,
            'steps' => $gaps,
            'hours' => [
                'nb_hours_closed' => $nb_hours_closed,
                'nb_hours_day' => $nb_hours_day,
                'nb_hours_night' => $nb_hours_night,
                'nb_hours_we' => $nb_hours_we,
                'ratio_bookings_day' => $ratio_bookings_day,
                'ratio_bookings_night' => $ratio_bookings_night,
                'ratio_bookings_we' => $ratio_bookings_we
            ]
        ];
        Configuration::getLogger()->debug('[booking] compute_duration', $result);
        return $result;
    }

    function lastUser($id_space, $id){
        $sql = 'SELECT UNIX_TIMESTAMP(max(updated_at)) as last_update, UNIX_TIMESTAMP(max(deleted_at)) as last_delete, max(start_time) as last_start FROM bk_calendar_entry WHERE id_space=? AND recipient_id=?';
        $res = $this->runRequest($sql, [$id_space, $id]);
        return $res->rowCount() > 0 ? $res->fetch() : ['last_update' => 0, 'last_delete' => 0, 'last_start' => 0];
    }

    function lastUserPeriod($id_space, $id, $from, $to){
        if(intval($from) == 0) {
            return $this->lastUser($id_space, $id);
        }
        $sql = 'SELECT UNIX_TIMESTAMP(max(updated_at)) as last_update, UNIX_TIMESTAMP(max(deleted_at)) as last_delete, max(start_time) as last_start FROM bk_calendar_entry WHERE id_space=? AND recipient_id=? AND start_time>=? AND start_time<=?';
        $res = $this->runRequest($sql, [$id_space, $id, $from, $to]);
        return $res->rowCount() > 0 ? $res->fetch() : ['last_update' => 0, 'last_delete' => 0, 'last_start' => 0];
    }

}
