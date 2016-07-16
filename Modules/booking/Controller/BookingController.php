<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Controller/BookingdefaultController.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';

require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreProject.php';

require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("booking");
    }

    public function indexAction() {

        $id_site = $this->request->getParameterNoException("id_site");
        $id_area = $this->request->getParameterNoException("id_area");
        $id_resource = $this->request->getParameterNoException("id_resource");
        $curentDate = $this->request->getParameterNoException("curentDate");

        $this->bookingAction($id_site, $id_area, $id_resource, $curentDate);
    }

    /**
     * Get the content of of the booking menu for the calendar pages
     * @param number $curentAreaId ID of the curent area
     * @param number $curentResourceId ID of the current resource
     * @param date $curentDate Curent date
     * @return array: booking menu content
     */
    public function calendarMenuData($curentSiteId, $curentAreaId, $curentResourceId, $curentDate) {

        /*
          echo "curentSiteId = " . $curentSiteId . "<br/>";
          echo "curentAreaId = " . $curentAreaId . "<br/>";
          echo "curentResourceId = " . $curentResourceId . "<br/>";
          echo "curentDate = " . $curentDate . "<br/>";
         */

        if ($curentDate == "") {
            $curentDate = date("Y-m-d", time());
        }

        $modelSites = new EcSite();
        $sites = $modelSites->getAll("name");
        if ($curentSiteId == "" || $curentSiteId == 0) {
            $curentSiteId = $sites[0]["id"];
        }

        $modelArea = new ReArea();
        $areas = array();
        if ($_SESSION["user_status"] < 3) {
            $areas = $modelArea->getUnrestrictedAreasIDNameForSite($curentSiteId);
        } else {
            $areas = $modelArea->getAreasIDNameForSite($curentSiteId);
        }

        $foundArea = false;
        foreach ($areas as $area) {
            if ($area["id"] == $curentAreaId) {
                $foundArea = true;
            }
        }
        if (!$foundArea) {
            $curentAreaId = $areas[0]["id"];
        }

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($curentAreaId);

        $_SESSION['bk_id_resource'] = $curentResourceId;
        $_SESSION['bk_id_area'] = $curentAreaId;
        $_SESSION['bk_id_site'] = $curentSiteId;
        $_SESSION['bk_curentDate'] = $curentDate;

        return array(
            'sites' => $sites,
            'areas' => $areas,
            'resources' => $resources,
            'curentSiteId' => $curentSiteId,
            'curentAreaId' => $curentAreaId,
            'curentResourceId' => $curentResourceId,
            'curentDate' => $curentDate
        );
    }

    /**
     * Check if a given user is allowed to book a ressource
     * @param number $id_resourcecategory ID of the resource category
     * @param number $resourceAccess Type of users who can access the resource
     * @param number $id_user User ID
     * @param number $userStatus User status
     * @param number $curentDateUnix Curent date in unix format 
     * @return boolean
     */
    protected function hasAuthorization($id_resourcecategory, $resourceAccess, $id_user, $userStatus, $curentDateUnix) {

        if ($userStatus == 5) {
            return true;
        }

        // user cannot book in the past
        if ($curentDateUnix < mktime(0, 0, 0, date("m", time()), date("d", time()), date("Y", time())) && $userStatus < 3) {
            return false;
        }

        // test depending the user status and resource
        $isUserAuthorizedToBook = false;
        if ($resourceAccess == 1) {
            if ($userStatus > 1) {
                $isUserAuthorizedToBook = true;
            }
        }
        if ($resourceAccess == 2) {
            //echo "pass 1 </Br>";
            if ($userStatus > 2) {
                $isUserAuthorizedToBook = true;
            }
            if ($userStatus == 2) {
                //echo "pass </Br>";
                // check if the user has been authorized
                $modelAuth = new SyAuthorization();
                $isUserAuthorizedToBook = $modelAuth->hasAuthorization($id_resourcecategory, $id_user);
                //echo "authorized user = " . $isUserAuthorizedToBook . "";
            }
        }
        if ($resourceAccess == 3) {
            if ($userStatus >= 3) {
                $isUserAuthorizedToBook = true;
            }
        }
        if ($resourceAccess == 4) {
            if ($userStatus >= 4) {
                $isUserAuthorizedToBook = true;
            }
        }
        return $isUserAuthorizedToBook;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function bookingAction($id_site, $id_area, $id_resource) {

        $menuData = $this->calendarMenuData($id_site, $id_area, $id_resource, date("Y-m-d", time()));


        $modelResource = new ResourceInfo();
        $modelArea = New ReArea();
        if ($id_resource == "" || $id_resource == 0) { // booking home page
            $userSettingsModel = new CoreUserSettings();
            $calendarDefaultResource = $userSettingsModel->getUserSetting($_SESSION["id_user"], "calendarDefaultResource");
            if ($calendarDefaultResource != "") {
                $id_resource = $calendarDefaultResource;
                $id_area = $modelResource->getAreaID($id_resource);
                $id_site = $modelArea->getSiteID($id_area);
            } else {
                if ($id_area == "" || $id_area == 0) {
                    $modelArea = new ReArea();
                    if ($_SESSION["user_status"] < 3) {
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
            $_SESSION['bk_id_site'] = $id_site;
            $_SESSION['bk_curentDate'] = date("Y-m-d", time());

            if ($id_resource == 0) {
                $this->render(array(
                    'menuData' => $menuData
                ));
                return;
            }
        }
        $this->redirect("bookingdayarea/");
    }

    public function book($message) {
        $lastView = "";
        if (isset($_SESSION["user_settings"]["calendarDefaultView"])) {
            $lastView = $_SESSION["user_settings"]["calendarDefaultView"];
        }
        if (isset($_SESSION['lastbookview'])) {
            $lastView = $_SESSION['lastbookview'];
        }
        if ($lastView == "bookday") {
            $this->dayAction("", $message);
            return;
        } else if ($lastView == "bookweek") {
            $this->weekAction("", $message);
            return;
        } else if ($lastView == "bookweekarea") {
            $this->weekareaAction("", $message);
            return;
        } else if ($lastView == "bookdayarea") {
            $this->dayareaAction("", $message);
            return;
        }
        $this->dayAction("", $message);
    }

    public function dayAction($action, $message) {

        //print_r($_SESSION);
        $_SESSION['lastbookview'] = "bookday";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentSiteId = $this->request->getParameterNoException('bk_id_site');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        //echo "curent resource bookday 1 = " . $curentResource . "<br/>";

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentSiteId = $_SESSION['bk_id_site'];
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

        $menuData = $this->calendarMenuData($curentSiteId, $curentAreaId, $curentResource, $curentDate);

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
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);
        //print_r($agendaStyle);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
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

    public function dayareaAction($action, $message) {
        $_SESSION['lastbookview'] = "bookdayarea";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentSiteId = $this->request->getParameterNoException('bk_id_site');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentSiteId = $_SESSION['bk_id_site'];
            $curentDate = $_SESSION['bk_curentDate'];
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

        $menuData = $this->calendarMenuData($curentSiteId, $curentAreaId, $curentResource, $curentDate);

        // save the menu info in the session
        $_SESSION['bk_id_resource'] = $curentResource;
        $_SESSION['bk_id_area'] = $curentAreaId;
        $_SESSION['bk_id_site'] = $curentSiteId;
        $_SESSION['bk_curentDate'] = $curentDate;

        // get the area info
        //$modelArea = new ReArea();
        //$area = $modelArea->getArea($curentAreaId);
        // get the resource info
        $modelRes = new ResourceInfo();
        $modelAccess = new BkAccess();
        $resourcesBase = $modelRes->resourcesForArea($curentAreaId);
        for ($r = 0; $r < count($resourcesBase); $r++) {
            $resourcesBase[$r]["accessibility_id"] = $modelAccess->getAccessId($resourcesBase[$r]["id"]);
        }

        // get the entries for this resource
        $modelEntries = new BkCalendarEntry();
        $dateArray = explode("-", $curentDate);
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
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        foreach ($resourcesBase as $resourceBase) {
            //print_r($resourceBase);
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);
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

    public function weekAction($action, $message) {
        $_SESSION['lastbookview'] = "bookweek";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentSiteId = $this->request->getParameterNoException('bk_id_site');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentSiteId = $_SESSION['bk_id_site'];
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

        $menuData = $this->calendarMenuData($curentSiteId, $curentAreaId, $curentResource, $curentDate);

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
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        // stylesheet
        $modelCSS = new BkBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($curentAreaId);

        // view
        $this->render(array(
            'lang' => $lang,
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

    public function weekareaAction($action, $message) {
        $_SESSION['lastbookview'] = "bookweekarea";

        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentSiteId = $this->request->getParameterNoException('bk_id_site');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentSiteId = $_SESSION['bk_id_site'];
            $curentDate = $_SESSION['bk_curentDate'];
            //echo "curent date n-2 = " . $curentDate . "<br/>";
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

        $menuData = $this->calendarMenuData($curentSiteId, $curentAreaId, $curentResource, $curentDate);

        // save the menu info in the session
        /*
          $_SESSION['id_resource'] = $curentResource;
          $_SESSION['id_area'] = $curentAreaId;
          $_SESSION['curentDate'] = $curentDate;
         */
        // get the area info
        $modelArea = new ReArea();
        $area = $modelArea->get($curentAreaId);

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->resourcesForArea($curentAreaId);

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
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $isUserAuthorizedToBook = array();
        foreach ($resourcesBase as $resourceBase) {
            $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);
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

    public function monthAction($action, $message) {
        $_SESSION['lastbookview'] = "bookmonth";
        $lang = $this->getLanguage();

        // get inputs
        $curentResource = $this->request->getParameterNoException('bk_id_resource');
        $curentAreaId = $this->request->getParameterNoException('bk_id_area');
        $curentSiteId = $this->request->getParameterNoException('bk_id_site');
        $curentDate = $this->request->getParameterNoException('bk_curentDate');

        if ($curentDate != "") {
            $curentDate = CoreTranslator::dateToEn($curentDate, $lang);
        }

        if ($curentAreaId == "") {
            $curentResource = $_SESSION['bk_id_resource'];
            $curentAreaId = $_SESSION['bk_id_area'];
            $curentSiteId = $_SESSION['bk_id_site'];
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

        $menuData = $this->calendarMenuData($curentSiteId, $curentAreaId, $curentResource, $curentDate);

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
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        $modelAccess = new BkAccess();
        $resourceBase["accessibility_id"] = $modelAccess->getAccessId($resourceBase["id"]);
        $isUserAuthorizedToBook = $this->hasAuthorization($resourceBase["id_category"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);

        // view
        $this->render(array(
            'lang' => $lang,
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
            'message' => $message
                ), "bookmonth");
    }

    public function editreservationAction($param) {

        $modelSettings = new CoreConfig();
        $editResaFunction = $modelSettings->getParam("bkReservationPlugin");

        if ($editResaFunction == "" || $editResaFunction == "bookingeditreservationdefault") {
            $modelDefault = new BookingdefaultController();
            $modelDefault->editreservationdefault($param);
            return;
        } else {
            /// todo run plugin
            //if ($controllerFound == false) {
            throw new Exception("Booking plugin " . $editResaFunction . " not found");
            //}
        }
    }

    /**
     * Form to edit a reservation
     */
    public function editreservationdefault($param) {

        $lang = $this->getLanguage();
        // get the action
        $action = $param;
        $contentAction = explode("_", $action);

        if (count($contentAction) > 3) {
            $_SESSION["id_resource"] = $contentAction[3];
        }

        // get the menu info
        $id_resource = $this->request->getSession()->getAttribut('bk_id_resource');
        $id_area = $this->request->getSession()->getAttribut('bk_id_area');
        $id_site = $this->request->getSession()->getAttribut('bk_id_site');
        $curentDate = $this->request->getSession()->getAttribut('bk_curentDate');

        // get the cal sups
        $modelCalSup = new BkCalSupplementary();
        $calSups = $modelCalSup->calSupsByResource($id_resource);
        $calSupsData = array();
        foreach ($calSups as $calSup) {
            $calSupsData[$calSup["name"]] = "";
        }

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourceBase = $modelRes->get($id_resource);
        $resourceBase["use_package"] = 1;

        // get users list
        $modelUser = new EcUser();
        $users = $modelUser->getActiveUsers("Name");

        $curentuserid = $this->request->getSession()->getAttribut("id_user");
        $curentuser = $modelUser->userAllInfo($curentuserid);

        // navigation
        $menuData = $this->calendarMenuData($id_site, $id_area, $id_resource, $curentDate);

        // color types
        $colorCodeModel = new BkColorCode();
        $colorCodes = $colorCodeModel->getColorCodes("display_order");

        // a user cannot delete a reservation in the past
        $canEditReservation = false;
        //echo "can edit reservation = " . $canEditReservation . " <br/>";
        $temp = explode("-", $curentDate);
        $H = date("H", time());
        $min = date("i", time());
        $curentDateUnix = mktime($H, $min + 1, 0, $temp[1], $temp[2], $temp[0]);
        if ($curentDateUnix >= time() && $_SESSION["user_status"] < 3) {
            $canEditReservation = true;
        }
        if ($_SESSION["user_status"] >= 3) {
            $canEditReservation = true;
        }
        
        // packages
        $packagesModel = new BkPackage();
        $packages = $packagesModel->getByResource($id_resource);

        $modelScheduling = new BkScheduling();
        $scheduling = $modelScheduling->get($id_area);

        // set the view given the action		
        if ($contentAction[0] == "t") { // add resa 
            $curentDate = $contentAction[1];
            $beginTime = $contentAction[2];
            $beginTime = str_replace("-", ".", $beginTime);
            $h = floor($beginTime);
            $m = $beginTime - $h;
            if ($m == 0.25) {
                $m = 15;
            }
            if ($m == 0.5) {
                $m = 30;
            }
            if ($m == 0.75) {
                $m = 45;
            }
            $timeBegin = array('h' => $h, 'm' => $m);

            $timeEnd = array('h' => $h + 1, 'm' => $m);
            if ($scheduling["size_bloc_resa"] == 1800) {
                if ($m < 30) {
                    $m += 30;
                } else if ($m >= 30) {
                    $m -= 30;
                    $h += 1;
                }
                $timeEnd = array('h' => $h, 'm' => $m);
            }
            if ($scheduling["size_bloc_resa"] == 900) {
                if ($m < 45) {
                    $m += 15;
                } else if ($m >= 45) {
                    $m -= 45;
                    $h += 1;
                }
                $timeEnd = array('h' => $h, 'm' => $m);
            }

            // navigation
            $menuData = $this->calendarMenuData($id_site, $id_area, $_SESSION["id_resource"], $curentDate);

            $responsiblesList = $modelUser->getUserResponsibles($curentuser["id"]);
            // view
            $this->render(array(
                'lang' => $lang,
                'scheduling' => $scheduling,
                'menuData' => $menuData,
                'resourceBase' => $resourceBase,
                'date' => $curentDate,
                'timeBegin' => $timeBegin,
                'timeEnd' => $timeEnd,
                'users' => $users,
                'curentuser' => $curentuser,
                'canEditReservation' => $canEditReservation,
                'colorCodes' => $colorCodes,
                'projectsList' => $projectsList,
                'showSeries' => $showSeries,
                'calSups' => $calSups,
                'calSupsData' => $calSupsData,
                'packages' => $packages,
                'responsiblesList' => $responsiblesList,
                'id_new_resa' => true
                    ), "editreservation");
        } else { // edit resa
            $reservation_id = $contentAction[1];

            $modelResa = new BkCalendarEntry();
            $reservationInfo = $modelResa->getEntry($reservation_id);
            $resourceBase = $modelRes->get($reservationInfo["resource_id"]);
            $resourceBase["use_package"] = 1;
            /*
              if ($reservationInfo["package_id"] > 0){
              $resourceBase["use_package"] = 1;
              }
              else{
              $resourceBase["use_package"] = 0;
              }
             */

            //print_r($reservationInfo);
            // navigation
            $_SESSION["id_resource"] = $reservationInfo["resource_id"];
            $menuData = $this->calendarMenuData($id_site, $id_area, $_SESSION["id_resource"], $curentDate);


            //print_r($reservationInfo);
            if ($_SESSION["user_status"] < 3 && $reservationInfo["start_time"] <= time()) {
                $canEditReservation = false;
            }

            $seriesInfo = "";
            if ($reservationInfo['repeat_id'] > 0) {
                $modelSeries = new BkCalendarSeries();
                $seriesInfo = $modelSeries->getEntry($reservationInfo['repeat_id']);
            }

            //print_r($seriesInfo);
            if ($_SESSION["user_status"] < 3 && $curentuserid != $reservationInfo["recipient_id"]) {
                $canEditReservation = false;
            }

            // get sup data 
            $calSupsData = $modelCalSup->getSupData($reservation_id);

            $responsiblesList = $modelUser->getUserResponsibles($reservationInfo["recipient_id"]);

            $this->render(array(
                'lang' => $lang,
                'scheduling' => $scheduling,
                'menuData' => $menuData,
                'resourceBase' => $resourceBase,
                'seriesInfo' => $seriesInfo,
                'date' => $curentDate,
                'users' => $users,
                'curentuser' => $curentuser,
                'reservationInfo' => $reservationInfo,
                'canEditReservation' => $canEditReservation,
                'colorCodes' => $colorCodes,
                'projectsList' => $projectsList,
                'showSeries' => $showSeries,
                'calSups' => $calSups,
                'calSupsData' => $calSupsData,
                'packages' => $packages,
                'responsiblesList' => $responsiblesList,
                'id_new_resa' => false
                    ), "editreservation");
        }
    }

    /**
     * Internal method
     * @param number $val
     * @return boolean
     */
    private function isMinutes($val) {
        if (intval($val) < 0 || intval($val) > 60) {
            //echo "minut not in [0, 60] <br/>";
            return false;
        }
        return true;
    }

    /**
     * Internal method
     * @param number $val
     * @return boolean
     */
    private function isHour($val) {

        if (intval($val) < 0 || intval($val) > 23) {
            return false;
        }
        return true;
    }

    public function editreservationqueryAction() {

        $lang = $this->getLanguage();

        // get reservation info
        $reservation_id = $this->request->getParameterNoException('reservation_id');
        $resource_id = $this->request->getParameter('resource_id');
        $booked_by_id = $this->request->getSession()->getAttribut("id_user");
        $recipient_id = $this->request->getParameter('recipient_id');
        $last_update = date("Y-m-d H:i:s", time());
        $color_type_id = $this->request->getParameter('color_code_id');
        $short_description = $this->request->getParameterNoException('short_description');
        $full_description = $this->request->getParameterNoException('full_description');
        $responsible_id = $this->request->getParameterNoException('responsible_id');

        // get reservation date
        $beginDate = $this->request->getParameter('begin_date');
        $beginDate = CoreTranslator::dateToEn($beginDate, $lang);
        $beginDate = explode("-", $beginDate);
        $begin_hour = $this->request->getParameter('begin_hour');
        $begin_hour = intval($begin_hour);
        //echo "begin hour = " . $begin_hour . "<br/>";
        if (!$this->isHour($begin_hour)) {
            $this->book("Error: The start hour you gave is not correct");
            return;
        }
        $begin_min = $this->request->getParameter('begin_min');
        $begin_min = intval($begin_min);
        if ($begin_min == "") {
            $begin_min = 0;
        }
        if (!$this->isMinutes($begin_min)) {
            $this->book("Error: The start minute you gave is not correct");
            return;
        }
        $start_time = mktime($begin_hour, $begin_min, 0, $beginDate[1], $beginDate[2], $beginDate[0]);

        $endDate = $this->request->getParameterNoException('end_date');
        if ($endDate != "") {
            $endDate = CoreTranslator::dateToEn($endDate, $lang);
        }
        $endDate = explode("-", $endDate);
        $end_hour = $this->request->getParameterNoException('end_hour');
        $end_hour = intval($end_hour);
        if (!$this->isHour($end_hour)) {
            $this->book("Error: The end hour you gave is not correct");
            return;
        }
        $end_min = $this->request->getParameterNoException('end_min');
        if ($end_min == "") {
            $end_min = 0;
        }
        $end_min = intval($end_min);
        //echo "end min = " . $end_min . "<br/>";
        if (!$this->isMinutes($end_min)) {
            $this->book("Error: The end minute you gave is not correct");
            return;
        }

        if (count($endDate) > 2) {
            $end_time = mktime($end_hour, $end_min, 0, $endDate[1], $endDate[2], $endDate[0]);
        }

        $duration = $this->request->getParameterNoException('duration');
        $duration_step = $this->request->getParameterNoException('duration_step');
        if ($duration != "") {
            $coef = 60;
            if ($duration_step == 1) {
                $coef = 60;
            }
            if ($duration_step == 2) {
                $coef = 3600;
            }
            if ($duration_step == 3) {
                $coef = 3600 * 24;
            }
            $end_time = $start_time + $duration * $coef;
        }

        $use_package = $this->request->getParameterNoException("use_package");
        $package = 0;
        if ($use_package == "yes") {
            $packageID = $this->request->getParameterNoException("package_choice");
            $package = $packageID;
            $modelPackage = new BkPackage();
            $duration = $modelPackage->getPackageDuration($packageID);
            $end_time = $start_time + $duration * 3600;
        }

        if ($start_time >= $end_time) {
            $this->book("Error: The start time you gave is after the end time");
            return;
        }

        // get the responsible:
        if ($responsible_id == "") {
            $modelUser = new EcUser();
            $respList = $modelUser->getUserResponsibles($recipient_id);
            $responsible_id = $respList[0]["id"];
        }

        $modelCalEntry = new BkCalendarEntry();
        // test if a resa already exists on this periode
        $conflict = $modelCalEntry->isConflict($start_time, $end_time, $resource_id, $reservation_id);

        if ($conflict) {
            $this->book("Error: There is already a reservation for the given slot");
            return;
        }

        if ($reservation_id == "") {
            $reservation_id = $modelCalEntry->addEntry($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity, $package);

            $modelCalEntry->setEntryResponsible($reservation_id, $responsible_id);
            $this->sendEditREservationEmail($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $short_description, $full_description, $quantity, "add");
        } else {
            $modelCalEntry->updateEntry($reservation_id, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity, $package);
            $modelCalEntry->setEntryResponsible($reservation_id, $responsible_id);
            $this->sendEditREservationEmail($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $short_description, $full_description, $quantity, "edit");
        }

        //echo "send email <br/>";
        $this->sendEmailToManagers($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $short_description, $full_description, $quantity, "edit");
        //return;
        // add the suplementary info
        if ($this->request->isParameter("calsupName")) {
            $this->addSuplementaryInfo($this->request->getParameter("calsupName"), $this->request->getParameter("calsupValue"), $reservation_id);
        }


        $_SESSION['bk_id_resource'] = $resource_id;
        $modelResource = new ResourceInfo();
        $areaID = $modelResource->getAreaID($resource_id);
        $_SESSION['bk_id_area'] = $areaID;
        $date = $this->request->getParameter('begin_date');
        //echo "date = " . $date . "<br />";
        if ($date != "") {
            $date = CoreTranslator::dateToEn($date, $lang);
        }
        //echo "DATE = " .  $date . "--";
        $_SESSION['curentDate'] = $date;

        $message = "Success: Your reservation has been saved";
        $this->book($message);
    }

    /**
     * Send email to advice user that a manager udpate his reservation
     * @param date $start_time
     * @param date $end_time
     * @param number $resource_id
     * @param number $booked_by_id
     * @param number $recipient_id
     * @param string $short_description
     * @param string $full_description
     * @param number $quantity
     * @param number $editstatus
     */
    private function sendEditReservationEmail($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $short_description, $full_description, $quantity, $editstatus) {


        $modelConfig = new CoreConfig();
        if ($modelConfig->getParam("SyEditBookingMailing") >= 2 && $booked_by_id != $recipient_id) {

            $modelUser = new CoreUser();
            $fromEmail = $modelUser->getUserEmail($booked_by_id);
            $toEmail = $modelUser->getUserEmail($recipient_id);

            if ($fromEmail != "" && $toEmail != "") {

                $modelUserSettings = new UserSettings();
                $settings = $modelUserSettings->getUserSettings($recipient_id);
                $lang = "En";
                if (isset($settings["language"])) {
                    $lang = $settings["language"];
                }

                $subject = SyTranslator::Your_reservation($lang);
                $content = "";
                if ($editstatus == "add") {
                    $content .= SyTranslator::Your_reservation_has_been_added($lang) . ": <br/>";
                } else if ($editstatus == "edit") {
                    $content .= SyTranslator::Your_reservation_has_been_modified($lang) . ": <br/>";
                } else if ($editstatus == "deleted") {
                    $content .= SyTranslator::Your_reservation_has_been_deleted($lang) . ": <br/>";
                }

                $modelResource = new SyResource();
                $resourceInfo = $modelResource->resource($resource_id);

                $content .= "<br/>";
                $content .= "<b>" . SyTranslator::Edited_by($lang) . ": </b> " . $modelUser->getUserFUllName($booked_by_id) . "<br/>";
                $content .= "<b>" . SyTranslator::Recipient($lang) . ": </b> " . $modelUser->getUserFUllName($recipient_id) . "<br/>";
                $content .= "<b>" . SyTranslator::Resource($lang) . "</b> " . $resourceInfo["name"] . "<br/>";
                $content .= "<b>" . SyTranslator::Beginning($lang) . ": </b> " . date("F j, Y, g:i a", $start_time) . "<br/>";
                $content .= "<b>" . SyTranslator::End($lang) . ": </b> " . date("F j, Y, g:i a", $end_time) . "<br/>";

                if ($short_description != "") {
                    $content .= "<b>" . SyTranslator::Short_description($lang) . ": </b> " . $short_description . "<br/>";
                }
                if ($short_description != "") {
                    $content .= "<b>" . SyTranslator::Full_description($lang) . ": </b> " . $full_description . "<br/>";
                }
                if ($quantity != "") {
                    $content .= "<b>" . SyTranslator::Quantity($lang) . ": </b> " . $quantity . "<br/>";
                }

                /*
                  echo "fromEmail = " . $fromEmail . "<br/>";
                  echo "toEmail = " . $toEmail . "<br/>";
                  echo "subject = " . $subject . "<br/>";
                  echo "content = " . $content . "<br/>";
                 */

                $modelMailer = new MailerSend();
                $modelMailer->sendEmail($fromEmail, Configuration::get("name"), $toEmail, $subject, $content, false);
            }
        }
    }

    private function sendEmailToManagers($start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $short_description, $full_description, $quantity, $editstatus) {

        $modelConfig = new CoreConfig();
        if ($modelConfig->getParam("SyBookingMailingAdmins") >= 2) {

            $modelUser = new CoreUser();
            $fromEmail = $modelUser->getUserEmail($booked_by_id);
            $toEmails = $modelUser->getActiveManagersEmails();
            $recipient_name = $modelUser->getUserFUllName($recipient_id);

            if ($fromEmail != "" && count($toEmails) > 0) {

                $modelUserSettings = new UserSettings();
                $settings = $modelUserSettings->getUserSettings($recipient_id);
                $lang = "En";
                if (isset($settings["language"])) {
                    $lang = $settings["language"];
                }

                $subject = SyTranslator::Reservation($lang) . " " . $recipient_name;
                $content = "";
                if ($editstatus == "add") {
                    $content .= SyTranslator::Your_reservation_has_been_added($lang) . ": <br/>";
                } else if ($editstatus == "edit") {
                    $content .= SyTranslator::Your_reservation_has_been_modified($lang) . ": <br/>";
                } else if ($editstatus == "deleted") {
                    $content .= SyTranslator::Your_reservation_has_been_deleted($lang) . ": <br/>";
                }

                $modelResource = new SyResource();
                $resourceInfo = $modelResource->resource($resource_id);

                $content .= "<br/>";
                $content .= "<b>" . SyTranslator::Edited_by($lang) . ": </b> " . $modelUser->getUserFUllName($booked_by_id) . "<br/>";
                $content .= "<b>" . SyTranslator::Recipient($lang) . ": </b> " . $modelUser->getUserFUllName($recipient_id) . "<br/>";
                $content .= "<b>" . SyTranslator::Resource($lang) . "</b> " . $resourceInfo["name"] . "<br/>";
                $content .= "<b>" . SyTranslator::Beginning($lang) . ": </b> " . date("F j, Y, g:i a", $start_time) . "<br/>";
                $content .= "<b>" . SyTranslator::End($lang) . ": </b> " . date("F j, Y, g:i a", $end_time) . "<br/>";

                if ($short_description != "") {
                    $content .= "<b>" . SyTranslator::Short_description($lang) . ": </b> " . $short_description . "<br/>";
                }
                if ($short_description != "") {
                    $content .= "<b>" . SyTranslator::Full_description($lang) . ": </b> " . $full_description . "<br/>";
                }
                if ($quantity != "") {
                    $content .= "<b>" . SyTranslator::Quantity($lang) . ": </b> " . $quantity . "<br/>";
                }

                $modelMailer = new MailerSend();
                $modelMailer->sendEmail($fromEmail, Configuration::get("name"), $toEmails, $subject, $content, false);
            }
        }
    }

    /**
     * Remove a reservation query
     */
    public function removeentry() {
        // get the action
        $id = '';
        if ($this->request->isParameterNotEmpty('actionid')) {
            $id = $this->request->getParameter("actionid");
        }

        $modelEntry = new SyCalendarEntry();
        $entry = $modelEntry->getEntry($id);
        $message = $modelEntry->removeEntry($id);

        $this->sendEditReservationEmail($entry["start_time"], $entry["end_time"], $entry["resource_id"], $entry["booked_by_id"], $entry["recipient_id"], $entry["short_description"], $entry["full_description"], $entry["quantity"], "deleted");

        $this->book($message);
    }

    /**
     * Add suplementary informations to a reservation
     * @param array $calsupNames Keys of the suplementary informations 
     * @param array $calsupValues Values of the suplementary informations   
     * @param number $reservation_id ID of the information to edit
     */
    private function addSuplementaryInfo($calsupNames, $calsupValues, $reservation_id) {

        $modelCalSup = new BkCalSupplementary();
        $modelCalSup->setEntrySupData($calsupNames, $calsupValues, $reservation_id);
    }

}
