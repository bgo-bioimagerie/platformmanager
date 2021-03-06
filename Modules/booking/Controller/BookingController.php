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


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingController extends BookingabstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("booking");
    }

    public function navbar($id_space) {
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

    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);

        $id_area = $this->request->getParameterNoException("id_area");
        $id_resource = $this->request->getParameterNoException("id_resource");
        $curentDate = $this->request->getParameterNoException("curentDate");

        if ($id_area == "") {
            if (isset($_SESSION['bk_id_area'])) {
                $id_area = $_SESSION['bk_id_area'];
            }
        }
        if ($id_resource == "") {
            if (isset($_SESSION['bk_id_resource'])) {
                $id_resource = $_SESSION['bk_id_resource'];
            }
        }
        if ($id_resource == "") {
            if (isset($_SESSION['bk_curentDate'])) {
                $curentDate = $_SESSION['bk_curentDate'];
            }
        }
        //echo "index: id_area = " . $id_area . ", id_resource = " . "curentDate = " . $curentDate . "<br/>";
        $this->bookingAction($id_space, $id_area, $id_resource, $curentDate);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function bookingAction($id_space, $id_area, $id_resource) {

        $lang = $this->getLanguage();

        $curentDate = date("Y-m-d", time());
        if (isset($_SESSION['bk_curentDate'])) {
            if ($this->request->getParameterNoException("curentDate") != "") {
                $curentDate = CoreTranslator::dateToEn($this->request->getParameterNoException("curentDate"), $lang);
                $_SESSION['bk_curentDate'] = $curentDate;
            } else {
                $curentDate = $_SESSION['bk_curentDate'];
            }
        }

        //echo "curent date booking = " . $curentDate . "<br/>";
        $menuData = $this->calendarMenuData($id_space, $id_area, $id_resource, $curentDate);

        $modelResource = new ResourceInfo();
        $modelArea = New ReArea();
        $userSettingsModel = new CoreUserSettings();
        if ($id_resource == "" || $id_resource == 0) { // booking home page
            $calendarDefaultResource = $userSettingsModel->getUserSetting($_SESSION["id_user"], "calendarDefaultResource");
            if ($calendarDefaultResource != "") {
                $id_resource = $calendarDefaultResource;
                $id_area = $modelResource->getAreaID($id_resource);
                $id_site = $modelArea->getSiteID($id_area);
            } else {
                if ($id_area == "" || $id_area == 0) {
                    $modelArea = new ReArea();
                    if ($_SESSION["user_status"] < 2) {
                        $id_area = $modelArea->getSmallestUnrestrictedID();
                    } else {
                        $id_area = $modelArea->getSmallestID();
                    }
                    $id_site = $modelArea->getSiteID($id_area);
                }
                // get the resource with the smallest id
                $id_resource = $modelResource->firstResourceIDForArea($id_area);
            }
            //echo "id_area = " . $id_area . "</br>";
            //echo "id_resource = " . $id_resource . "</br>";
            //$menuData = $this->calendarMenuData($id_area, $id_resource, date("Y-m-d", time()));
            $_SESSION['bk_id_resource'] = $id_resource;
            $_SESSION['bk_id_area'] = $id_area;
            $_SESSION['bk_curentDate'] = $curentDate;
            //echo "booking: id_area = " . $id_area . ", id_resource = " . "curentDate = " . $curentDate . "<br/>";

            if ($id_resource == 0) {
                $this->render(array(
                    'id_space' => $id_space,
                    'menuData' => $menuData
                ));
                return;
            }
        }

        $calendarDefaultView = $userSettingsModel->getUserSetting($_SESSION["id_user"], "calendarDefaultView");
        if (isset($_SESSION['lastbookview'])) {
            $lastView = $_SESSION['lastbookview'];
            $this->redirect($lastView . "/" . $id_space);
        } else if ($calendarDefaultView == "") {
            $this->redirect("bookingdayarea/" . $id_space);
        } else {
            $this->redirect($calendarDefaultView . "/" . $id_space);
        }
    }

    public function book($id_space, $message) {
        $lastView = "";
        if (isset($_SESSION["user_settings"]["calendarDefaultView"])) {
            $lastView = $_SESSION["user_settings"]["calendarDefaultView"];
        }
        if (isset($_SESSION['lastbookview'])) {
            $lastView = $_SESSION['lastbookview'];
        }
        if ($lastView == "bookingday") {
            $this->dayAction($id_space, "", $message);
            return;
        } else if ($lastView == "bookingweek") {
            $this->weekAction($id_space, "", $message);
            return;
        } else if ($lastView == "bookingweekarea") {
            $this->weekareaAction($id_space, "", $message);
            return;
        } else if ($lastView == "bookingdayarea") {
            $this->dayareaAction($id_space, "", $message);
            return;
        }
        $this->dayAction($id_space, "", $message);
    }

    public function dayAction($id_space, $action, $message) {

        //print_r($_SESSION);
        $_SESSION['lastbookview'] = "bookingday";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        //echo "curent resource bookday 1 = " . $curentResource . "<br/>";

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentDate = $_SESSION['bk_curentDate'];
        }


        //print_r($_SESSION);
        //echo "curent resource bookday 2 = " . $curentResource . "<br/>";
        //sreturn;
        // change input if action
        if ($action == "daybefore") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime - 86400;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "dayafter") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime + 86400;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "today") {
            $curentDate = date("Y-m-d", time());
        }

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
        //print_r($menuData);

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }
        if (!$foundR) {
            $curentResource = $menuData["resources"][0]["id"];
            $_SESSION['bk_id_resource'] = $curentResource;
        }


        // save the menu info in the session
        //$_SESSION['bk_id_resource'] = $curentResource;
        //$_SESSION['bk_id_area'] = $curentAreaId;
        // $_SESSION['bk_id_site'] = $curentSiteId;
        $curentDate = $_SESSION['bk_curentDate'];

        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($curentResource);

        //if (count($resourceInfo) <= 1) {
        //    $this->redirect("calendar", "booking");
        //    return;
        //}

        $modelRes = new ResourceInfo();
        $resourceBase = $modelRes->get($curentResource);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        //echo "curent date line 470 = " . $curentDate . "<br/>";
        $dateArray = explode("-", $curentDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);
        $calEntries = $modelEntries->getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $curentResource);

        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getForSpace($id_space);

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        // get last state
        $modelEvent = new ReEvent();
        $resourceBase["last_state"] = $modelEvent->getLastStateColor($resourceBase["id"]);
        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);
        //print_r($agendaStyle);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'menuData' => $menuData,
            'message' => $message,
            'scheduling' => $scheduling,
            'resourceInfo' => $resourceInfo,
            'resourceBase' => $resourceBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'agendaStyle' => $agendaStyle
                ), "bookday");
    }

    public function dayareaAction($id_space, $action, $message) {
        $_SESSION['lastbookview'] = "bookingdayarea";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            if (isset($_SESSION['bk_id_resource'])) {
                $curentResource = $_SESSION['bk_id_resource'];
                $curentAreaId = $_SESSION['bk_id_area'];
                $curentDate = $_SESSION['bk_curentDate'];
            }
        }

        // change input if action
        if ($action == "daybefore") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime - 86400;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "dayafter") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime + 86400;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "today") {
            $curentDate = date("Y-m-d", time());
        }

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);
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

        // save the menu info in the session
        $_SESSION['bk_id_resource'] = $curentResource;
        $_SESSION['bk_id_area'] = $curentAreaId;
        $_SESSION['bk_curentDate'] = $curentDate;

        // get the area info
        //$modelArea = new ReArea();
        //$area = $modelArea->getArea($curentAreaId);
        // get the resource info
        $modelRes = new ResourceInfo();
        $modelAccess = new BkAccess();
        //echo "curentAreaId = " . $curentAreaId . "<br/>"; 
        $resourcesBase = $modelRes->resourcesForArea($curentAreaId);
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resourcesBase[$r]["accessibility_id"] = $modelAccess->getAccessId($resourcesBase[$r]["id"]);
        }

        // get last state
        $modelEvent = new ReEvent();
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resourcesBase[$r]["last_state"] = $modelEvent->getLastStateColor($resourcesBase[$r]["id"]);
        }

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $curentDate);
        //echo "curent date = " . $curentDate . "<br/>";
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2], $dateArray[0]);
        for ($t = 0; $t < count($resourcesBase); $t++) {
            $calEntries[] = $modelEntries->getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $resourcesBase[$t]["id"]);
        }

        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getForSpace($id_space);

        // isUserAuthorizedToBook
        foreach ($resourcesBase as $resourceBase) {
            //print_r($resourceBase);
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);
        }

        //print_r($calEntries);
        //return;
        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
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
            'message' => $message,
            'agendaStyle' => $agendaStyle
                ), "bookdayarea");
    }

    public function weekAction($id_space, $action, $message) {
        $_SESSION['lastbookview'] = "bookingweek";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentDate = $_SESSION['bk_curentDate'];
        }

        // change input if action
        if ($action == "dayweekbefore") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime - 86400 * 7;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "dayweekafter") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime + 86400 * 7;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "thisWeek") {
            $curentDate = date("Y-m-d", time());
        }

        // get the closest monday to curent day
        $i = 0;
        //echo "curentDate = " . $curentDate . "<br/>";
        $curentDateE = explode("-", $curentDate);
        while (date('D', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - $i, $curentDateE[0])) != "Mon") {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i), $curentDateE[0]));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i) + 6, $curentDateE[0]));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }
        if (!$foundR) {
            $curentResource = $menuData["resources"][0]["id"];
            $_SESSION['bk_id_resource'] = $curentResource;
        }
        // save the menu info in the session
        //$_SESSION['bk_id_resource'] = $curentResource;
        //$_SESSION['bk_id_area'] = $curentAreaId;
        //$_SESSION['bk_id_area'] = $curentAreaId;
        //$_SESSION['bk_curentDate'] = $curentDate;
        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($curentResource);

        if (count($resourceInfo) <= 1) {
            $this->redirect("booking");
            return;
        }

        $modelRes = new ResourceInfo();
        $resourceBase = $modelRes->get($curentResource);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + 7, $dateArray[0]);
        $calEntries = $modelEntries->getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $curentResource);

        //echo "Cal entry count = " . count($calEntries) . "</br>";
        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        // get last state
        $modelEvent = new ReEvent();
        $resourceBase["last_state"] = $modelEvent->getLastStateColor($resourceBase["id"]);

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'menuData' => $menuData,
            'resourceInfo' => $resourceInfo,
            'resourceBase' => $resourceBase,
            'date' => $curentDate,
            'date_unix' => $curentDateUnix,
            'mondayDate' => $mondayDate,
            'sundayDate' => $sundayDate,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'message' => $message,
            'agendaStyle' => $agendaStyle,
            'scheduling' => $scheduling
                ), "bookweek");
    }

    public function weekareaAction($id_space, $action, $message) {
        $_SESSION['lastbookview'] = "bookingweekarea";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentDate = $_SESSION['bk_curentDate'];
            //echo "curent area id " . $curentAreaId . "<br/>"; 
            //echo "curent date n-2 = " . $curentDate . "<br/>";
        }
        
        $modelArea = new ReArea();
        $areaSpace = $modelArea->getSpace($curentAreaId);
        if ($areaSpace != $id_space){
            $curentAreaId = $modelArea->getDefaultArea($id_space);
            $curentResource = 0;
        }
        

        //echo "curent date n-1= " . $curentDate . "<br/>";
        // change input if action
        if ($action == "dayweekbefore") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime - 86400 * 7;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "dayweekafter") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime + 86400 * 7;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "thisWeek") {
            $curentDate = date("Y-m-d", time());
        }

        //echo "curent date n = " . $curentDate . "<br/>";
        // get the closest monday to curent day
        $i = 0;
        $curentDateE = explode("-", $curentDate);
        while (date('D', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - $i, $curentDateE[0])) != "Mon") {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i), $curentDateE[0]));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i) + 6, $curentDateE[0]));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);


        // save the menu info in the session
        /*
          $_SESSION['id_resource'] = $curentResource;
          $_SESSION['id_area'] = $curentAreaId;
          $_SESSION['curentDate'] = $curentDate;
         */
        // get the area info
        $area = $modelArea->get($curentAreaId);

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->resourcesForArea($curentAreaId);

        // get last state
        $modelEvent = new ReEvent();
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resourcesBase[$r]["last_state"] = $modelEvent->getLastStateColor($resourcesBase[$r]["id"]);
        }

        $modelRescal = new ResourceInfo();
        for ($t = 0; $t < count($resourcesBase); $t++) {
            $resourcesInfo[$t] = $modelRescal->get($resourcesBase[$t]["id"]);
        }

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + 7, $dateArray[0]);
        $calEntries = $modelEntries->getEntriesForPeriodeAndArea($dateBegin, $dateEnd, $curentAreaId);

        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $isUserAuthorizedToBook = array();
        foreach ($resourcesBase as $resourceBase) {
            $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);
        }


        //echo "area id = "  . $curentAreaId . "</br>";
        //print_r($calEntries);
        //return;
        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
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
            'message' => $message,
            'agendaStyle' => $agendaStyle,
            'scheduling' => $scheduling
                ), "bookweekarea");
    }

    public function monthAction($id_space, $action, $message) {
        $_SESSION['lastbookview'] = "bookingmonth";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentDate = $_SESSION['bk_curentDate'];
        }

        // change input if action
        $curentTime = time();
        if ($action == "daymonthbefore") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime - 86400 * 30;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "daymonthafter") {
            $curentDate = explode("-", $curentDate);
            $curentTime = mktime(0, 0, 0, $curentDate[1], $curentDate[2], $curentDate[0]);
            $curentTime = $curentTime + 86400 * 30;
            $curentDate = date("Y-m-d", $curentTime);
        }
        if ($action == "thisMonth") {
            $curentDate = date("Y-m-d", time());
            $curentTime = time();
        }

        // get the closest monday to curent day
        $i = 0;
        //echo "curentDate = " . $curentDate . "<br/>";
        $curentDateE = explode("-", $curentDate);
        while (date('d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - $i, $curentDateE[0])) != 1) {
            $i++;
        }
        $mondayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i), $curentDateE[0]));
        $sundayDate = date('Y-m-d', mktime(0, 0, 0, $curentDateE[1], $curentDateE[2] - ($i) + 31, $curentDateE[0]));

        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);

        $foundR = false;
        foreach ($menuData["resources"] as $r) {
            if ($r["id"] == $curentResource) {
                $foundR = true;
                break;
            }
        }
        if (!$foundR) {
            $curentResource = $menuData["resources"][0]["id"];
            $_SESSION['bk_id_resource'] = $curentResource;
        }
        // save the menu info in the session
        //$_SESSION['id_resource'] = $curentResource;
        //$_SESSION['id_area'] = $curentAreaId;
        //$_SESSION['curentDate'] = $curentDate;
        // get the resource info
        $modelRescal = new ResourceInfo();
        $resourceInfo = $modelRescal->get($curentResource);

        if (count($resourceInfo) <= 1) {
            $this->redirect("booking");
            return;
        }

        $modelRes = new ResourceInfo();
        $resourceBase = $modelRes->get($curentResource);

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $mondayDate);
        $dateBegin = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $dateEnd = mktime(23, 59, 59, $dateArray[1], $dateArray[2] + 31, $dateArray[0]);
        $calEntries = $modelEntries->getEntriesForPeriodeAndResource($dateBegin, $dateEnd, $curentResource);

        //echo "Cal entry count = " . count($calEntries) . "</br>";
        // curentdate unix
        $temp = explode("-", $curentDate);
        $curentDateUnix = mktime(0, 0, 0, $temp[1], $temp[2], $temp[0]);

        // color code
        $modelColor = new BkColorCode();
        $colorcodes = $modelColor->getColorCodes($id_space, "name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $id_space, $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);

        // view
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'menuData' => $menuData,
            'resourceInfo' => $resourceInfo,
            'resourceBase' => $resourceBase,
            'date' => $curentDate,
            'month' => date("n", $curentTime),
            'year' => date("Y", $curentTime),
            'date_unix' => $curentDateUnix,
            'mondayDate' => $mondayDate,
            'sundayDate' => $sundayDate,
            'calEntries' => $calEntries,
            'colorcodes' => $colorcodes,
            'isUserAuthorizedToBook' => $isUserAuthorizedToBook,
            'message' => $message,
            'agendaStyle' => $agendaStyle
                ), "bookmonth");
    }

    public function editreservationAction($id_space, $param) {

        $modelSettings = new CoreConfig();
        $editResaFunction = $modelSettings->getParamSpace("bkReservationPlugin", $id_space);
        
        //echo "editResaFunction = " . $editResaFunction . "<br/>";
        
        if ($editResaFunction == "" || $editResaFunction == "bookingeditreservationdefault") {
            $modelDefault = new BookingdefaultController($this->request);
            //$modelDefault->setRequest($this->request);
            $modelDefault->editreservationdefault($id_space, $param);
            return;
        } else {

            /// todo run plugin
            $modelCache = new FCache();
            $pathInfo = $modelCache->getURLInfos($editResaFunction);
            //print_r($pathInfo);
            $path = $this->request->getParameter('path');
            //echo "path = " . $path . "<br/>";
            $pathData = explode("/", $path);
        
            $urlInfo = array("pathData" => $pathData, "pathInfo" => $pathInfo);

            //print_r($urlInfo);
            $controllerName = $urlInfo["pathInfo"]["controller"];
            $classController = ucfirst(strtolower($controllerName)) . "Controller";
            $module = $urlInfo["pathInfo"]["module"];
            $fileController = 'Modules/' . $module . "/Controller/" . $classController . ".php";
            if (file_exists($fileController)) {
                // Instantiate controler
                require_once ($fileController);
                $controller = new $classController ($this->request);
                //$controller->setRequest($this->request);

                $action = $urlInfo["pathInfo"]["action"];
                //echo "action = " . $action . "<br/>";
                //echo 'url info = ';
                //print_r($urlInfo);
                //echo '<br/>';
                $args = $this->getArgs($urlInfo);
                //echo "args = "; print_r($args); echo "<br/>";

                $controller->runAction($urlInfo["pathInfo"]["module"], $action, $args);
                return;
            } else {
                throw new Exception("Unable to find the controller file '$fileController' ");
            }
        }
    }

    private function getArgs($urlInfo) {

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
