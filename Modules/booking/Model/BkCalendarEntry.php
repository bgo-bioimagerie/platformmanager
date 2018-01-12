<?php

require_once 'Framework/Model.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * Class defining the GRR area model
 *
 * @author Sylvain Prigent
 */
class BkCalendarEntry extends Model {

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
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn('bk_calendar_entry', 'period_id', 'int(11)', 0);
        $this->addColumn('bk_calendar_entry', 'all_day_long', 'int(1)', 0);
        $this->addColumn('bk_calendar_entry', 'deleted', 'int(1)', 0);
    }

    public function getStatsQuantities($id_space, $dateBegin, $dateEnd) {

        $dateBeginArray = explode("-", $dateBegin);
        $beginTime = mktime(0, 0, 0, $dateBeginArray[1], $dateBeginArray[2], $dateBeginArray[0]);
        $dateEndArray = explode("-", $dateEnd);
        $endTime = mktime(0, 0, 0, $dateEndArray[1], $dateEndArray[2], $dateEndArray[0]);

        // get all the quantity types
        $sql = "SELECT * FROM bk_calquantities WHERE id_resource IN (SELECT id FROM re_info WHERE id_space=?)";
        $quantities = $this->runRequest($sql, array($id_space))->fetchAll();
        for ($i = 0; $i < count($quantities); $i++) {
            $count = 0;
            $sql = "SELECT quantities FROM bk_calendar_entry WHERE start_time>=? AND start_time<=? AND resource_id IN (SELECT id FROM re_info WHERE id_space=?)";
            $dd = $this->runRequest($sql, array($beginTime, $endTime, $id_space))->fetchAll();
            foreach ($dd as $dddd) {
                $d = $dddd[0];
                //if ( $d != ""){
                //    echo $d . "<br/>";
                //}
                $darray = explode(";", $d);
                foreach ($darray as $di) {
                    $diarray = explode("=", $d);
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
        $sql1 = "SELECT id, name FROM re_info WHERE id_space=?";
        $resources = $this->runRequest($sql1, array($id_space))->fetchAll();
        
        // get all responsibles
        $sql2 = "SELECT DISTINCT responsible_id FROM bk_calendar_entry WHERE resource_id IN (SELECT id FROM re_info WHERE id_space=?) AND start_time>=? AND start_time<=?";
        $resps = $this->runRequest($sql2, array($id_space, $dateBeginTime, $dateEndTime))->fetchAll();
        
        
        $data = array();
        $data["resources"] = $resources;
        $data["count"] = array();
        
        foreach($resps as $resp){
            
            $sqlr = "SELECT name, firstname FROM core_users WHERE id=?";
            $respinfo = $this->runRequest($sqlr, array($resp[0]))->fetch();
            
            $resourceCount = array();
            foreach( $resources as $resource){
                $sql3 = "SELECT * FROM bk_calendar_entry WHERE responsible_id=? AND resource_id=? AND start_time>=? AND start_time<=?";
                $res = $this->runRequest($sql3, array($resp[0], $resource["id"], $dateBeginTime, $dateEndTime))->fetchAll();
                $time = 0;
                foreach($res as $r){
                    $time += $r["end_time"] - $r["start_time"];
                }
                $resourceCount[] = array( "resource" => $resource["name"], "time" => round($time/3600, 1) );
            }
            
            $data["count"][] = array( "responsible" => $respinfo["name"] . " " . $respinfo["firstname"], "count" => $resourceCount ); 
        }
        return $data;
        
    }

    public function updateNullResponsibles() {

        $sql = "SELECT * FROM bk_calendar_entry WHERE responsible_id<=1";
        $data = $this->runRequest($sql)->fetchAll();
        $modelResponsible = new EcResponsible();
        foreach ($data as $d) {
            $resps = $modelResponsible->getUserResponsibles($d["recipient_id"]);

            //print_r($resps); echo "<br/>";
            //echo "id = " . $d["id"] . "<br/>"; 

            if (count($resps) > 0) {
                $sql = "UPDATE bk_calendar_entry SET responsible_id=? WHERE id=?";
                $this->runRequest($sql, array($resps[0]["id_resp"], $d["id"]));
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

    public function getPeriod($id) {
        $sql = "SELECT period_id FROM bk_calendar_entry WHERE id=?";
        $tmp = $this->runRequest($sql, array($id))->fetch();
        return $tmp[0];
    }

    public function setPeriod($id, $period_id) {
        $sql = "UPDATE bk_calendar_entry SET period_id=? WHERE id=?";
        $this->runRequest($sql, array($period_id, $id));
    }

    public function cleanBadResa() {
        $sql = "DELETE FROM bk_calendar_entry WHERE (start_time>end_time) OR start_time=0 OR end_time=0";
        $this->runRequest($sql);
    }

    public function setReservationInvoice($reservation_id, $invoice_id) {
        $sql = "UPDATE bk_calendar_entry SET invoice_id=? WHERE id=?";
        $this->runRequest($sql, array($invoice_id, $reservation_id));
    }

    public function getInvoiceEntries($id_invoice) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE invoice_id=?";
        return $this->runRequest($sql, array($id_invoice))->fetchAll();
    }

    public function getResourcesForInvoice($id_invoice) {
        $sql = "SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE invoice_id=?";
        return $this->runRequest($sql, array($id_invoice))->fetchAll();
    }

    public function getResourcesUsersForInvoice($id_resource, $id_invoice) {
        $sql = "SELECT DISTINCT recipient_id FROM bk_calendar_entry WHERE resource_id=? AND invoice_id=?";
        return $this->runRequest($sql, array($id_resource, $id_invoice))->fetchAll();
    }

    public function getResourcesUserResaForInvoice($id_resource, $id_user, $id_invoice) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE recipient_id=? AND resource_id=? AND invoice_id=?";
        return $this->runRequest($sql, array($id_user, $id_resource, $id_invoice))->fetchAll();
    }

    public function getDefault($start_time, $end_time, $resource_id, $id_user) {

        $modelResp = new EcResponsible();
        $resps = $modelResp->getUserResponsibles($id_user);
        //print_r($resps);

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
            "responsible_id" => $resps[0]['id_resp'],
            "invoice_id" => 0,
            "all_day_long" => 0);
    }

    public function setAllDayLong($id, $all_day_long) {
        $sql = "UPDATE bk_calendar_entry SET all_day_long=? WHERE id=?";
        $this->runRequest($sql, array($all_day_long, $id));
    }

    public function setDeleted($id, $deleted = 1) {
        $sql = "UPDATE bk_calendar_entry SET deleted=? WHERE id=?";
        $this->runRequest($sql, array($deleted, $id));
    }

    public function setEntry($id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id) {
        if ($id == 0) {
            $sql = "INSERT INTO bk_calendar_entry (start_time, end_time, resource_id, booked_by_id, recipient_id, 
                    last_update, color_type_id, short_description, full_description, quantities, 
                    supplementaries, package_id, responsible_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE bk_calendar_entry SET start_time=?, end_time=?, resource_id=?, booked_by_id=?, recipient_id=?, 
                    last_update=?, color_type_id=?, short_description=?, full_description=?, quantities=?, 
                    supplementaries=?, package_id=?, responsible_id=? WHERE id=?";
            $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
                $last_update, $color_type_id, $short_description, $full_description, $quantities,
                $supplementaries, $package_id, $responsible_id, $id));
            return $id;
        }
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
    public function addEntry($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {

        $sql = "insert into bk_calendar_entry(start_time, end_time, resource_id, booked_by_id, recipient_id, 
							last_update, color_type_id, short_description, full_description, quantities, package_id)"
                . " values(?,?,?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
            $last_update, $color_type_id, $short_description, $full_description, $quantity, $package));
        return $this->getDatabase()->lastInsertId();
    }

    public function getAllEntries($deleted = 0) {
        $sql = "select * from bk_calendar_entry WHERE deleted=?";
        $req = $this->runRequest($sql, array($deleted));
        return $req->fetchAll();
    }

    public function getZeroRespEntries() {
        $sql = "select * from bk_calendar_entry WHERE responsible_id=0";
        $req = $this->runRequest($sql);
        return $req->fetchAll();
    }

    public function getUnpricedReservations($beginPeriod, $endPeriod, $id_resource, $id_resp) {

        $beginPeriodArray = explode("-", $beginPeriod);
        $searchDate_start = mktime(0, 0, 0, $beginPeriodArray[1], $beginPeriodArray[2], $beginPeriodArray[0]);
        $endPeriodArray = explode("-", $endPeriod);
        $searchDate_end = mktime(23, 59, 59, $endPeriodArray[1], $endPeriodArray[2], $endPeriodArray[0]);


        $q = array('start' => $searchDate_start, 'end' => $searchDate_end, 'resp' => $id_resp, 'resource' => $id_resource);
        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				start_time >=:start AND start_time < :end AND resource_id=:resource AND responsible_id=:resp
                                AND invoice_id=0 AND deleted=0 
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
    public function setEntryOld($id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {

        if (!$this->isCalEntry($id)) {
            return $this->addEntry($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity, $package);
        }
    }

    /**
     * Check if an entry exists
     * @param unknown $id
     * @return boolean
     */
    public function isCalEntry($id) {
        $sql = "select * from bk_calendar_entry where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the repeat ID for a series booking
     * @param unknown $id
     * @param unknown $repeat_id
     */
    public function setRepeatID($id, $repeat_id) {
        $sql = "update bk_calendar_entry set repeat_id=?
									  where id=?";
        $this->runRequest($sql, array($repeat_id, $id));
    }

    /**
     * Get the informations of an entry
     * @param unknown $id
     * @return mixed
     */
    public function getEntry($id) {
        $sql = "select * from bk_calendar_entry where id=?";
        $req = $this->runRequest($sql, array($id));
        return $req->fetch();
    }

    public function updateEntry($id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity = 0, $package = 0) {
        $sql = "update bk_calendar_entry set start_time=?, end_time=?, resource_id=?, booked_by_id=?, recipient_id=?, 
							last_update=?, color_type_id=?, short_description=?, full_description=?, quantity=?, package_id=?
									  where id=?";
        $this->runRequest($sql, array($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id,
            $last_update, $color_type_id, $short_description, $full_description, $quantity, $package, $id));
    }

    public function setEntryResponsible($id, $responsibleId) {
        $sql = "update bk_calendar_entry set responsible_id=? where id=?";
        $this->runRequest($sql, array($responsibleId, $id));
    }

    /**
     * Get all the entries for a given day
     * @param unknown $curentDate
     * @return multitype:
     */
    public function getEntriesForDay($curentDate) {
        $dateArray = explode("-", $curentDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);

        $q = array('start' => $dateBegin, 'end' => $dateEnd);
        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				(start_time <=:end AND end_time >= :start) 
                                AND deleted=0
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
    public function getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $resource_id) {
        //$dateArray = explode("-", $date);
        //$dateBegin = mktime(0,0,0,$dateArray[1],$dateArray[2],$dateArray[0]);
        //$dateEnd = mktime(23,59,59,$dateArray[1],$dateArray[2],$dateArray[0]);

        $q = array('start' => $dateBegin, 'end' => $dateEnd, 'res' => $resource_id);

        $sql = 'SELECT * FROM bk_calendar_entry WHERE
				(start_time <=:end AND end_time >= :start) AND resource_id = :res
                                AND deleted=0
				ORDER BY start_time';

        $req = $this->runRequest($sql, $q);
        $data = $req->fetchAll(); // Liste des bénéficiaire dans la période séléctionée


        $modelUser = new EcUser();
        $modelColor = new BkColorCode();
        for ($i = 0; $i < count($data); $i++) {
            //echo "color id = " . $data[$i]["color_type_id"] . "</br>";
            $rid = $data[$i]["recipient_id"];
            if ($rid > 0) {
                $userInfo = $modelUser->userAllInfo($rid);
                $data[$i]["recipient_fullname"] = $userInfo["name"] . " " . $userInfo["firstname"];
                $data[$i]["phone"] = "";
                if (isset($userInfo["phone"])) {
                    $data[$i]["phone"] = $userInfo["phone"];
                }
                $data[$i]["color_bg"] = $modelColor->getColorCodeValue($data[$i]["color_type_id"]);
                $data[$i]["color_text"] = $modelColor->getColorCodeText($data[$i]["color_type_id"]);
            } else {
                $data[$i]["recipient_fullname"] = "";
                $data[$i]["phone"] = "";
                $data[$i]["color_bg"] = $modelColor->getColorCodeValue($data[$i]["color_type_id"]);
                $data[$i]["color_text"] = $modelColor->getColorCodeText($data[$i]["color_type_id"]);
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
    public function getEntriesForPeriodeAndArea($dateBegin, $dateEnd, $areaId) {

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($areaId);

        $data = array();
        foreach ($resources as $resource) {
            $id = $resource["id"];
            $dataInter = $this->getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $id);
            $data = array_merge($data, $dataInter);
        }

        return $data;
    }

    public function isConflictP($start_time, $end_time, $resource_id, $id_period) {
        $sql = "SELECT id, period_id FROM bk_calendar_entry WHERE
			  ((start_time <=:start AND end_time > :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                           (start_time <=:start AND end_time >= :end) 
                           ) 
                           AND deleted=0
			AND resource_id = :res;";
        $q = array('start' => $start_time, 'end' => $end_time, 'res' => $resource_id);
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            if ($id_period > 0 && $req->rowCount() == 1) {
                $tmp = $req->fetch();
                $period_id = $tmp['period_id'];
                //echo 'found a conflict with ' . $tmp['period_id'] . ' for '.$id_period.'<br>'; 
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

    public function hasTooManyReservations($start_time, $id_user, $id_resource, $reservation_id, $bookingQuota) {

        $year = date('Y', $start_time);
        $month = date('m', $start_time);
        $day = date('d', $start_time);

        //echo "date = " . $year ."-". $month . "-" . $day . "<br/>";
        //echo "booking quota = " . $bookingQuota . "<br/>";

        $startDayTime = mktime(0, 0, 0, $month, $day, $year);
        $endDayTime = mktime(23, 59, 59, $month, $day, $year);

        $sql = "SELECT id FROM bk_calendar_entry WHERE "
                . "start_time >= ? AND start_time<=? "
                . "AND deleted=0 "
                . "AND resource_id=? "
                . "AND booked_by_id=? ";

        if ($reservation_id != "") {
            $sql .= "AND id!=?";
        }
        $req = $this->runRequest($sql, array($startDayTime, $endDayTime, $id_resource, $id_user, $reservation_id));

        if ($req->rowCount() >= $bookingQuota) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a new entry is in conflic with an existing entries
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $resource_id
     * @param string $reservation_id
     * @return boolean
     */
    public function isConflict($start_time, $end_time, $resource_id, $reservation_id = "") {
        $sql = "SELECT id FROM bk_calendar_entry WHERE
			  ((start_time <=:start AND end_time > :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time <=:end AND end_time >= :start AND end_time <= :end) OR
                           (start_time >=:start AND start_time < :end AND end_time >= :end) OR 
                           (start_time <=:start AND end_time >= :end) 
                           ) 
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
     * Check if a new entry is in conflic with an existing entries
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
    public function removeEntry($id) {
        $sql = "UPDATE bk_calendar_entry SET deleted=? WHERE id=?";
        $this->runRequest($sql, array(1, $id));
        /*
          $sql = "DELETE FROM bk_calendar_entry WHERE id = ?";
          $this->runRequest($sql, array($id));
         */
    }

    /**
     * Removes all the entries of a series
     * @param unknown $series_id
     */
    public function removeEntriesFromSeriesID($series_id) {
        $sql = "UPDATE bk_calendar_entry SET deleted=? WHERE repeat_id=?";
        $this->runRequest($sql, array(1, $series_id));
        /*
          $sql = "DELETE FROM bk_calendar_entry WHERE repeat_id = ?";
          $this->runRequest($sql, array($series_id));
         */
    }

    /**
     * Delect entries having a given description
     * @param unknown $desciption
     * @return multitype:
     */
    public function selectEntriesByDescription($desciption) {
        $sql = "SELECT * FROM bk_calendar_entry WHERE short_description=? ORDER BY end_time";
        $req = $this->runRequest($sql, array($desciption));
        return $req->fetchAll();
    }

    /**
     * Check if a responsible has entries in a given period
     * @param unknown $resp_id
     * @param unknown $startdate
     * @param unknown $enddate
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
                                AND invoice_id=0';
        $req = $this->runRequest($sql, $q);

        if ($req->rowCount() > 0) {
            return 1;
        }
        return 0;
    }

    /**
     * Check if there are some entries for a unit in a given period
     * @param unknown $unit_id
     * @param unknown $startdate
     * @param unknown $enddate
     * @return boolean
     */
    public function hasUnitEntry($unit_id, $startdate, $enddate) {
        $q = array('start' => $startdate, 'end' => $enddate);
        $sql = 'SELECT DISTINCT recipient_id, id FROM bk_calendar_entry WHERE
				(start_time >=:start AND start_time <= :end)
                                AND deleted=0
                                ';
        $req = $this->runRequest($sql, $q);
        $recs = $req->fetchAll();

        foreach ($recs as $rec) {
            $sql = "select id_unit from core_users where id=" . $rec["recipient_id"];
            $req = $this->runRequest($sql);
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
    public function getUserBooking($user_id) {
        $sql = "select * from bk_calendar_entry where recipient_id=? AND deleted=0 order by end_time DESC;";
        $req = $this->runRequest($sql, array($user_id));
        return $req->fetchAll();
    }

    /**
     * Get all the entries for a given user and a given resource
     * @param unknown $user_id
     * @param unknown $resource_id
     * @return multitype:
     */
    public function getUserBookingResource($user_id, $resource_id) {
        $sql = "select * from bk_calendar_entry where recipient_id=? and resource_id=? AND deleted=0 order by end_time DESC;";
        $req = $this->runRequest($sql, array($user_id, $resource_id));
        return $req->fetchAll();
    }

    /**
     * Get the emails address of the users who booked a resource
     * @param unknown $resource_id
     * @return multitype:
     */
    public function getEmailsBookerResource($resource_id) {

        $sql = "SELECT DISTINCT user.email AS email 
				FROM core_users AS user
				INNER JOIN bk_calendar_entry AS bk_calendar_entry ON user.id = bk_calendar_entry.recipient_id
				WHERE bk_calendar_entry.resource_id=?
                                AND deleted=0
				AND user.is_active = 1 
				;";
        $req = $this->runRequest($sql, array($resource_id));
        return $req->fetchAll();
    }

    /**
     * Get the emails address of the users who booked resorces of a given area
     * @param unknown $area_id
     * @return multitype:
     */
    public function getEmailsBookerArea($area_id) {

        $sql = "SELECT DISTINCT user.email AS email
				FROM core_users AS user
				INNER JOIN bk_calendar_entry AS bk_calendar_entry ON user.id = bk_calendar_entry.recipient_id
				WHERE bk_calendar_entry.resource_id IN (SELECT id FROM re_info WHERE id_area=?) 
				AND user.is_active = 1  
                                AND deleted=0
				;";
        $req = $this->runRequest($sql, array($area_id));
        return $req->fetchAll();
    }

}
