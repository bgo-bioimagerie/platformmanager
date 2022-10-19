<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingblockController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelResources = new ResourceInfo();
        $resources = $modelResources->getBySpace($idSpace);
        $rmap = [];
        foreach ($resources as $res) {
            $rmap[$res['id']] = $res['name'];
        }

        $modelColor = new BkColorCode();
        $colorCodes = $modelColor->getColorCodes($idSpace);

        $errorMessages = [];
        if (empty($resources)) {
            $errorMessages[] = BookingTranslator::ResourceNeeded($lang);
        }
        if (empty($colorCodes)) {
            $errorMessages[] = BookingTranslator::ColorNeeded($lang);
        }
        if (!empty($errorMessages)) {
            $_SESSION["flash"] = implode("</br>", $errorMessages);
            $_SESSION["flashClass"] = 'warning';
        }

        $bm = new BkCalendarEntry();
        $blockedEntries = $bm->blockedEntries($idSpace);
        $table = new TableView();
        $table->setTitle(BookingTranslator::Blocked_Resouces($lang));
        $table->addLineEditButton("bookingeditreservation/".$idSpace);



        for ($i=0; $i<count($blockedEntries); $i++) {
            $e = $blockedEntries[$i];
            $start = new DateTime();
            $start->setTimestamp($e['start_time']);
            $end = new DateTime();
            $end->setTimestamp($e['end_time']);
            $blockedEntries[$i]['id'] = 'r_'.$e['id'];
            $blockedEntries[$i]['start'] = CoreTranslator::dateFromEn($start->format('Y-m-d'), $lang)." ".$start->format('H:i');
            $blockedEntries[$i]['end'] = CoreTranslator::dateFromEn($end->format('Y-m-d'), $lang)." ".$end->format('H:i');
            $blockedEntries[$i]['reason'] = BookingTranslator::BlockReason($e['reason'], $lang);
            $blockedEntries[$i]['resource'] = $rmap[$e['resource_id']];
        }


        $headers = array(
            "id" => "ID",
            "start" => BookingTranslator::Beginning_of_the_reservation($lang),
            "end" => BookingTranslator::End_of_the_reservation($lang),
            "resource" => ResourcesTranslator::resource($lang),
            "reason" => BookingTranslator::Reason($lang)
        );

        $tableHtml = $table->view($blockedEntries, $headers);

        $lang = $this->getLanguage();
        $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            'resources' => $resources,
            'colorCodes' => $colorCodes,
            'blocked' => $tableHtml
        ));
    }

    /**
     * Query to make several resources unavailable
     *
     */
    public function blockresourcesqueryAction($idSpace)
    {
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
        $reason = $this->request->getParameterNoException("reason");
        if ($reason == '') {
            $reason = BkCalendarEntry::$REASON_BOOKING;
        }

        if ($begin_date == "") {
            throw new PfmParamException("invalid begin date");
        }
        if ($end_date == "") {
            throw new PfmParamException("invalid end date");
        }

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
            $resources = $modelResources->getBySpace($idSpace);
            $modelColor = new BkColorCode();
            $colorCodes = $modelColor->getColorCodes($idSpace);
            $_SESSION['flash'] = $errormessage;
            $_SESSION['flashClass'] = 'danger';
            $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'resources' => $resources,
                'colorCodes' => $colorCodes
                    ), "indexAction");
            return;
        }

        // Add the booking
        $modelCalEntry = new BkCalendarEntry();
        $userID = $_SESSION["id_user"];
        foreach ($resources as $resource_id) {
            $conflict = $modelCalEntry->isConflict($idSpace, $start_time, $end_time, [$resource_id]);

            if ($conflict) {
                $errormessage = "Error: There is already a reservation for the given slot, please remove it before booking";
                $modelResources = new ResourceInfo();
                $resources = $modelResources->getBySpace($idSpace);
                $modelColor = new BkColorCode();
                $colorCodes = $modelColor->getColorCodes($idSpace);
                $_SESSION['flash'] = $errormessage;
                $_SESSION['flashClass'] = 'danger';
                $this->render(array(
                    'id_space' => $idSpace,
                    'lang' => $lang,
                    'resources' => $resources,
                    'colorCodes' => $colorCodes
                        ), "indexAction");
                return;
            }
            $booked_by_id = $userID;
            $recipient_id = $userID;
            $last_update = date("Y-m-d H:i:s", time());
            $full_description = "";
            $quantity = "";
            $modelCalEntry->addEntry($idSpace, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantity, reason: $reason);
            $_SESSION['flash'] = 'Resource(s) blocked';
            $_SESSION['flashClass'] = 'success';
        }

        $this->redirect("bookingblock/" . $idSpace);
    }
}
