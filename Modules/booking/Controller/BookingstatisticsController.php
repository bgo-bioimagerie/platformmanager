<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/booking/Controller/BookingabstractController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Controller/BookingdefaultController.php';
require_once 'Modules/booking/Model/BkStatsUser.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

require_once 'Modules/core/Model/CoreUserSettings.php';

require_once 'Modules/booking/Model/BkStatsUser.php';
require_once 'Modules/booking/Model/BkGraph.php';
require_once 'Modules/booking/Model/BkReport.php';

require_once 'Modules/statistics/Model/StatisticsTranslator.php';
require_once 'Modules/statistics/Controller/StatisticsController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingstatisticsController extends StatisticsController
{
    /**
     * @bug sends back stats as print_r, not a report
     */
    public function statquantitiesAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "bookingStatQuantities");
        $form->setTitle(BookingTranslator::statQuantities($lang));
        $form->addDate("datebegin", BookingTranslator::Date_Begin($lang), true);
        $form->addDate("dateend", BookingTranslator::Date_End($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "statquantities/" .$id_space);

        if ($form->check()) {
            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $dateBegin = $form->getParameter("datebegin");
            $dateEnd = $form->getParameter("dateend");
            $name = 'stats_'.BkStats::STATS_QUANTITIES.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.csv';
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');
            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => BkStats::STATS_QUANTITIES,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "user" => ["id" => $_SESSION['id_user']],
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);

            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        }

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang)
        ));
    }

    public function statreservationrespAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $form = new Form($this->request, "bookingStatTimeResp");
        $form->setTitle(BookingTranslator::statResp($lang));
        $form->addDate("datebegin", BookingTranslator::Date_Begin($lang), true);
        $form->addDate("dateend", BookingTranslator::Date_End($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingstatreservationresp/" .$id_space);

        if ($form->check()) {
            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $dateBegin = $form->getParameter("datebegin");
            $dateEnd = $form->getParameter("dateend");
            $name = 'stats_'.BkStats::STATS_BK_TIME.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.csv';
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');
            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => BkStats::STATS_BK_TIME,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "user" => ["id" => $_SESSION['id_user']],
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);

            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        }

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang)
        ));
    }

    /**
     * Statistics form pages
     */
    public function statreservationsAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("date_begin");
        if ($date_begin == "") {
            $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
            $dateArray = explode("-", $date_begin);
            $y = date("Y") - 1;
            $m = $dateArray[1] ?? '01';
            $d = $dateArray[2] ?? '01';
            $date_begin = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);
        }
        $date_end = $this->request->getParameterNoException("date_end");
        if ($date_end == "") {
            $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);
            $dateArray = explode("-", $date_end);
            $y = date("Y");
            $m = $dateArray[1] ?? '12';
            $d = $dateArray[2] ?? '31';
            $date_end = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);
        }



        $form = new Form($this->request, "statreservationsForm");
        $form->setTitle(BookingTranslator::bookingreservationstats($lang));
        $form->addDate("date_begin", BookingTranslator::PeriodBegining($lang), true, $date_begin);
        $form->addDate("date_end", BookingTranslator::PeriodEnd($lang), true, $date_end);
        $form->addSelect("generateclientstats", BookingTranslator::GenerateStatsPerClient($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $this->request->getParameterNoException("generateclientstats"));

        $modelColorCode = new BkColorCode();
        $colorCodes = $modelColorCode->getForList($id_space);
        $formAdd = new FormAdd($this->request, 'statreservationsFormAdd');
        $values = $this->request->getParameterNoException("exclude_color");
        if ($values == "") {
            $values = array();
        }
        $formAdd->addSelect("exclude_color", "", $colorCodes["names"], $colorCodes["ids"], $values);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, StatisticsTranslator::Exclude_colorcodes($lang));
        $form->setValidationButton(CoreTranslator::Ok($lang), 'bookingreservationstats/' . $id_space);


        if ($form->check()) {
            $dateBegin = CoreTranslator::dateToEn($form->getParameter("date_begin"), $lang);
            $dateEnd = CoreTranslator::dateToEn($form->getParameter("date_end"), $lang);
            $excludeColorCode = $this->request->getParameter("exclude_color");
            $generateclientstats = $this->request->getParameter("generateclientstats");

            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $name = 'stats_'.BkStats::STATS_BK.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.xlsx';
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');
            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => BkStats::STATS_BK,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "excludeColorCode" => $excludeColorCode,
                "generateclientstats" => $generateclientstats,
                "user" => ["id" => $_SESSION['id_user']],
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);
            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    /**
     * @deprecated
     */
    public function statreservationsqueryAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $month_start = $this->request->getParameter("month_start");
        $year_start = $this->request->getParameter("year_start");
        $month_end = $this->request->getParameter("month_end");
        $year_end = $this->request->getParameter("year_end");

        // get data
        $modelGraph = new BkGraph();
        $graphArray = $modelGraph->getYearNumResGraph($id_space, $month_start, $year_start, $month_end, $year_end);
        $graphTimeArray = $modelGraph->getYearNumHoursResGraph($id_space, $month_start, $year_start, $month_end, $year_end);

        // render data
        $camembertCount = $modelGraph->getCamembertArray($id_space, $month_start, $year_start, $month_end, $year_end);
        $camembertTimeCount = $modelGraph->getCamembertTimeArray($id_space, $month_start, $year_start, $month_end, $year_end);

        $content = "";
        // annual number
        $content .= BookingTranslator::Annual_review_of_the_number_of_reservations_of($lang) . " " . Configuration::get("name") . "\r\n";
        $i = 0;
        foreach ($graphArray['graph'] as $g) {
            $i++;
            $content .= $i . " ; " . $g . "\r\n";
        }

        // annual number
        $content .= "\r\n";
        $content .= BookingTranslator::Annual_review_of_the_time_of_reservations_of($lang) . " " . Configuration::get("name") . "\r\n";
        $i = 0;
        foreach ($graphTimeArray['graph'] as $g) {
            $i++;
            $content .= $i . " ; " . $g . "\r\n";
        }

        // annual resources
        $content .= "\r\n";
        $content .= BookingTranslator::Booking_number_year($lang) . " " . Configuration::get("name") . "\r\n";
        foreach ($camembertCount as $g) {
            $content .= $g[0] . " ; " . $g[1] . "\r\n";
        }

        // annual resources
        $content .= "\r\n";
        $content .= BookingTranslator::Booking_time_year($lang) . " " . Configuration::get("name") . "\r\n";
        foreach ($camembertTimeCount as $g) {
            $content .= $g[0] . " ; " . $g[1] . "\r\n";
        }

        if (getenv('PFM_MODE') == 'test') {
            return ['data' => ['stats' => $content]];
        }
        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=booking_stats.csv");
        echo $content;
    }

    public function statbookingusersAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("date_begin");
        if ($date_begin == "") {
            // if a default date is set, get it, if not, get actual date - 1 year
            $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
            if ($date_begin === "") {
                $date_begin = CoreTranslator::dateFromEn(date("Y-m-d", strtotime("-1 years")), $lang);
            } else {
                $dateArray = explode("-", $date_begin);
                $y = date("Y") - 1;
                $m = $dateArray[1];
                $d = $dateArray[2];
                $date_begin = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);
            }
        }
        $date_end = $this->request->getParameterNoException("date_end");
        if ($date_end == "") {
            $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);
            // if a default date is set, get it, if not, get actual date
            if ($date_end === "") {
                $date_end = CoreTranslator::dateFromEn(date("Y-m-d"), $lang);
            } else {
                $dateArray = explode("-", $date_end);
                $y = date("Y");
                $m = $dateArray[1];
                $d = $dateArray[2];
                $date_end = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);
            }
        }

        // build the form
        $form = new Form($this->request, "statbookingusers");
        $form->setTitle(BookingTranslator::bookingusersstats($lang));
        $form->addDate('startdate', BookingTranslator::Date_Begin($lang), true, $date_begin);
        $form->addDate('enddate', BookingTranslator::Date_End($lang), true, $date_end);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingusersstats/" . $id_space);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "statistics/" . $id_space);

        if ($form->check()) {
            $period_begin = $form->getParameter("startdate");
            $period_end = $form->getParameter("enddate");
            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $name = 'stats_'.BkStats::STATS_BK_USERS.'_'.str_replace('/', '-', $period_begin).'_'.str_replace('/', '-', $period_end).'.csv';
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');
            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => BkStats::STATS_BK_USERS,
                "dateBegin" => $period_begin,
                "dateEnd" => $period_end,
                "user" => ["id" => $_SESSION['id_user']],
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);
            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            $this->render(array(
                'lang' => $lang,
                'id_space' => $id_space,
                'formHtml' => $formHtml
            ));
        }
    }

    public function grrAction($id_space)
    {
        // table not file, do not async
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $isrequest = $this->request->getParameterNoException('is_request');
        if ($isrequest == "y") {
            // get the form parameters
            $searchDate_start = $this->request->getParameterNoException('searchDate_start');
            $searchDate_end = $this->request->getParameterNoException('searchDate_end');

            if ($searchDate_start != "") {
                $searchDate_start = CoreTranslator::dateToEn($searchDate_start, $lang);
            }
            if ($searchDate_end != "") {
                $searchDate_end = CoreTranslator::dateToEn($searchDate_end, $lang);
            }

            if ($searchDate_start == "" || $searchDate_end == "") {
                $errormessage = "You must specify a start date and an end date";
                $this->render(array(
                    'lang' => $lang,
                    'id_space' => $id_space,
                    'errorMessage' => $errormessage
                ));
                return;
            }

            // convert start date to unix date
            $tabDate = explode("-", $searchDate_start);
            $searchDate_s = mktime(0, 0, 0, $tabDate[1], $tabDate[2], $tabDate[0]);

            // convert end date to unix date
            $tabDate = explode("-", $searchDate_end);
            $searchDate_e = mktime(0, 0, 0, $tabDate[1], $tabDate[2] + 1, $tabDate[0]);

            if ($searchDate_e <= $searchDate_s) {
                $errormessage = "The start date must be before the end date";
                $this->render(array(
                    'lang' => $lang,
                    'id_space' => $id_space,
                    'errorMessage' => $errormessage,
                    'searchDate_start' => $searchDate_start,
                    'searchDate_end' => $searchDate_end
                ));
                return;
            }

            $champ = $this->request->getParameterNoException('champ');
            $type_recherche = $this->request->getParameterNoException('type_recherche');
            $text = $this->request->getParameterNoException('text');
            $contition_et_ou = $this->request->getParameterNoException('condition_et_ou');
            $entrySummary = $this->request->getParameterNoException('summary_rq');

            $reportModel = new BkReport();
            $table = $reportModel->reportstats($id_space, $searchDate_s, $searchDate_e, $champ, $type_recherche, $text, $contition_et_ou);

            $outputType = $this->request->getParameterNoException('output');



            if ($outputType == 1) { // only details
                $this->render(array(
                    'lang' => $lang,
                    'id_space' => $id_space,
                    'searchDate_start' => $searchDate_start,
                    'searchDate_end' => $searchDate_end,
                    'champ' => $champ,
                    'type_recherche' => $type_recherche,
                    'text' => $text,
                    'summary_rq' => $entrySummary,
                    'output' => $outputType,
                    'table' => $table
                ));
                return;
            } elseif ($outputType == 2) { // only summary
                $summaryTable = $reportModel->summaryseReportStats($table, $entrySummary);
                $this->render(array(
                    'lang' => $lang,
                    'id_space' => $id_space,
                    'searchDate_start' => $searchDate_start,
                    'searchDate_end' => $searchDate_end,
                    'champ' => $champ,
                    'type_recherche' => $type_recherche,
                    'text' => $text,
                    'summary_rq' => $entrySummary,
                    'output' => $outputType,
                    'summaryTable' => $summaryTable
                ));
                return;
            } elseif ($outputType == 3) { // details and summary
                $summaryTable = $reportModel->summaryseReportStats($table, $entrySummary);
                $this->render(array(
                    'lang' => $lang,
                    'id_space' => $id_space,
                    'searchDate_start' => $searchDate_start,
                    'searchDate_end' => $searchDate_end,
                    'champ' => $champ,
                    'type_recherche' => $type_recherche,
                    'text' => $text,
                    'summary_rq' => $entrySummary,
                    'output' => $outputType,
                    'table' => $table,
                    'summaryTable' => $summaryTable
                ));
                return;
            } elseif ($outputType == 4) { // details csv
                return $this->exportDetailsCSV($table, $lang);
            } elseif ($outputType == 5) { // summary csv
                $summaryTable = $reportModel->summaryseReportStats($table, $entrySummary);
                return $this->exportSummaryCSV($summaryTable);
            }
        }

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space
        ));
    }

    /**
     * Internal method for GRR stats
     * @param array $table
     * @param string $lang
     */
    private function exportDetailsCSV($table, $lang)
    {
        $content = "";
        $content.= ResourcesTranslator::Area($lang) . " ; "
                . ResourcesTranslator::resource($lang) . " ; "
                . BookingTranslator::Short_description($lang) . " ; "
                . CoreTranslator::Date($lang) . " ; "
                . BookingTranslator::Full_description($lang) . " ; "
                . BookingTranslator::Color_codes($lang) . " ; "
                . BookingTranslator::Recipient($lang) . " \r\n";


        foreach ($table as $t) {
            $content.= $t["area_name"] . "; ";
            $content.= $t["resource"] . "; ";
            $content.= $t["short_description"] . "; ";

            $date = date("d/m/Y à H:i", $t["start_time"]) . " - ";
            $date .= date("d/m/Y à H:i", $t["end_time"]);

            $content.= $date . ";";
            $content.= $t["full_description"] . "; ";
            $content.= $t["color"] . "; ";
            $content.= $t["login"] . " ";
            $content.= "\r\n";
        }

        if (getenv('PFM_MODE') == 'test') {
            return $content;
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=rapport.csv");
        echo $content;
    }

    /**
     * Internal method for GRR stats
     * @param array $table
     */
    private function exportSummaryCSV($summaryTable)
    {
        $countTable = $summaryTable['countTable'];
        $timeTable = $summaryTable['timeTable'];
        $resourcesNames = $summaryTable['resources'];
        $entrySummary = $summaryTable['entrySummary'];

        $content = "";

        // head
        $content .= " ; ";
        foreach ($resourcesNames as $name) {
            $content .= $name . " ; ";
        }
        $content .= "Total \r\n";

        // body
        $i = -1;
        $totalCG = 0;
        $totalHG = 0;
        foreach ($countTable as $coutT) {
            $i++;
            $content .= $entrySummary[$i] . " ; ";
            $j = -1;
            $totalC = 0;
            $totalH = 0;
            foreach ($coutT as $col) {
                $j++;
                $content .= "(" . $col . ") " . $timeTable[$entrySummary[$i]][$resourcesNames[$j]] / 3600 . " ; ";
                $totalC += $col;
                $totalH += $timeTable[$entrySummary[$i]][$resourcesNames[$j]];
            }
            $content .= "(" . $totalC . ") " . $totalH / 3600 . " ";
            $content .= "\r\n";
            $totalCG += $totalC;
            $totalHG += $totalH;
        }

        // total line
        $content .= "Total ; ";
        for ($i = 0; $i < count($resourcesNames); $i++) {
            // calcualte the sum
            $sumC = 0;
            $sumH = 0;
            for ($x = 0; $x < count($entrySummary); $x++) {
                $sumC += $countTable[$entrySummary[$x]][$resourcesNames[$i]];
                $sumH += $timeTable[$entrySummary[$x]][$resourcesNames[$i]];
            }
            $content .= "(" . $sumC . ") " . $sumH / 3600 . " ; ";
        }
        $content .= "(" . $totalCG . ")" . $totalHG / 3600;
        $content .= " \r\n ";

        if (getenv('PFM_MODE') == 'test') {
            return $content;
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=rapport.csv");
        echo $content;
    }
}
