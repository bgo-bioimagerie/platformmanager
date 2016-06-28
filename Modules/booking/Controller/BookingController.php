<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';

require_once 'Modules/core/Model/CoreUserSettings.php';

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
        $this->bookingAction(0, 0, 0);
    }

    /**
     * Get the content of of the booking menu for the calendar pages
     * @param number $curentAreaId ID of the curent area
     * @param number $curentResourceId ID of the current resource
     * @param date $curentDate Curent date
     * @return array: booking menu content
     */
    public function calendarMenuData($curentAreaId, $curentResourceId, $curentDate) {

        $modelArea = new ReArea();
        $areas = array();
        if ($_SESSION["user_status"] < 3) {
            $areas = $modelArea->getUnrestrictedAreasIDName();
        } else {
            $areas = $modelArea->getAreasIDName();
        }

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($curentAreaId);

        return array('areas' => $areas,
            'resources' => $resources,
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
                $menuData = $this->calendarMenuData($id_site, $id_area, $id_resource, date("Y-m-d", time()));
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
            $this->bookday($message);
            return;
        } else if ($lastView == "bookweek") {
            $this->bookweek($message);
            return;
        } else if ($lastView == "bookweekarea") {
            $this->bookweekarea($message);
            return;
        } else if ($lastView == "bookdayarea") {
            $this->bookdayarea($message);
            return;
        }
        $this->bookday($message);
    }

    public function dayareaAction() {
        $_SESSION['lastbookview'] = "bookdayarea";

        $lang = "En";
        if (isset($_SESSION["user_settings"]["language"])) {
            $lang = $_SESSION["user_settings"]["language"];
        }

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
        $action = "";
        if ($this->request->isParameterNotEmpty("actionid")) {
            $action = $this->request->getParameter("actionid");
        }
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

        $menuData = $this->calendarMenuData($curentAreaId, $curentResource, $curentDate);

        // save the menu info in the session
        $_SESSION['id_resource'] = $curentResource;
        $_SESSION['id_area'] = $curentAreaId;
        $_SESSION['curentDate'] = $curentDate;

        // get the area info
        //$modelArea = new ReArea();
        //$area = $modelArea->getArea($curentAreaId);

        // get the resource info
        $modelRes = new ResourceInfo();
        $resourcesBase = $modelRes->resourcesForArea($curentAreaId);

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
        $modelColor = new SyColorCode();
        $colorcodes = $modelColor->getColorCodes("name");

        // isUserAuthorizedToBook
        foreach ($resourcesBase as $resourceBase) {
            $isUserAuthorizedToBook[] = $this->hasAuthorization($resourceBase["category_id"], $resourceBase["accessibility_id"], $_SESSION['id_user'], $_SESSION["user_status"], $curentDateUnix);
        }

        //print_r($calEntries);
        //return;
        // stylesheet
        $modelCSS = new SyBookingTableCSS();
        $agendaStyle = $modelCSS->getAreaCss($curentAreaId);


        // view
        $navBar = $this->navBar();
        $this->generateView(array(
            'navBar' => $navBar,
            'menuData' => $menuData,
            'resourcesInfo' => $resourcesInfo,
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

}
