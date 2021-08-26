<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingblockController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bookingsettings");
        $_SESSION["openedNav"] = "bookingsettings";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);

        $modelResources = new ResourceInfo();
        $resources = $modelResources->getBySpace($id_space);

        $modelColor = new BkColorCode();
        $colorCodes = $modelColor->getColorCodes($id_space);

        $errormessage = "";
        $lang = $this->getLanguage();
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'resources' => $resources,
            'colorCodes' => $colorCodes,
            'errormessage' => $errormessage
        ));
    }

    /**
     * Query to make several resources unavailable
     * 
     * @deprecated
     */
    public function blockresourcesqueryAction($id_space) {
        $lang = $this->getLanguage();

        // get form variables
        $short_description = $this->request->getParameter("short_description");
        $resources = $this->request->getParameter("resources");
        $begin_date = $this->request->getParameter("begin_date");
        $begin_hour = $this->request->getParameter("begin_hour");
        $begin_min = $this->request->getParameter("begin_min");
        $end_date = $this->request->getParameter("end_date");
        $end_hour = $this->request->getParameter("end_hour");
        $end_min = $this->request->getParameter("end_min");
        $color_type_id = $this->request->getParameter("color_code_id");

        $beginDate = CoreTranslator::dateToEn($begin_date, $lang);
        $beginDate = explode("-", $beginDate);
        $start_time = mktime(intval($begin_hour), intval($begin_min), 0, $beginDate[1], $beginDate[2], $beginDate[0]);

        $endDate = CoreTranslator::dateToEn($end_date, $lang);
        $endDate = explode("-", $endDate);
        $end_time = mktime(intval($end_hour), intval($end_min), 0, $endDate[1], $endDate[2], $endDate[0]);

        if ($end_time <= $start_time) {
            $errormessage = "Error: The begin time must be before the end time";
            //echo "error message = " . $errormessage . "<br/>";
            $modelResources = new ResourceInfo();
            $resources = $modelResources->getBySpace($id_space);
            $modelColor = new BkColorCode();
            $colorCodes = $modelColor->getColorCodes($id_space);
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'resources' => $resources,
                'colorCodes' => $colorCodes,
                'errormessage' => $errormessage
                    ), "indexAction");
            return;
        }

        // Add the booking
        $modelCalEntry = new BkCalendarEntry();
        $userID = $_SESSION["id_user"];
        foreach ($resources as $resource_id) {

            $conflict = $modelCalEntry->isConflict($id_space, $start_time, $end_time, $resource_id);

            if ($conflict) {
                $errormessage = "Error: There is already a reservation for the given slot, please remove it before booking";
                $modelResources = new ResourceInfo();
                $resources = $modelResources->getBySpace($id_space);
                $modelColor = new BkColorCode();
                $colorCodes = $modelColor->getColorCodes($id_space);
                $this->render(array(
                    'id_space' => $id_space,
                    'lang' => $lang,
                    'resources' => $resources,
                    'colorCodes' => $colorCodes,
                    'errormessage' => $errormessage
                        ), "indexAction");
                return;
            }
            $booked_by_id = $userID;
            $recipient_id = $userID;
            $last_update = date("Y-m-d H:i:s", time());
            $full_description = "";
            $quantity = "";
            $modelCalEntry->addEntry($id_space, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity);
        }

        $this->redirect("bookingblock/" . $id_space);
    }

}
