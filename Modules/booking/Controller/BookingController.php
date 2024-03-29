<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/booking/Controller/BookingabstractController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Controller/BookingdefaultController.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReEvent.php';

require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';

require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingController extends BookingabstractController
{
    public function navbar($id_space)
    {
        $html = file_get_contents('Modules/booking/View/Booking/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Calendar_View}}', BookingTranslator::Calendar_View($lang), $html);
        $html = str_replace('{{Scheduling}}', BookingTranslator::Scheduling($lang), $html);
        $html = str_replace('{{Display}}', BookingTranslator::Display($lang), $html);
        $html = str_replace('{{Accessibilities}}', BookingTranslator::Accessibilities($lang), $html);
        $html = str_replace('{{Nightwe}}', BookingTranslator::Nightwe($lang), $html);
        $html = str_replace('{{Color_codes}}', BookingTranslator::Color_codes($lang), $html);
        $html = str_replace('{{Additional_info}}', BookingTranslator::Additional_info($lang), $html);
        $html = str_replace('{{SupplementariesInfo}}', BookingTranslator::SupplementariesInfo($lang), $html);
        $html = str_replace('{{Packages}}', BookingTranslator::Packages($lang), $html);
        $html = str_replace('{{Quantities}}', BookingTranslator::Quantities($lang), $html);
        $html = str_replace('{{booking}}', BookingTranslator::booking($lang), $html);
        $html = str_replace('{{Block_Resouces}}', BookingTranslator::Block_Resouces($lang), $html);
        $html = str_replace('{{Restrictions}}', BookingTranslator::Restrictions($lang), $html);



        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("bookingsettings", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', BookingTranslator::Booking($lang), $html);

        return $html;
    }

    public function journalAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $m = new BkCalendarEntry();
        $bookings = $m->journal($id_space, $_SESSION['id_user'], 100);
        return $this->render(['data' => ['bookings' => $bookings]]);
    }

    public function futureAction($id_space, $id_resource)
    {
        if (!isset($_SESSION['id_user']) || !$_SESSION['id_user']) {
            throw new PfmAuthException('need login', 403);
        }
        $modelBooking = new BkCalendarEntry();
        $bookings = $modelBooking->getUserFutureBookings($id_space, $_SESSION["id_user"], $id_resource);
        $this->render(['data' => ['bookings' => $bookings]]);
    }

    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);

        $id_area = $this->request->getParameterNoException("id_area");
        $id_resource = $this->request->getParameterNoException("id_resource");
        $curentDate = $this->request->getParameterNoException("curentDate");

        $this->bookingAction($id_space, $id_area, $id_resource, $curentDate);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function bookingAction($id_space, $id_area, $id_resource)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $curentDate = date("Y-m-d", time());
        if ($this->request->getParameterNoException("curentDate") != "") {
            $curentDate = CoreTranslator::dateToEn($this->request->getParameterNoException("curentDate"), $lang);
        }

        $modelResource = new ResourceInfo();
        $userSettingsModel = new CoreUserSettings();
        if ($id_resource == "" || $id_resource == 0) { // booking home page
            $calendarDefaultResource = $userSettingsModel->getUserSetting($_SESSION["id_user"], "calendarDefaultResource");
            if ($calendarDefaultResource != "") {
                $id_resource = $calendarDefaultResource;
                $id_area = $modelResource->getAreaID($id_space, $id_resource);
            } else {
                if ($id_area == "" || $id_area == 0) {
                    $modelArea = new ReArea();
                    if ($_SESSION["user_status"] < 2) {
                        $id_area = $modelArea->getSmallestUnrestrictedID($id_space);
                    } else {
                        $id_area = $modelArea->getSmallestID($id_space);
                    }
                }
                // get the resource with the smallest id
                $id_resource = $modelResource->firstResourceIDForArea($id_space, $id_area);
            }
        }

        $bk_id_resource = $this->request->getParameterNoException("id_resource", default: $id_resource);
        $bk_id_area = $this->request->getParameterNoException("id_area", default: $id_area);
        $id_user = $this->request->getParameterNoException('id_user');


        $modelCoreConfig = new CoreConfig();


        $bkUserDefaultViewType = $this->request->getParameterNoException('view');
        if (!$bkUserDefaultViewType) {
            $bkUserDefaultViewType = $userSettingsModel->getUserSetting($_SESSION["id_user"], "BkDefaultViewType");
        }
        if (!$bkUserDefaultViewType) {
            $bkUserDefaultViewType = $modelCoreConfig->getParamSpace("BkDefaultViewType", $id_space, "simple");
        }

        $qc = [
            "bk_curentDate" => $curentDate,
            "bk_id_resource" => $bk_id_resource,
            "bk_id_area" => $bk_id_area,
            "id_user" => $id_user,
            "view" => $bkUserDefaultViewType
        ];


        $bkUserDefaultView = $userSettingsModel->getUserSetting($_SESSION["id_user"], "calendarDefaultView");
        if (isset($_SESSION['lastbookview'])) {
            $lastView = $_SESSION['lastbookview'];
            $this->redirect($lastView . "/" . $id_space, $qc);
        } elseif ($bkUserDefaultView == "") {
            $bkSpaceDefaultView = $modelCoreConfig->getParamSpace("BkSetDefaultView", $id_space, "bookingweekarea");
            $this->redirect($bkSpaceDefaultView . "/" . $id_space, $qc);
        } else {
            $this->redirect($bkUserDefaultView . "/" . $id_space, $qc);
        }
    }


    /**
     * @deprecated, used by tests only?
     */
    public function book($id_space)
    {
        $lastView = "";
        if (isset($_SESSION["user_settings"]["calendarDefaultView"])) {
            $lastView = $_SESSION["user_settings"]["calendarDefaultView"];
        }
        if (isset($_SESSION['lastbookview'])) {
            $lastView = $_SESSION['lastbookview'];
        }
        switch($lastView) {
            case "bookingday":
                $this->dayAction($id_space);
                break;
            case "bookingweek":
                $this->weekAction($id_space);
                break;
            case "bookingdayarea":
                $this->dayareaAction($id_space);
                break;
            case "bookingweekarea":
                $this->weekareaAction($id_space);
                break;
            default:
                $this->dayAction($id_space);
        }
    }

    public function dayAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $_SESSION['lastbookview'] = "bookingday";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        $id_user = $this->request->getParameterNoException('id_user');
        if ($id_user && $this->role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if (!$curentDate) {
            $curentDate = date('Y-m-d');
        }

        $tmpDate = explode("-", $curentDate);
        $tmpTime = mktime(0, 0, 0, intval($tmpDate[1]), intval($tmpDate[2]), intval($tmpDate[0]));
        $beforeTime = $tmpTime - 86400;
        $beforeDate = date("Y-m-d", $beforeTime);
        $afterTime = $tmpTime + 86400;
        $afterDate = date("Y-m-d", $afterTime);

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        $curentAreaId = $menuData['curentAreaId'];

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }

        // Setting an error message if no resource exists
        if (empty($menuData["resources"])) {
            $_SESSION["flash"] = BookingTranslator::noBookingArea($lang);
            $_SESSION["flashClass"] = "danger";
        }

        if (!$foundR && !empty($menuData["resources"])) {
            $curentResource = $menuData["resources"][0]["id"];
        }

        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($id_space, $curentResource);

        $modelRes = new ResourceInfo();
        $resourceBase = $modelRes->get($id_space, $curentResource);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $curentDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);


        $mschedule = new BkScheduling();
        $schedule= $mschedule->getByReArea($id_space, $curentAreaId);
        if ($schedule['shared']) {
            $modelRes = new ResourceInfo();
            $resourcesBase = $modelRes->resourcesForArea($id_space, $curentAreaId);

            $resIds = [];
            for ($r = 0; $r < count($resourcesBase); $r++) {
                $resIds[] = $resourcesBase[$r]["id"];
            }
            $cals = $modelEntries->getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, $resIds, $id_user);
            $calmap = [];
            $calEntries = [];

            foreach ($cals as $cal) {
                $calmap[$cal['resource_id']][] = $cal;
            }

            foreach ($cals as $cal) {
                if ($cal['resource_id'] != $curentResource) {
                    $cal['id'] = 0;
                }
                $calEntries[] = $cal;
            }
        } else {
            $calEntries = $modelEntries->getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $curentResource, $id_user);
        }

        // curentdate unix
        $curentDate = (!$curentDate || $curentDate == "") ? date("Y-m-d") : $curentDate;
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getForSpace($id_space);

        $isUserAuthorizedToBook = false;
        if ($resourceBase) {
            // isUserAuthorizedToBook
            $modelAccess = new BkAccess();
            $resourceBase["accessibility_id"] = $modelAccess->getAccessId($id_space, $resourceBase["id"]);
            $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $curentDateUnix);
            // get last state
            $modelEvent = new ReEvent();
            $resourceBase["last_state"] = $modelEvent->getLastStateColor($id_space, $resourceBase["id"]);
        }
        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($id_space, $curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->getByReArea($id_space, $curentAreaId);


        $u = new CoreSpaceUser();
        $user = $u->getUserSpaceInfo2($id_space, $_SESSION['id_user']);
        if ($user === false) {
            $user = [];
        }

        $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], [$user]);
        if ($this->role > CoreSpace::$USER) {
            $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], $u->getUsersOfSpaceByLetter($id_space, '', 1));
        }

        $detailedViewRequest = $this->request->getParameterNoException('view');
        $detailedView = true;
        if ($detailedViewRequest == 'simple') {
            $detailedView = false;
        }


        // view
        return $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'menuData' => $menuData,
            'scheduling' => $scheduling,
            'resourceInfo' => $resourceInfo,
            'resourceBase' => $resourceBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle,
            'afterDate' => $afterDate,
            'beforeDate' => $beforeDate,
            'bk_id_resource' => $curentResource,
            'bk_id_area' => $curentAreaId,
            'users' => $users,
            'id_user' => $id_user,
            'detailedView' => $detailedView,
            'data' => ['bookings' => $calEntries]
        ), "bookday");
    }

    public function dayareaAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $_SESSION['lastbookview'] = "bookingdayarea";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        $id_user = $this->request->getParameterNoException('id_user');
        if ($id_user && $this->role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        } else {
            // set a default value to currentDate to today => avoids mkTime() errors
            $curentDate = date("Y-m-d");
        }

        $tmpDate = explode("-", $curentDate);
        $tmpTime = mktime(0, 0, 0, intval($tmpDate[1]), intval($tmpDate[2]), intval($tmpDate[0]));
        $beforeTime = $tmpTime - 86400;
        $beforeDate = date("Y-m-d", $beforeTime);
        $afterTime = $tmpTime + 86400;
        $afterDate = date("Y-m-d", $afterTime);


        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        $curentAreaId = $menuData['curentAreaId'];

        $foundA = false;
        foreach ($menuData["areas"] as $are) {
            if ($curentAreaId == $are["id"]) {
                $foundA = true;
                break;
            }
        }
        if (!$foundA) {
            $curentAreaId = $menuData["areas"][0]["id"];
        }

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->resourcesForArea($id_space, $curentAreaId);
        $curentAreaId = $menuData['curentAreaId'];

        $resIds = [];
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resIds[] = $resourcesBase[$r]["id"];
        }


        $modelEvent = new ReEvent();
        $colors =$modelEvent->getLastStateColors($id_space, $resIds);
        $cmap = [];
        foreach ($colors as $c) {
            if (!key_exists($c['id_resource'], $cmap)) {
                $cmap[$c['id_resource']] = $c['color'];
            }
        }

        $modelAccess = new BkAccess();
        $accessIds = $modelAccess->getAccessIds($id_space, $resIds);
        $amap = [];
        foreach ($accessIds as $a) {
            $amap[$a['id_resource']] = $a['id_access'];
        }

        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resourcesBase[$r]["accessibility_id"] = $amap[$resourcesBase[$r]["id"]] ?? "";
            $resourcesBase[$r]["last_state"] = $cmap[$resourcesBase[$r]["id"]] ?? "";
        }


        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $curentDate);

        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);

        $cals = $modelEntries->getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, $resIds, $id_user);
        $calmap = [];
        $calEntries = [];
        foreach ($resourcesBase as $r) {
            $calEntries[] = [];
        }
        foreach ($cals as $cal) {
            $calmap[$cal['resource_id']][] = $cal;
        }

        $mschedule = new BkScheduling();
        $schedule= $mschedule->getByReArea($id_space, $curentAreaId);
        if ($schedule['shared']) {
            foreach ($resourcesBase as $i => $r) {
                foreach ($cals as $cal) {
                    if ($cal['resource_id'] != $r['id']) {
                        $cal['id'] = 0;
                    }
                    $calEntries[$i][] = $cal;
                }
            }
        } else {
            foreach ($resourcesBase as $i => $r) {
                $calEntries[$i] = $calmap[$r['id']] ?? [];
            }
        }

        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getForSpace($id_space);

        $isUserAuthorizedToBook = [];
        // isUserAuthorizedToBook
        foreach ($resourcesBase as $resourceBase) {
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $curentDateUnix);
        }

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($id_space, $curentAreaId);
        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->getByReArea($id_space, $curentAreaId);
        // Setting an error message if no resource exists
        if (empty($resourcesBase)) {
            $_SESSION["flash"] = BookingTranslator::noBookingArea($lang);
            $_SESSION["flashClass"] = "danger";
        }

        $u = new CoreSpaceUser();
        $user = $u->getUserSpaceInfo2($id_space, $_SESSION['id_user']);
        if ($user === false) {
            $user = [];
        }

        $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], [$user]);
        if ($this->role > CoreSpace::$USER) {
            $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], $u->getUsersOfSpaceByLetter($id_space, '', 1));
        }

        $detailedViewRequest = $this->request->getParameterNoException('view');
        $detailedView = true;
        if ($detailedViewRequest == 'simple') {
            $detailedView = false;
        }

        // view
        return $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'menuData' => $menuData,
            'scheduling' => $scheduling,
            'resourcesBase' => $resourcesBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle,
            'afterDate' => $afterDate,
            'beforeDate' => $beforeDate,
            'bk_id_resource' => $curentResource,
            'bk_id_area' => $curentAreaId,
            'users' => $users,
            'id_user' => $id_user,
            'detailedView' => $detailedView,
            'data' => ['bookings' => $calEntries]
        ), "bookdayarea");
    }

    public function weekAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $_SESSION['lastbookview'] = "bookingweek";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        $id_user = $this->request->getParameterNoException('id_user');
        if ($id_user && $this->role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        } else {
            // set a default value to currentDate to today => avoids mkTime() errors
            $curentDate = date("Y-m-d");
        }

        $tmpDate = explode("-", $curentDate);
        $tmpTime = mktime(0, 0, 0, intval($tmpDate[1]), intval($tmpDate[2]), intval($tmpDate[0]));
        $beforeTime = $tmpTime - (86400 * 7);
        $beforeDate = date("Y-m-d", $beforeTime);
        $afterTime = $tmpTime + (86400 * 7);
        $afterDate = date("Y-m-d", $afterTime);


        // get the closest monday to curent day
        $i = 0;
        $curentDate = (!$curentDate || $curentDate == "") ? date("Y-m-d") : $curentDate;
        $curentDateE = explode("-", $curentDate);
        while (date('D', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - $i, intval($curentDateE[0]))) != "Mon") {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i), intval($curentDateE[0])));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i) + 6, intval($curentDateE[0])));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        $curentAreaId = $menuData['curentAreaId'];

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }
        if (!$foundR) {
            if (empty($menuData["resources"])) {
                $curentResource = 0;
            } else {
                $curentResource = $menuData["resources"][0]["id"];
            }
        }

        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($id_space, $curentResource);

        // Setting an error message if no resource exists
        if (!$resourceInfo) {
            $_SESSION["flash"] = BookingTranslator::noBookingArea($lang);
            $_SESSION["flashClass"] = "danger";
        }

        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->get($id_space, $curentResource);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + 7, $dateArray[0]);
        //$calEntries = $modelEntries->getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $curentResource, $id_user);

        $mschedule = new BkScheduling();
        $schedule= $mschedule->getByReArea($id_space, $curentAreaId);
        if ($schedule['shared']) {
            $modelRes = new ResourceInfo();
            $resourcesBaseShared = $modelRes->resourcesForArea($id_space, $curentAreaId);

            $resIds = [];
            for ($r = 0; $r < count($resourcesBaseShared); $r++) {
                $resIds[] = $resourcesBaseShared[$r]["id"];
            }
            $cals = $modelEntries->getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, $resIds, $id_user);
            $calmap = [];
            $calEntries = [];

            foreach ($cals as $cal) {
                $calmap[$cal['resource_id']][] = $cal;
            }

            foreach ($cals as $cal) {
                if ($cal['resource_id'] != $curentResource) {
                    $cal['id'] = 0;
                }
                $calEntries[] = $cal;
            }
        } else {
            $calEntries = $modelEntries->getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $curentResource, $id_user);
        }




        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        $isUserAuthorizedToBook = false;
        if ($resourcesBase) {
            // isUserAuthorizedToBook
            $modelAccess = new BkAccess();
            $resourcesBase["accessibility_id"] = $modelAccess->getAccessId($id_space, $resourcesBase["id"]);
            $isUserAuthorizedToBook = $this->hasAuthorization($resourcesBase["id_category"], $resourcesBase["accessibility_id"], $id_space, $_SESSION['id_user'], 0);

            // get last state
            $modelEvent = new ReEvent();
            $resourcesBase["last_state"] = $modelEvent->getLastStateColor($id_space, $resourcesBase["id"]);
        }

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($id_space, $curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->getByReArea($id_space, $curentAreaId);

        $u = new CoreSpaceUser();
        $user = $u->getUserSpaceInfo2($id_space, $_SESSION['id_user']);
        if ($user === false) {
            $user = [];
        }

        $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], [$user]);
        if ($this->role > CoreSpace::$USER) {
            $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], $u->getUsersOfSpaceByLetter($id_space, '', 1));
        }

        $detailedViewRequest = $this->request->getParameterNoException('view');
        $detailedView = true;
        if ($detailedViewRequest == 'simple') {
            $detailedView = false;
        }

        // view
        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'menuData' => $menuData,
            'resourceInfo' => $resourceInfo,
            'resourceBase' => $resourcesBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'mondayDate' => $mondayDate,
            'sundayDate' => $sundayDate,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle,
            'scheduling' => $scheduling,
            'afterDate' => $afterDate,
            'beforeDate' => $beforeDate,
            'bk_id_resource' => $curentResource,
            'bk_id_area' => $curentAreaId,
            'users' => $users,
            'id_user' => $id_user,
            'detailedView' => $detailedView,
            'data' => ['bookings' => $calEntries]
        ), "bookweek");
    }

    public function weekareaAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $_SESSION['lastbookview'] = "bookingweekarea";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        $id_user = $this->request->getParameterNoException('id_user');
        if ($id_user && $this->role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        } else {
            // set a default value to currentDate to today => avoids mkTime() errors
            $curentDate = date("Y-m-d");
        }

        $modelArea = new ReArea();
        $areaSpace = $modelArea->getSpace($curentAreaId);
        if ($areaSpace != $id_space) {
            $curentAreaId = $modelArea->getDefaultArea($id_space);
            $curentResource = 0;
        }

        $tmpDate = explode("-", $curentDate);
        $tmpTime = mktime(0, 0, 0, intval($tmpDate[1]), intval($tmpDate[2]), intval($tmpDate[0]));
        $beforeTime = $tmpTime - (86400 * 7);
        $beforeDate = date("Y-m-d", $beforeTime);
        $afterTime = $tmpTime + (86400 * 7);
        $afterDate = date("Y-m-d", $afterTime);

        $i = 0;
        $curentDate = (!$curentDate || $curentDate == "") ? date("Y-m-d") : $curentDate;
        $curentDateE = explode("-", $curentDate);
        while (date('D', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - $i, intval($curentDateE[0]))) != "Mon") {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i), intval($curentDateE[0])));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i) + 6, intval($curentDateE[0])));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        $curentAreaId = $menuData['curentAreaId'];

        // get the area info
        $area = $modelArea->get($id_space, $curentAreaId);

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->resourcesForArea($id_space, $curentAreaId);

        // get last state
        $modelEvent = new ReEvent();
        $resIds = [];
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resIds[] = $resourcesBase[$r]["id"];
        }
        $colors =$modelEvent->getLastStateColors($id_space, $resIds);
        $cmap = [];
        foreach ($colors as $c) {
            if (!key_exists($c['id_resource'], $cmap)) {
                $cmap[$c['id_resource']] = $c['color'];
            }
        }

        $modelRescal = new ResourceInfo();
        $resources  = $modelRescal->getBySpace($id_space);
        $rmap = [];
        foreach ($resources as $r) {
            $rmap[$r['id']] = $r;
        }
        for ($t = 0; $t < count($resourcesBase); $t++) {
            $resourceInfo[$t] = $rmap[$resourcesBase[$t]["id"]];
            $resourcesBase[$t]['last_state'] = '';
            if (array_key_exists($resourcesBase[$t]['id'], $cmap)) {
                $resourcesBase[$t]['last_state'] = $cmap[$resourcesBase[$t]['id']];
            }
        }

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + 7, $dateArray[0]);
        //$calEntries = $modelEntries->getEntriesForPeriodeAndArea($id_space, $dateBegin, $dateEnd, $curentAreaId, $id_user);

        $cals = $modelEntries->getEntriesForPeriodeAndResources($id_space, $dateBegin, $dateEnd, $resIds, $id_user);
        $calmap = [];
        $calEntries = [];
        foreach ($resourcesBase as $r) {
            $calEntries[] = [];
        }
        foreach ($cals as $cal) {
            $calmap[$cal['resource_id']][] = $cal;
        }

        $mschedule = new BkScheduling();
        $schedule= $mschedule->getByReArea($id_space, $curentAreaId);
        if ($schedule['shared']) {
            foreach ($resourcesBase as $i => $r) {
                foreach ($cals as $cal) {
                    if ($cal['resource_id'] != $r['id']) {
                        $cal['id'] = 0;
                    }
                    $calEntries[$i][] = $cal;
                }
            }
        } else {
            foreach ($resourcesBase as $i => $r) {
                $calEntries[$i] = $calmap[$r['id']] ?? [];
            }
        }

        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $isUserAuthorizedToBook = array();
        $accessIds = $modelAccess->getAccessIds($id_space, $resIds);
        $amap = [];
        foreach ($accessIds as $a) {
            $amap[$a['id_resource']] = $a['id_access'];
        }

        $isUserAuthorizedToBook = [];
        foreach ($resourcesBase as $resourceBase) {
            $resourceBase["accessibility_id"] = $amap[$resourceBase["id"]] ?? null;
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], 0);
        }

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($id_space, $curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->getByReArea($id_space, $curentAreaId);

        // Setting an error message if no resource exists
        if (empty($resourcesBase)) {
            $_SESSION["flash"] = BookingTranslator::noBookingArea($lang);
            $_SESSION["flashClass"] = "danger";
        }

        $u = new CoreSpaceUser();
        $user = $u->getUserSpaceInfo2($id_space, $_SESSION['id_user']);
        if ($user === false) {
            $user = [];
        }

        $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], [$user]);
        if ($this->role > CoreSpace::$USER) {
            $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], $u->getUsersOfSpaceByLetter($id_space, '', 1));
        }

        $detailedViewRequest = $this->request->getParameterNoException('view');
        $detailedView = false;
        if ($detailedViewRequest == 'detailed') {
            $detailedView = true;
        }

        // view
        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'menuData' => $menuData,
            'areaname' => $area["name"],
            'resourcesInfo' => $resourcesBase,
            'resourcesBase' => $resourcesBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'mondayDate' => $mondayDate,
            'sundayDate' => $sundayDate,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle,
            'scheduling' => $scheduling,
            'afterDate' => $afterDate,
            'beforeDate' => $beforeDate,
            'bk_id_resource' => $curentResource,
            'bk_id_area' => $curentAreaId,
            'users' => $users,
            'id_user' => $id_user,
            'detailedView' => $detailedView,
            'data' => ['bookings' => $calEntries]
        ), "bookweekarea");
    }

    public function monthAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $_SESSION['lastbookview'] = "bookingmonth";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        $id_user = $this->request->getParameterNoException('id_user');
        if ($id_user && $this->role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        } else {
            // set a default value to currentDate to today => avoids mkTime() errors
            $curentDate = date("Y-m-d");
        }

        $tmpDate = explode("-", $curentDate);
        $tmpTime = mktime(0, 0, 0, intval($tmpDate[1]), intval($tmpDate[2]), intval($tmpDate[0]));
        $beforeTime = $tmpTime - (86400 * 30);
        $beforeDate = date("Y-m-d", $beforeTime);
        $afterTime = $tmpTime + (86400 * 30);
        $afterDate = date("Y-m-d", $afterTime);

        // get the closest monday to curent day
        $i = 0;
        $curentDate = (!$curentDate || $curentDate == "") ? date("Y-m-d") : $curentDate;
        $curentDateE = explode("-", $curentDate);
        while (date('d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - $i, intval($curentDateE[0]))) != 1) {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i), intval($curentDateE[0])));
        $nbdays = date('t', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i), intval($curentDateE[0])));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, intval($curentDateE[1]), intval($curentDateE[2]) - ($i) + $nbdays, intval($curentDateE[0])));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        $curentAreaId = $menuData['curentAreaId'];

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }
        if (!$foundR) {
            $curentResource = $menuData["resources"][0]["id"];
        }

        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($id_space, $curentResource);

        // Setting an error message if no resource exists
        if (!$resourceInfo) {
            $_SESSION["flash"] = BookingTranslator::noBookingArea($lang);
            $_SESSION["flashClass"] = "danger";
        }

        $resourcesBase = $resourceInfo;
        $modelEvent = new ReEvent();
        $resourcesBase["last_state"] = $modelEvent->getLastStateColor($id_space, $resourcesBase["id"]);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + $nbdays, $dateArray[0]);
        $calEntries = $modelEntries->getEntriesForPeriodeAndResource($id_space, $dateBegin, $dateEnd, $curentResource, $id_user);
        //echo "Cal entry count = " . count($calEntries) . "</br>";
        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourcesBase["accessibility_id"] = $modelAccess->getAccessId($id_space, $resourcesBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourcesBase["id_category"], $resourcesBase["accessibility_id"], $id_space, $_SESSION['id_user'], 0);

        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($id_space, $curentAreaId);

        $u = new CoreSpaceUser();
        $user = $u->getUserSpaceInfo2($id_space, $_SESSION['id_user']);
        if ($user === false) {
            $user = [];
        }

        $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], [$user]);
        if ($this->role > CoreSpace::$USER) {
            $users = array_merge([['id' => 0, 'login' => '', 'name' => 'all', 'firstname' => '']], $u->getUsersOfSpaceByLetter($id_space, '', 1));
        }

        // view
        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'menuData' => $menuData,
            'resourceInfo' => $resourceInfo,
            'resourcesBase' => $resourcesBase,
            'date' => $curentDate,
            'month' => date("n", $tmpTime),
            'year' => date("Y", $tmpTime),
            'date_unix' => $curentDateUnix,
            'mondayDate' => $mondayDate,
            'sundayDate' => $sundayDate,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle,
            'afterDate' => $afterDate,
            'beforeDate' => $beforeDate,
            'bk_id_resource' => $curentResource,
            'bk_id_area' => $curentAreaId,
            'users' => $users,
            'id_user' => $id_user,
            'detailedView' => false,
            'data' => ['bookings' => $calEntries]
        ), "bookmonth");
    }

    public function editreservationAction($id_space, $param)
    {
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $modelSettings = new CoreConfig();
        $editResaFunction = $modelSettings->getParamSpace("bkReservationPlugin", $id_space);

        if ($editResaFunction == "" || $editResaFunction == "bookingeditreservationdefault") {
            $modelDefault = new BookingdefaultController($this->request, $this->currentSpace);
            //$modelDefault->setArgs(['id_space' => $id_space, 'param' => $param]);
            return $modelDefault->editreservationdefault($id_space, $param);
        } else {
            Configuration::getLogger()->warning("[booking][plugin=$editResaFunction] booking plugins will be deprecated");
            // run plugin
            // deprecated and uses only old routing way
            $modelCache = new FCache();
            $pathInfo = $modelCache->getURLInfos($editResaFunction);
            $path = $this->request->getParameter('path');
            $pathData = explode("/", $path);

            $urlInfo = array("pathData" => $pathData, "pathInfo" => $pathInfo);

            $controllerName = $urlInfo["pathInfo"]["controller"];
            $classController = ucfirst(strtolower($controllerName)) . "Controller";
            $module = $urlInfo["pathInfo"]["module"];
            $fileController = 'Modules/' . $module . "/Controller/" . $classController . ".php";
            if (file_exists($fileController)) {
                // Instantiate controler
                require_once($fileController);
                $controller = new $classController($this->request, $this->currentSpace);
                $action = $urlInfo["pathInfo"]["action"];
                $args = $this->getArgs($urlInfo);

                return $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
            } else {
                throw new PfmException("Unable to find the controller file '$fileController' ", 404);
            }
        }
    }

    private function getArgs($urlInfo)
    {
        $args = $urlInfo["pathInfo"]["gets"];
        $argsValues = array();

        for ($i = 0; $i < count($args); $i++) {
            if (isset($urlInfo["pathData"][$i + 1])) {
                $argsValues[$args[$i]["name"]] = $urlInfo["pathData"][$i + 1];
            } else {
                $argsValues[$args[$i]["name"]] = "";
            }
        }

        return $argsValues;
    }
}
