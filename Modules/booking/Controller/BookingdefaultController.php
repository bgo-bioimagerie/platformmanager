<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Email.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/booking/Controller/BookingabstractController.php';

require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BkCalendarPeriod.php';
require_once 'Modules/booking/Model/BkRestrictions.php';

require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ReArea.php';

require_once 'Modules/core/Model/CoreUserSettings.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingdefaultController extends BookingabstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->module = "booking";
    }


    /**
     * @deprecated
     */
    public function indexAction() {

    }

    public function editreservationdefault($id_space, $param) {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);

        if ($this->isNew($param)) {
            $resaInfo = $this->addreservation($id_space, $param);
            return $this->editReservation($id_space, $resaInfo, $param);
        } else {
            $resaInfo = $this->editReservationInfo($id_space, $param);
            return $this->editReservation($id_space, $resaInfo, $param);
        }
    }

    private function addreservation($id_space ,$param) {
        // get the parameters
        $paramVect = explode("_", $param);
        $date = $paramVect[1];
        $dateArray = explode("-", $date);
        $hour = $paramVect[2];
        $hourArray = explode("-", $hour);
        $id_resource = $paramVect[3];

        $modelResource = new ResourceInfo();
        $modelScheduling = new BkScheduling();
        $schedule = $modelScheduling->getByReArea($id_space ,$modelResource->getAreaID($id_space, $id_resource));

        $minutes = 0;
        if (count($hourArray) == 2) {
            $minutes = $hourArray[1];
        }

        $start_time = mktime($hourArray[0], $minutes, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $duration = 0;
        $units = 'h';
            switch ($schedule['booking_time_scale']) {
                case 1:
                    $end_time = $start_time + $schedule['size_bloc_resa'];
                    $duration = ($end_time - $start_time) / 60;
                    $units = 'm';
                    break;
                case 2:
                    $end_time = $start_time + 3600;
                    $duration = ($end_time - $start_time) / 3600;
                    $units = 'h';
                    break;
                case 3:
                    $start_time = mktime($schedule['day_begin'], 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    $end_time = mktime($schedule['day_end'], 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    $duration = 1;
                    $units = 'd';
                    break;
                default:
                    $end_time = $start_time + 3600;
                    $duration = ($end_time - $start_time) / 60;
                    break;
            }
        if($schedule['resa_time_setting'] == 2) {
            $duration=0;
        }

        $modelResa = new BkCalendarEntry();
        $id_user = $_SESSION["id_user"];

        $resaInfo = $modelResa->getDefault($id_space ,$start_time, $end_time, $id_resource, $id_user);
        $resaInfo['duration'] = $duration;
        $resaInfo['durationUnits'] = $units;
        return $resaInfo;

    }

    private function canUserEditReservation($id_space, $id_resource, $id_user, $id_reservation, $id_recipient, $start_date) {

        if ($id_reservation == 0) {
            return true;
        }
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($role >= CoreSpace::$MANAGER) {
            return true;
        }

        $modelRestrictions = new BkRestrictions();
        $limitHours = $modelRestrictions->getBookingDelayUserCanEdit($id_space, $id_resource);

        if ($id_recipient == $id_user) {

            if ($limitHours >= 0 && ($start_date - 3600*$limitHours > time())) {
                return true;
            }
            $modelConfig = new CoreConfig();
            $canEdit = intval($modelConfig->getParamSpace("BkCanUserEditStartedResa", $id_space));
            if($canEdit == 1){
                return true;
            }
        }
        return false;
    }

    public function editreservationqueryAction($id_space) {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelUser = new CoreUser();
        $modelResource = new ResourceInfo();
        $resource = $modelResource->get($id_space, $this->request->getParameter("id_resource"));
        $modelBkAccess = new BkAccess();
        $bkAccess = $modelBkAccess->getAccessId($id_space, $resource['id']);

        $responsible_id = $this->request->getParameterNoException("responsible_id");

        $id = $this->request->getParameter("id");
        $id_entry = $id;
        $id_resource = $this->request->getParameter("id_resource");
        $booked_by_id = $_SESSION["id_user"];
        $recipient_id = $this->request->getParameter("recipient_id");
        $last_update = date("Y-m-d H:m:i", time());
        $color_type_id = $this->request->getParameter("color_type_id");
        $short_description = $this->request->getParameterNoException("short_description");
        $full_description = $this->request->getParameterNoException("full_description");
        $all_day_long = intval($this->request->getParameterNoException("all_day_long"));

        $dateResaStart = $this->request->getParameter("resa_start");
        $dateResaEnd = $this->request->getParameterNoException("resa_end");
        $duration = $this->request->getParameterNoException("resa_duration");
        if(!$dateResaEnd && !$duration) {
            throw new PfmParamException('no end date nor duration specified');
        }

        $temp = explode("-", $dateResaStart);
        try {
            $curentDateUnix = mktime(0, 0, 0, intval($temp[1]), intval($temp[2]), intval($temp[0]));
        } catch(Exception $e) {
            Configuration::getLogger()->debug('[booking] invalid input date', ['date' => $dateResaStart]);
            $curentDateUnix = time();
        }

        $canValidateBooking = $this->hasAuthorization($resource['id_category'], $bkAccess, $id_space, $_SESSION['id_user'], $curentDateUnix);

        $redir = $this->request->getParameterNoException('from');

        $backto = [];
        $redirPage = '';
        if($redir) {
            $redirInfo = explode(':', $redir);
            $redirPage = $redirInfo[0];
            $backto = ["bk_curentDate" => $redirInfo[1], "bk_id_resource"=> $redirInfo[2], "bk_id_area"=> $redirInfo[3], "id_user" => $redirInfo[4], "view" => $redirInfo[5]];
        }

        if (!$canValidateBooking) {
            $_SESSION['flash'] = BookingTranslator::resourceBookingUnauthorized($lang);
            $_SESSION['flashClass'] = "warning";
            return $this->redirect("booking$redirPage/".$id_space, $backto, ['error' => 'resourceBookingUnauthorized']);
        }

        $dateResaStartArray = explode("-", $dateResaStart);
        if($dateResaStart == "") {
            throw new PfmParamException("invalid start date");
        }

        $ri = $modelResource->get($id_space ,$id_resource);
        if(!$ri){
            Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id_resource]);
            throw new PfmAuthException('access denied for this resource', 403);
        }

        $modelScheduling = new BkScheduling();
        $schedule = $modelScheduling->getByReArea($id_space, $ri['id_area']);


        if($all_day_long == 1){
            $start_time = mktime($schedule["day_begin"], 0, 0, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);
        }
        else{
            $hour_startH = $this->request->getParameter("hour_startH");
            $hour_startM = $this->request->getParameter("hour_startm");
            $modelScheduling = new BkScheduling();
            $hour_startM = $modelScheduling->getClosestMinutes($id_space, $id_resource, $hour_startM);
            $start_time = mktime($hour_startH, $hour_startM, 0, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);
        }



        $hour_endH = $this->request->getParameterNoException("hour_endH");
        $hour_endM = $this->request->getParameterNoException("hour_endm");

        if($duration && !$dateResaEnd) {
            $units = $this->request->getParameterNoException("resa_units");
            switch ($units) {
                case 'm':
                    $end = $start_time + (60 * $duration);
                    break;
                case 'h':
                    $end = $start_time + (3600 * $duration);
                    break;
                case 'd':
                    $end = mktime(23, 59, 59, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);
                    break;
                default:
                    $end = $start_time + (3600 * $duration);
                    break;
            }
            $dateResaEnd = date('Y-m-d', $end);
            $hour_endH = intval(date('G', $end));
            $hour_endM = intval(date('i', $end));
        }

        $dateResaEndArray = explode("-", $dateResaEnd);
        if($dateResaEnd == "" || $hour_endH == "" || $hour_endM == "") {
            throw new PfmParamException("invalid end date, missing end date or time");
        }

        if($all_day_long == 1){
            $end_time = mktime($schedule["day_end"]-1, 59, 59, $dateResaEndArray[1], $dateResaEndArray[2], $dateResaEndArray[0]);
        }
        else{
            $modelScheduling = new BkScheduling();
            $hour_endM = $modelScheduling->getClosestMinutes($id_space ,$id_resource, $hour_endM);

            $end_time = mktime($hour_endH, $hour_endM, 0, $dateResaEndArray[1], $dateResaEndArray[2], $dateResaEndArray[0]);
        }

        
        $bk_start_start_time = mktime($schedule["day_begin"], 0, 0, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);
        $bk_start_end_time = mktime($schedule["day_end"], 0, 0, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);

        $bk_end_start_time = mktime($schedule["day_begin"], 0, 0, $dateResaEndArray[1], $dateResaEndArray[2], $dateResaEndArray[0]);
        $bk_end_end_time = mktime($schedule["day_end"], 0, 0, $dateResaEndArray[1], $dateResaEndArray[2], $dateResaEndArray[0]);


        $dayofweek = strtolower(date('l', $start_time));
        if(!$schedule['is_'.$dayofweek]) {
            throw new PfmParamException('invalid booking start day: '.$dayofweek);
        }
        $dayofweek = strtolower(date('l', $end_time));
        if(!$schedule['is_'.$dayofweek]) {
            throw new PfmParamException('invalid bookin end day: '.$dayofweek);
        }

        if($bk_start_start_time > $start_time || $start_time > $bk_start_end_time) {
            throw new PfmParamException("start hour not in schedule [".$schedule["day_begin"].":".$schedule["day_end"]."]");
        }

        if($bk_end_start_time > $end_time || $end_time > $bk_end_end_time) {
            throw new PfmParamException("end hour not in schedule [".$schedule["day_begin"].":".$schedule["day_end"]."]");
        }


        $modelSupInfo = new BkCalSupInfo();
        $supInfos = $modelSupInfo->getForResource($id_space, $id_resource);
        $supplementaries = "";
        foreach ($supInfos as $sup) {
            $q = $this->request->getParameterNoException("sup" . $sup["id"]);
            $supplementaries .= $sup["id"] . "=" . $q . ";";
        }

        $modelQuantities = new BkCalQuantities();
        $quantitiesInfo = $modelQuantities->getByResource($id_space ,$id_resource);
        $quantities = "";
        foreach ($quantitiesInfo as $q) {
            $qt = $this->request->getParameterNoException("q" . $q["id"]);
            $quantities .= $q["id"] . "=" . $qt . ";";
        }

        $use_package = $this->request->getParameterNoException("use_package");
        $package_id = 0;
        if ($use_package == "yes") {
            $package_id = $this->request->getParameter("package_id");
            $modelPackage = new BkPackage();
            $pk_duration = $modelPackage->getPackageDuration($id_space ,$package_id);
            $end_time = $start_time + 3600 * $pk_duration;
        }

        $modelResp = new ClClientUser();
        $userResps = $modelResp->getUserClientAccounts($recipient_id, $id_space);
        $foundResp = false;
        foreach ($userResps as $uresp) {
            if ($uresp["id"] == $responsible_id) {
                $foundResp = true;
                break;
            }
        }

        if (!$foundResp) {
            $_SESSION['flash'] = 'No client defined';
            $resaInfo = array(
                "id" => $id,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "resource_id" => $id_resource,
                "booked_by_id" => $booked_by_id,
                "recipient_id" => $recipient_id,
                "last_update" => date("Y-m-d H:m:i", time()),
                "color_type_id" => $color_type_id,
                "short_description" => $short_description,
                "full_description" => $full_description,
                "quantities" => $quantities,
                "supplementaries" => $supplementaries,
                "package_id" => $package_id,
                "responsible_id" => 0
            );
            return $this->editReservation($id_space, $resaInfo);
        }

        $canEdit = $this->canUserEditReservation($id_space, $id_resource, $_SESSION["id_user"], $id, $recipient_id, $start_time);
        if (!$canEdit) {
            throw new PfmAuthException("ERROR: You're not allowed to modify this reservation", 403);
        }

        $modelCalEntry = new BkCalendarEntry();

        $valid = true;
        if ($start_time >= $end_time) {
            $_SESSION["flash"] = "Error: Start Time Must Be Before End Time";
            $valid = false;
        }
        if ($start_time == 0) {
            $_SESSION["flash"] = "Error: Start Time Cannot Be Null";
            $valid = false;
        }
        if ($start_time == 0) {
            $_SESSION["flash"] = "Error: End Time Cannot Be Null";
            $valid = false;
        }

        // set the reservation
        $modelCoreConfig = new CoreConfig();
        $modelRestrictions = new BkRestrictions();
        $BkUseRecurentBooking = $modelCoreConfig->getParamSpace("BkUseRecurentBooking", $id_space, 0);
        $periodic_option = intval($this->request->getParameterNoException("periodic_radio"));

        $error = null;

        $oldEntry = null;
        if($id > 0) {
            $oldEntry = $modelCalEntry->getEntry($id_space, $id);
        }

        if (!$BkUseRecurentBooking || $periodic_option == 1) {
            // test if a resa already exists on this periode
            $resources_id = [$id_resource];
            if($schedule['shared']) {
                $rem = new ResourceInfo();
                $areaResources = $rem->resourceIDNameForArea($id_space, $ri['id_area']);
                $resources_id = [];
                foreach ($areaResources as $r) {
                    $resources_id[] = $r['id'];
                }
            }
            $conflict = $modelCalEntry->isConflict($id_space, $start_time, $end_time, $resources_id, $id);
            if ($conflict) {
                $_SESSION["flash"] = BookingTranslator::reservationError($lang);
                $error = 'reservationError';
                $valid = false;
            }
            // test if the user is above quota
            $modelSpace = new CoreSpace();
            $userSpaceRole = $modelSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
            if( $userSpaceRole <= 2 ){
                $bookingQuota = $modelRestrictions->getMaxBookingPerDay($id_space ,$id_resource);
                if($bookingQuota != "" && $bookingQuota>0){
                    $userHasTooManyReservations = $modelCalEntry->hasTooManyReservations($id_space, $start_time, $_SESSION["id_user"], $id_resource, $id, $bookingQuota);
                    if ($userHasTooManyReservations){
                        $_SESSION["flash"] = BookingTranslator::quotaReservationError($bookingQuota, $lang);
                        $valid = false;
                        $error = 'quotaReservationError';
                    }
                }
            }
            if ($valid) {
                $id_entry = $modelCalEntry->setEntry($id_space ,$id, $start_time, $end_time, $id_resource, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
                $modelCalEntry->setAllDayLong($id_space, $id_entry, $all_day_long);
                $_SESSION["flash"] = BookingTranslator::reservationSuccess($lang);
                $_SESSION["flashClass"] = 'success';

            }
        } else {
            $periodicEndDate = $this->request->getParameter("periodic_enddate");
            $periodicEndDateArray = explode("-", $periodicEndDate);
            if($periodicEndDate == "") {
                throw new PfmParamException("invalid end date");
            }
            // check parameters order here !!!
            $last_start_time = mktime($hour_startH, $hour_startM, 0, $periodicEndDateArray[1], $periodicEndDateArray[2], $periodicEndDateArray[0]);
            $modelPeriodic = new BkCalendarPeriod();
            $id_period = $modelCalEntry->getPeriod($id_space ,$id);
            $id_period = $modelPeriodic->setPeriod($id_space, $id_period, $periodic_option, 0); // initialize with default option updated later
            $modelPeriodic->setEndDate($id_space ,$id_period, $periodicEndDate);

            // every day
            $modelPeriodic->deleteAllPeriodEntries($id_space ,$id_period);
            if ($periodic_option == 2) {

                $is_one_false = false;
                $pass = -86400;
                for ($btime = $start_time; $btime <= $last_start_time; $btime+=86400) {
                    $pass += 86400;
                    $conflict = $modelCalEntry->isConflictPeriod($id_space ,$btime, $end_time + $pass, [$id_resource], $id_period);

                    $valid = true;
                    if ($conflict) {
                        $_SESSION["flash"] = BookingTranslator::reservationError($lang);
                        $valid = false;
                        $is_one_false = true;
                        $error = 'reservationError';
                    }
                    if ($valid) {
                        $id_entry = $modelCalEntry->setEntry($id_space, 0, $btime, $end_time + $pass, $id_resource, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
                        $modelCalEntry->setPeriod($id_space, $id_entry, $id_period);
                        $modelCalEntry->setAllDayLong($id_space, $id_entry, $all_day_long);
                        $_SESSION["flash"] = BookingTranslator::reservationSuccess($lang);
                    }
                }
                if ($is_one_false) {
                    $modelPeriodic->deleteAllPeriod($id_space ,$id_period);
                }
            }
            // every week
            else if ($periodic_option == 3) {

                $periodic_week = intval($this->request->getParameter("periodic_week"));
                $modelPeriodic->setPeriod($id_space ,$id_period, $periodic_option, $periodic_week);
                $step = $periodic_week * 7 * 24 * 3600;
                $pass = -$step;

                $is_one_false = false;
                for ($btime = $start_time; $btime <= $last_start_time; $btime+=$step) {

                    $pass += $step;
                    $conflict = $modelCalEntry->isConflictPeriod($id_space ,$btime, $end_time + $pass, [$id_resource], $id_period);

                    $valid = true;
                    if ($conflict) {
                        $_SESSION["flash"] = BookingTranslator::reservationError($lang);
                        $valid = false;
                        $is_one_false = true;
                        $error = 'reservationError';
                    }
                    if ($valid) {
                        $id_entry = $modelCalEntry->setEntry($id_space ,0, $btime, $end_time + $pass, $id_resource, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
                        $modelCalEntry->setPeriod($id_space ,$id_entry, $id_period);
                        $modelCalEntry->setAllDayLong($id_space ,$id_entry, $all_day_long);

                        $_SESSION["flash"] = BookingTranslator::reservationSuccess($lang);
                    }
                }
                if ($is_one_false) {
                    $modelPeriodic->deleteAllPeriod($id_space, $id_period);
                }
            }
            // every month
            else if ($periodic_option == 4) {
                $periodic_month = intval($this->request->getParameter("periodic_month"));
                $id_period = $modelPeriodic->setPeriod($id_space, $id_period, $periodic_option, $periodic_month);
                // same date
                $last_start = date('Y-m-d', $last_start_time);
                $startDate = date('Y-m-d', $start_time);
                $curentDate = $startDate;
                $is_one_false = false;
                $n = -1;

                while ($curentDate < $last_start) {
                    $n++;
                    // calculate next month
                    if ($periodic_month == 1) {
                        if($n == 0){
                            $curentDate = $startDate;
                        }
                        else{
                            $curentDate = date('Y-m-d', strtotime('+' . $n . ' month', strtotime($startDate)));
                        }
                    }
                    // same day one month later
                    else { // $periodic_month == 2
                        if($n == 0){
                            $curentDate = $startDate;
                        }
                        else{
                            $nextMonthtime = strtotime('+' . $n . ' month', strtotime($startDate));
                            $delidatetime = strtotime($startDate);
                            $baseDay = date('w', $delidatetime);
                            $offsetPos = 0;
                            for ($i=0 ; $i < 7 ; $i++){
                                if ( date('w', $nextMonthtime + $i*24*3600) == $baseDay){
                                    $offsetPos = $i;
                                    break;
                                }
                            }
                            $offsetNeg = 0;
                            for ($i=0 ; $i < 7 ; $i++){
                                if ( date('w', $nextMonthtime - $i*24*3600) == $baseDay){
                                    $offsetNeg = $i;
                                    break;
                                }
                            }
                            $newDateTime = 0;
                            if($offsetPos < $offsetNeg ){
                                $newDateTime = $nextMonthtime + $offsetPos*24*3600;
                            }
                            else{
                                $newDateTime = $nextMonthtime - $offsetNeg*24*3600;
                            }
                            $curentDate = date('Y-m-d', $newDateTime);
                        }
                    }

                    $curentDateArray = explode("-", $curentDate);
                    $year = $curentDateArray[0];
                    $month = $curentDateArray[1];
                    $day = $curentDateArray[2];

                    // add booking
                    $start_m_time = mktime($hour_startH, $hour_startM, 0, $month, $day, $year);
                    $end_m_time = mktime($hour_endH, $hour_endM, 0, $month, $day, $year);

                    $conflict = $modelCalEntry->isConflictPeriod($id_space ,$start_m_time, $end_m_time, [$id_resource], $id_period);

                    $valid = true;
                    if ($conflict) {
                        $_SESSION["flash"] = BookingTranslator::reservationError($lang);
                        $valid = false;
                        $is_one_false = true;
                        $error = 'reservationError';
                    }
                    if ($valid) {
                        $id_entry = $modelCalEntry->setEntry($id_space, 0, $start_m_time, $end_m_time, $id_resource, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
                        $modelCalEntry->setPeriod($id_space, $id_entry, $id_period);
                        $modelCalEntry->setAllDayLong($id_space, $id_entry, $all_day_long);
                        $_SESSION["flash"] = BookingTranslator::reservationSuccess($lang);
                        $_SESSION["flashClass"] = 'success';
                    }
                }
                if ($is_one_false) {
                    $modelPeriodic->deleteAllPeriod($id_space, $id_period);
                }
            }
            // every year
            else if ($periodic_option == 5) {
                $periodic_month = $this->request->getParameter("periodic_month");
                $modelPeriodic->setPeriod($id_space ,$id_period, $periodic_option, $periodic_month);

                // same date
                $last_start = date('Y-m-d', $last_start_time);
                $startDate = date('Y-m-d', $start_time);
                $curentDate = $startDate;
                $is_one_false = false;
                $n = -1;

                while ($curentDate < $last_start) {
                    $n++;

                    if($n == 0){
                        $curentDate = $startDate;
                    }
                    else{
                        $curentDate = date('Y-m-d', strtotime('+' . $n . ' year', strtotime($startDate)));
                    }

                    $curentDateArray = explode("-", $curentDate);
                    $year = $curentDateArray[0];
                    $month = $curentDateArray[1];
                    $day = $curentDateArray[2];

                    // add booking
                    $start_m_time = mktime($hour_startH, $hour_startM, 0, $month, $day, $year);
                    $end_m_time = mktime($hour_endH, $hour_endM, 0, $month, $day, $year);

                    $conflict = $modelCalEntry->isConflictPeriod($id_space, $start_m_time, $end_m_time, [$id_resource], $id_period);

                    $valid = true;
                    if ($conflict) {
                        $_SESSION["flash"] = BookingTranslator::reservationError($lang);
                        $valid = false;
                        $is_one_false = true;
                        $error = 'reservationError';
                    }
                    if ($valid) {
                        $id_entry = $modelCalEntry->setEntry($id_space, 0, $start_m_time, $end_m_time, $id_resource, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
                        $modelCalEntry->setPeriod($id_space, $id_entry, $id_period);
                        $modelCalEntry->setAllDayLong($id_space, $id_entry, $all_day_long);
                        $_SESSION["flash"] = BookingTranslator::reservationSuccess($lang);
                        $_SESSION["flashClass"] = 'success';
                    }
                }
                if ($is_one_false) {
                    $modelPeriodic->deleteAllPeriod($id_space ,$id_period);
                }
            }
        }

        if($valid) {
            // do not send email if not valid
            $emailSpaceAdmins = intval($modelCoreConfig->getParamSpace("BkBookingMailingAdmins", $id_space));
            if($emailSpaceAdmins == 2){
                // get resource name
                $modelResource = new ResourceInfo();
                $resourceName = $modelResource->getName($id_space, $id_resource);
                $modelUser = new CoreUser();
                $userName = $modelUser->getUserFUllName($_SESSION['id_user']);

                $modelResoucesResp = new ReResps();
                $toAdress = $modelResoucesResp->getResourcesManagersEmails($id_space, $id_resource);
                $subject = $resourceName . " has been booked";
                $content = "The " . $resourceName . " has been booked from " . date(Constants::DATETIME_FORMAT, $start_time) . " to " . date(Constants::DATETIME_FORMAT, $end_time) . " by " . $userName;
                if($id > 0 && $oldEntry) {
                    $subject = "[$id] $resourceName booking has been modified";
                    $content = "The " . $resourceName . " booking $i  has been moved from " . date(Constants::DATETIME_FORMAT, $oldEntry['start_time'])." / ".date(Constants::DATETIME_FORMAT, $oldEntry['end_time']). " to " . date(Constants::DATETIME_FORMAT, $start_time)." / ".date(Constants::DATETIME_FORMAT, $end_time) . " by " . $userName;
                }
                if( $BkUseRecurentBooking && $periodic_option > 1 ){
                    $content .= " with periodicity";
                }
                $params = [
                    "id_space" => $id_space,
                    "subject" => $subject,
                    "to" => $toAdress,
                    "content" => $content
                ];
                $email = new Email();
                $email->sendEmailToSpaceMembers($params, $lang);
            }
        }

        return $this->redirect("booking$redirPage/".$id_space, $backto, ['bkcalentry' => ['id' => $id_entry], 'error' => $error]);
    }

    private function editreservation($id_space, $resaInfo, $param = "") {
        $lang = $this->getLanguage();


        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
        $canEditReservation = false;
        if ($role > CoreSpace::$USER) {
            $canEditReservation = true;
        }

        $id_resource = $resaInfo["resource_id"];
        $canView = $this->canUserEditReservation($id_space, $id_resource, $_SESSION["id_user"], $resaInfo['id'], $resaInfo['recipient_id'], $resaInfo['start_time']);
        if (!$canView) {
            throw new PfmAuthException("ERROR: You're not allowed to edit this reservation", 403);
        }

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getAllForSelect($id_space, "name");

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");
        $formTitle = $this->isNew($param) ? BookingTranslator::Add_Reservation($lang) : BookingTranslator::Edit_Reservation($lang);

        $form = new Form($this->request, "editReservationDefault");
        $form->addText("id", "Id", false, $resaInfo['id'], false, true);
        $form->setValisationUrl("bookingeditreservationquery/" . $id_space);
        $form->setTitle($formTitle);
        $form->addHidden("from", $this->request->getParameterNoException('from'));
        if($resaInfo['reason'] > 0) {
            $form->addText("reason", BookingTranslator::Reason($lang), false, BookingTranslator::BlockReason($resaInfo['reason'], $lang), false, true);
        }

        $resourceName = $modelResource->get($id_space, $id_resource)['name'];
        if ($this->canBookForOthers($id_space, $_SESSION["id_user"])) {
            $form->addSelect("id_resource", ResourcesTranslator::resource($lang), $resources["names"], $resources["ids"], $id_resource);
            $form->addSelect("recipient_id", CoreTranslator::User($lang), $users["names"], $users["ids"], $resaInfo["recipient_id"]);
        } else {
            $form->addText('resource_name', ResourcesTranslator::resource($lang), true, $resourceName, 'disabled');
            $form->addHidden("id_resource", $id_resource);
            $form->addHidden("recipient_id", $resaInfo["recipient_id"]);
        }
        // responsible
        if ($canEditReservation) {
            $modelResp = new ClClientUser();
            $choices = array();
            $choicesid = array();
            $rID = $this->request->getParameterNoException("recipient_id");
            if ($rID == "") {
                $rID = $resaInfo["recipient_id"];
            }
            $resps = $modelResp->getUserClientAccounts($rID, $id_space);
            foreach ($resps as $r) {
                $choicesid[] = $r["id"];
                $choices[] = ($r["name"]);
            }
            $form->addSelect("responsible_id", ClientsTranslator::ClientAccount($lang), $choices, $choicesid, $resaInfo["responsible_id"]);
        } else {
            $modelResp = new ClClientUser();
            $resps = $modelResp->getUserClientAccounts($_SESSION["id_user"], $id_space);
            if (count($resps) > 1) {
                $choices = array();
                $choicesid = array();
                foreach ($resps as $r) {
                    $choicesid[] = $r["id"];
                    $choices[] = $r["name"];
                }
                $form->addSelect("responsible_id", ClientsTranslator::ClientAccount($lang), $choices, $choicesid, $resaInfo["responsible_id"]);
            } else {
                $form->addHidden("responsible_id", $resaInfo["responsible_id"]);
            }
        }

        // description
        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = intval($modelCoreConfig->getParamSpace("BkDescriptionFields", $id_space));
        if ($BkDescriptionFields == 1 || $BkDescriptionFields == 2) {
            $form->addText("short_description", BookingTranslator::Short_desc($lang), false, $resaInfo["short_description"]);
        }
        if ($BkDescriptionFields == 1 || $BkDescriptionFields == 3) {
            $form->addTextArea("full_description", BookingTranslator::Full_description($lang), false, $resaInfo["full_description"]);
        }

        // supplemetaries informations
        $modelSupInfo = new BkCalSupInfo();
        $supInfos = $modelSupInfo->getForResource($id_space, $id_resource);
        $supData = explode(";", $resaInfo["supplementaries"]);
        $supDataId = array();
        $supDataValue = array();
        foreach ($supData as $sup) {
            $sd = explode("=", $sup);
            if (count($sd) == 2) {
                $supDataId[] = $sd[0];
                $supDataValue[] = $sd[1];
            }
        }
        foreach ($supInfos as $sup) {
            $key = array_search($sup["id"], $supDataId);
            $value = "";
            if ($key !== false) {
                $value = $supDataValue[$key];
            }
            $form->addText("sup" . $sup["id"], $sup["name"], $sup["mandatory"], $value);
        }

        $modelColors = new BkColorCode();

        $modelUserSpace = new CoreSpace();
        $userSPaceRole = $modelUserSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
        $colors = $modelColors->getColorCodesForListUser($id_space, $userSPaceRole, "display_order");
        if (!$colors || (is_array($colors) && empty($colors))) {
            $_SESSION['flash'] = BookingTranslator::colorNeeded($lang);
        }
        $form->addSelectMandatory("color_type_id", BookingTranslator::color_code($lang), $colors["names"], $colors["ids"], $resaInfo["color_type_id"]);

        // quantities
        $modelQuantities = new BkCalQuantities();
        $quantitiesInfo = $modelQuantities->getByResource($id_space ,$id_resource);
        $qData = explode(";", $resaInfo["quantities"]);
        $qDataId = array();
        $qDataValue = array();
        foreach ($qData as $sup) {
            $sd = explode("=", $sup);
            if (count($sd) == 2) {
                $qDataId[] = $sd[0];
                $qDataValue[] = $sd[1];
            }
        }
        foreach ($quantitiesInfo as $q) {
            $name = $q["name"];
            if ($q["mandatory"] == 1) {
                $name .= "*";
            }
            $key = array_search($q["id"], $qDataId);
            $value = ($key!==false) ? $qDataValue[$key] : "";
            $form->addNumber("q" . $q["id"], $q["name"], $q["mandatory"], $value);
        }

        // booking nav bar
        $bk_id_area = $modelResource->getAreaID($id_space ,$id_resource);
        $curentDate = date("Y-m-d", $resaInfo["start_time"]);
        $_SESSION['bk_curentDate'] = $curentDate;
        $menuData = $this->calendarMenuData($id_space, $bk_id_area, $id_resource, $curentDate);

        // date time
        $form->addSelect("all_day_long", BookingTranslator::AllDay($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $resaInfo["all_day_long"] ?? 0);
        $form->addDate("resa_start", BookingTranslator::Beginning_of_the_reservation($lang), false, date("Y-m-d", $resaInfo["start_time"]), $lang);
        $form->addHour("hour_start", BookingTranslator::time($lang), false, array(date("H", $resaInfo["start_time"]), date("i", $resaInfo["start_time"])));

        // conditionnal on package
        $modelPackage = new BkPackage();
        $packages = $modelPackage->getByResource($id_space, $id_resource);
        $pNames = array();
        $pIds = array();
        foreach ($packages as $p) {
            $pNames[] = $p["name"];
            $pIds[] = $p["id"];
        }

        $use_packages = false;
        if (count($packages) > 0) {
            $use_packages = true;
        }
        $formPackage = new Form($this->request, "formPackage");
        $formPackage->addSelect("package_id", BookingTranslator::Package($lang), $pNames, $pIds, $resaInfo["package_id"], false);

        if(array_key_exists('duration', $resaInfo) && $resaInfo['duration']) {
            $formEndDate = new Form($this->request, "formEndDate");
            $formEndDate->addHidden("resa_units", $resaInfo['durationUnits']);
            $formEndDate->addNumber("resa_duration", BookingTranslator::Duration($lang). '('.$resaInfo['durationUnits'].')', true, $resaInfo['duration']);
        } else {
            $formEndDate = new Form($this->request, "formEndDate");
            $formEndDate->addDate("resa_end", BookingTranslator::End_of_the_reservation($lang), false, date("Y-m-d", $resaInfo["end_time"]));
            $formEndDate->addHour("hour_end", BookingTranslator::time($lang), false, array(date("H", $resaInfo["end_time"]), date("i", $resaInfo["end_time"])));
        }
        $packageChecked = $resaInfo["package_id"];

        $userCanEdit = $this->canUserEditReservation($id_space, $id_resource, $_SESSION["id_user"], $resaInfo["id"], $resaInfo["recipient_id"], $resaInfo["start_time"]);

        // create delete form
        $formDelete = new Form($this->request, "bookingeditreservationdefaultdeleteform");
        $formDelete->addComment(BookingTranslator::RemoveReservation($lang));
        $formDelete->addHidden("id_reservation", 0);

        $sendEmailWhenDelete = intval($modelCoreConfig->getParamSpace('BkBookingMailingDelete', $id_space));
        if ($sendEmailWhenDelete == 1) {
            $formDelete->addSelect("sendmail", BookingTranslator::SendEmailsToUsers($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), 1);
        } else {
            $formDelete->addHidden("sendmail", 0);
        }
        $formDelete->setValidationButton(CoreTranslator::Ok($lang), 'bookingeditreservationdefaultdelete/' . $id_space . "/" . $resaInfo["id"]);

        $BkUseRecurentBooking = $modelCoreConfig->getParamSpace("BkUseRecurentBooking", $id_space, 0);
        // periodicity information
        $modelCalEntry = new BkCalendarEntry();
        $id_period = $modelCalEntry->getPeriod($id_space, $resaInfo["id"]);
        $periodInfo = array();
        $periodInfo["choice"] = 1;
        $periodInfo["enddate"] = date("Y-m-d", $resaInfo["end_time"]);
        if ($id_period > 0) {
            $modelPeriod = new BkCalendarPeriod();
            $periodInfo = $modelPeriod->getPeriod($id_space, $id_period);
        }

        // create delete period form
        $formDeletePeriod = new Form($this->request, "bookingeditreservationdefaultdeleteform");
        $formDeletePeriod->addComment(BookingTranslator::RemoveReservationPeriodic($lang));
        $formDeletePeriod->setValidationButton(CoreTranslator::Ok($lang), 'bookingeditreservationperiodicdelete/' . $id_space . "/" . $id_period);

        $details = ['steps' => []];
        if($resaInfo["id"] > 0 && $resaInfo['responsible_id']) {
            try {
                $details = $modelCalEntry->computeDuration($id_space, $resaInfo);
            } catch(Exception $e) {
                Configuration::getLogger()->debug('[booking] failed to compute duration for entry', ['error' => $e->getMessage()]);
            }
        }

        $modelResource = new ResourceInfo();
        $modelScheduling = new BkScheduling();
        $schedul = $modelScheduling->getByReArea($id_space ,$modelResource->getAreaID($id_space, $id_resource));
        $forcePackages = $schedul['force_packages'] ?? 0;
        if($forcePackages) {
            $packageChecked = true;
        }


        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "menuData" => $menuData,
            "data" => ["booking" => $resaInfo],
            "form" => $form,
            "use_packages" => $use_packages,
            "forcePackages" => $forcePackages,
            "packageChecked" => $packageChecked,
            "userCanEdit" => $userCanEdit,
            "id_reservation" => $resaInfo["id"],
            "canEditReservation" => $canEditReservation,
            "formPackage" => $formPackage->getHtml($lang, false),
            "formEndDate" => $formEndDate->getHtml($lang, false),
            "usePeriodicBooking" => $BkUseRecurentBooking,
            "args" => $param,
            "periodInfo" => $periodInfo,
            "id_period" => $id_period,
            "formDelete" => $formDelete->getHtml($lang),
            "formDeletePeriod" => $formDeletePeriod->getHtml($lang),
            'from' => $this->request->getParameterNoException('from'),
            'details' => $details
        ),
            "addreservationAction"
        );
    }

    private function canBookForOthers($id_space, $id_user) {
        $modelSpace = new CoreSpace();
        $userRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        return ($userRole >= CoreSpace::$MANAGER);
    }

    private function editReservationInfo($id_space, $param) {
        $contentAction = explode("_", $param);
        $id = $contentAction[1];

        $modelCalEntry = new BkCalendarEntry();
        $entryInfo = $modelCalEntry->getEntry($id_space, $id);
        if(!$entryInfo) {
            throw new PfmParamException('entry not found');
        }

        $modelResource = new ResourceInfo();
        $_SESSION['bk_id_resource'] = $entryInfo["resource_id"];
        $_SESSION['bk_id_area'] = $modelResource->getAreaID($id_space, $entryInfo["resource_id"]);
        $_SESSION['bk_curentDate'] = date("Y-m-d", $entryInfo["start_time"]);

        return $entryInfo;
    }

    private function isNew($param) {
        $contentAction = explode("_", $param);
        if ($contentAction[0] == "t") {
            return true;
        }
        return false;
    }

    public function deleteperiodAction($id_space, $id_period){
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $modelCalEntry = new BkCalendarPeriod();
        $modelCalEntry->deleteAllPeriod($id_space, $id_period);

        $this->redirect("booking/" . $id_space);
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $sendEmail = intval($this->request->getParameter("sendmail"));
        $modelCalEntry = new BkCalendarEntry();
        $entryInfo = $modelCalEntry->getEntry($id_space, $id);
        if (!$entryInfo) {
            throw new PfmDbException("reservation not found", 404);
        }
        $id_resource = $entryInfo["resource_id"];
        $canEdit = $this->canUserEditReservation($id_space, $entryInfo['resource_id'], $_SESSION["id_user"], $id, $entryInfo['recipient_id'], $entryInfo['start_time']);
        if (!$canEdit) {
            throw new PfmAuthException("ERROR: You're not allowed to modify this reservation", 403);
        }
        if ($sendEmail == 1 && $entryInfo["start_time"] > time()) {
            $resourceModel = new ResourceInfo();
            $resourceName = $resourceModel->getName($id_space, $id_resource);
            $toAddress = $modelCalEntry->getEmailsBookerResource($id_space, $id_resource, time());
            $subject = $resourceName . " has been freed";
            $content = "The " . $resourceName . " has been freed from " . date(Constants::DATETIME_FORMAT, $entryInfo["start_time"]) . " to " . date(Constants::DATETIME_FORMAT, $entryInfo["end_time"]);

            // NEW MAIL SENDER
            $params = [
                "id_space" => $id_space,
                "subject" => $subject,
                "to" => $toAddress,
                "content" => $content
            ];
            $email = new Email();
            $email->sendEmailToSpaceMembers($params, $this->getLanguage(), mailing: "booking@$id_space");

            //Add user's name in resource managers email
            $modelConfig = new CoreConfig();
            $sendMailResponsibles = intval($modelConfig->getParamSpace("BkBookingMailingAdmins", $id_space));
            if ($sendMailResponsibles > 0) {
                $modelResp = new ReResps();
                $modelUser = new CoreUser();
                $params['to'] = $modelResp->getResourcesManagersEmails($id_space, $id_resource);
                $userName = $modelUser->getUserFUllName($_SESSION['id_user']);
                $params['content'] .= " by " . $userName;
                $email->sendEmailToSpaceMembers($params, $this->getLanguage(), mailing: "booking@$id_space");
            }
        }
        $modelCalEntry->removeEntry($id_space, $id);
        $this->redirect("booking/" . $id_space);
    }

}
