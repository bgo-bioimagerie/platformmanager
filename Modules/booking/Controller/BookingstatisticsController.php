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
class BookingstatisticsController extends StatisticsController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("booking");
        $_SESSION["openedNav"] = "statistics";
    }
    
    public function statquantitiesAction($id_space){
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $form = new Form($this->request, "bookingStatQuantities");
        $form->setTitle(BookingTranslator::statQuantities($lang));
        $form->addDate("datebegin", BookingTranslator::Date_Begin($lang), true);
        $form->addDate("dateend", BookingTranslator::Date_End($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "statquantities/" .$id_space);
        
        if ($form->check()){
        
            $modelBooking = new BkCalendarEntry();
            $stats = $modelBooking->getStatsQuantities(
                    $id_space,
                    CoreTranslator::dateToEn($form->getParameter("datebegin"), $lang),
                    CoreTranslator::dateToEn($form->getParameter("dateend"), $lang)
                    );

            $stats = !empty($stats) ?: "No data found for this period";
            // TODO: link that to a report generation
            print_r($stats);
            return;
        }
        
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang)
        ));
        
        
    }

    public function statreservationrespAction($id_space){
        
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $form = new Form($this->request, "bookingStatTimeResp");
        $form->setTitle(BookingTranslator::statResp($lang));
        $form->addDate("datebegin", BookingTranslator::Date_Begin($lang), true);
        $form->addDate("dateend", BookingTranslator::Date_End($lang), true);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingstatreservationresp/" .$id_space);
        
        if ($form->check()){
        
            $modelBooking = new BkCalendarEntry();
            $stats = $modelBooking->getStatTimeResps(
                    $id_space,
                    CoreTranslator::dateToEn($form->getParameter("datebegin"), $lang),
                    CoreTranslator::dateToEn($form->getParameter("dateend"), $lang)
                    );
            
            $csv = ",";
            foreach ( $stats["resources"] as $resoure ){
                $csv .= $resoure["name"] . ",";
            }
            $csv .= "\n";
            foreach ( $stats["count"] as $data ){
                $csv .= $data["responsible"] . ",";
                foreach( $data["count"] as $count ){
                    $csv .= $count["time"] . ",";
                }
                $csv .= "\n";
            }
            
            header('Content-Disposition: attachment; filename="filename.csv";');
            echo $csv;
            return;
        }
        
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang)
        ));
    }
    
    /**
     * Statistics form pages
     */
    public function statreservationsAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("date_begin");
        if ($date_begin == "") {
            $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
            $dateArray = explode("-", $date_begin);
            $y = date("Y") - 1;
            $m = $dateArray[1] ?? '1';
            $d = $dateArray[2] ?? '1';
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
        $form->setButtonsWidth(2, 8);

        if ($form->check()) {
            $dateBegin = CoreTranslator::dateToEn($form->getParameter("date_begin"), $lang);
            $dateEnd = CoreTranslator::dateToEn($form->getParameter("date_end"), $lang);
            $excludeColorCode = $this->request->getParameter("exclude_color");
            $generateclientstats = $this->request->getParameter("generateclientstats");

            //require_once 'externals/PHPExcel/Classes/PHPExcel.php';
            $spreadsheet = $this->getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateclientstats, null);
            // write excel file
            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="platorm-manager-bilan.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
            return;
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
    public function statreservationsqueryAction($id_space) {
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

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);
        $resourcesNumber = count($resources);

        $modelResourceC = new ReCategory();
        $resourcesCategory = $modelResourceC->getBySpace($id_space);
        $resourcesCategoryNumber = count($resourcesCategory);
        // render data
        $camembertCount = $modelGraph->getCamembertArray($id_space, $month_start, $year_start, $month_end, $year_end);
        $camembertTimeCount = $modelGraph->getCamembertTimeArray($id_space, $month_start, $year_start, $month_end, $year_end);

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=booking_stats.csv");

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
        echo $content;
        return;
    }

    public function statbookingusersAction($id_space) {

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
        $form = new Form($this->request, "sygrrifstats/statbookingusers");
        $form->setTitle(BookingTranslator::bookingusersstats($lang));
        $form->addDate('startdate', BookingTranslator::Date_Begin($lang), true, $date_begin);
        $form->addDate('enddate', BookingTranslator::Date_End($lang), true, $date_end);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingusersstats/" . $id_space);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "statistics/" . $id_space);

        if ($form->check()) {
            // run the database query
            $model = new BkStatsUser();
            $users = $model->bookingUsers($id_space, CoreTranslator::dateToEn($form->getParameter("startdate"), $lang), CoreTranslator::dateToEn($form->getParameter("enddate"), $lang));

            $this->exportstatbookingusersCSV($users);
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

    /**
     * Internal method to export booking users into csv
     * @param array $table
     * @param string $lang
     */
    private function exportstatbookingusersCSV($users) {

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=bookingusers.csv");

        $content = "name ; email \r\n";

        foreach ($users as $user) {
            $content.= $user["name"] . ";";
            $content.= $user["email"] . "\r\n";
        }
        echo $content;
    }

    public function grrAction($id_space) {
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
            $date_debut = $tabDate[2] . '/' . $tabDate[1] . '/' . $tabDate[0];
            $searchDate_s = mktime(0, 0, 0, $tabDate[1], $tabDate[2], $tabDate[0]);

            // convert end date to unix date
            $tabDate = explode("-", $searchDate_end);
            $date_fin = $tabDate[2] . '/' . $tabDate[1] . '/' . $tabDate[0];
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

            //print_r($champ);
            //print_r($type_recherche);
            //print_r($text);

            $reportModel = new BkReport();
            $table = $reportModel->reportstats($id_space, $searchDate_s, $searchDate_e, $champ, $type_recherche, $text, $contition_et_ou);

            //print_r($table);

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
            } else if ($outputType == 2) { // only summary
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
            } else if ($outputType == 3) { // details and summary
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
            } else if ($outputType == 4) { // details csv
                $this->exportDetailsCSV($table, $lang);
                return;
            } else if ($outputType == 5) { // summary csv
                $summaryTable = $reportModel->summaryseReportStats($table, $entrySummary);
                $this->exportSummaryCSV($summaryTable, $lang);
                return;
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
    private function exportDetailsCSV($table, $lang) {

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=rapport.csv");

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
        echo $content;
    }

    /**
     * Internal method for GRR stats
     * @param array $table
     * @param string $lang
     */
    private function exportSummaryCSV($summaryTable, $lang) {
        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=rapport.csv");

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
        echo $content;
    }

    public function statsReservationsPerMonth($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet) {

        $dateBeginArray = explode("-", $dateBegin);
        $month_start = $dateBeginArray[1];
        $year_start = $dateBeginArray[0];
        $dateEndArray = explode("-", $dateEnd);
        $month_end = $dateEndArray[1];
        $year_end = $dateEndArray[0];

        // get data
        $modelGraph = new BkGraph();
        $data = $modelGraph->getStatReservationPerMonth($month_start, $year_start, $month_end, $year_end, $id_space, $excludeColorCode);

        $objWorkSheet = $spreadsheet->createSheet();
        $lang = $this->getLanguage();
        $objWorkSheet->setTitle(BookingTranslator::Reservation_counting($lang));
        $objWorkSheet->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $objWorkSheet->SetCellValue('A' . $curentLine, BookingTranslator::Month($lang));
        $objWorkSheet->SetCellValue('B' . $curentLine, BookingTranslator::Reservation_number($lang));
        $objWorkSheet->SetCellValue('C' . $curentLine, BookingTranslator::Reservation_time($lang));

        $style = $this->getStylesheet();
        $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);

        $dates = $data['dates'];
        $counting = $data['count'];
        $time = $data['time'];

        for ($i = 0; $i < count($data['dates']); $i++) {
            $curentLine++;
            $objWorkSheet->SetCellValue('A' . $curentLine, $dates[$i]);
            $objWorkSheet->SetCellValue('B' . $curentLine, $counting[$i]);
            $objWorkSheet->SetCellValue('C' . $curentLine, $time[$i]);

            $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        }
        return $spreadsheet;
    }

    public function statsReservationsPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet) {

        $dateBeginArray = explode("-", $dateBegin);
        $month_start = $dateBeginArray[1];
        $year_start = $dateBeginArray[0];
        $dateEndArray = explode("-", $dateEnd);
        $month_end = $dateEndArray[1];
        $year_end = $dateEndArray[0];

        // get data
        $modelGraph = new BkGraph();
        $data = $modelGraph->getStatReservationPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode);

        $colorModel = new BkColorCode();
        $colorCodes = $colorModel->getColorCodes($id_space);

        $objWorkSheet = $spreadsheet->createSheet();
        $lang = $this->getLanguage();
        $objWorkSheet->setTitle(BookingTranslator::Reservation_per_resource($lang));
        $objWorkSheet->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $objWorkSheet->SetCellValue('A' . $curentLine, BookingTranslator::Resource($lang));
        $objWorkSheet->SetCellValue('B' . $curentLine, BookingTranslator::Reservation_number($lang));
        $objWorkSheet->SetCellValue('C' . $curentLine, BookingTranslator::Reservation_time($lang));

        $style = $this->getStylesheet();
        $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $num = 3;
        foreach ($colorCodes as $c) {
            $num++;
            $letter = $this->get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $c["name"]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        }
        $num++;
        $letter = $this->get_col_letter($num);
        $objWorkSheet->SetCellValue($letter . $curentLine, BookingTranslator::ReservationCancelled_number($lang));
        $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $num++;
        $letter = $this->get_col_letter($num);
        $objWorkSheet->SetCellValue($letter . $curentLine, BookingTranslator::ReservationCancelled_time($lang));
        $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);


        $resourcesids = $data['resourcesids'];
        $resources = $data['resource'];
        $counting = $data['count'];
        $time = $data['time'];

        for ($i = 0; $i < count($resources); $i++) {
            $curentLine++;
            $objWorkSheet->SetCellValue('A' . $curentLine, $resources[$i]);
            $objWorkSheet->SetCellValue('B' . $curentLine, $counting[$i]);
            $objWorkSheet->SetCellValue('C' . $curentLine, $time[$i]);
            $num = 3;
            foreach ($colorCodes as $c) {
                $num++;
                $timeColor = $modelGraph->getReservationPerResourceColor($id_space, $dateBegin, $dateEnd, $resourcesids[$i], $c['id']);

                $letter = $this->get_col_letter($num);
                $objWorkSheet->SetCellValue($letter . $curentLine, $timeColor);
                $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            }
            $num++;
            $letter = $this->get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $data['countCancelled'][$i]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $num++;
            $letter = $this->get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $data['timeCancelled'][$i]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);

            $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        }
        return $spreadsheet;
    }

    public function statsReservationsPerClient($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet) {
        // get data
        $modelGraph = new BkGraph();
        $modelClient = new ClClient();
        $clients = $modelClient->getAll($id_space);
        $data = $modelGraph->getStatReservationPerClient($dateBegin, $dateEnd, $id_space, $clients, $excludeColorCode);

        $objWorkSheet = $spreadsheet->createSheet();
        $lang = $this->getLanguage();
        $objWorkSheet->setTitle(BookingTranslator::Reservation_per_client($lang));
        $objWorkSheet->getRowDimension('1')->setRowHeight(40);

        $curentLine = -1;
        for ($catstat = 0; $catstat <= 1; $catstat++) { // number reservation = 0, time reservation = 1
            $curentLine+=2;
            if ($catstat == 0) {
                $title = BookingTranslator::NumberResaPerClientFrom($lang) . CoreTranslator::dateFromEn($dateBegin, $lang)
                        . BookingTranslator::To($lang) . CoreTranslator::dateFromEn($dateEnd, $lang);
            } else {
                $title = BookingTranslator::TimeResaPerClientFrom($lang) . CoreTranslator::dateFromEn($dateBegin, $lang)
                        . BookingTranslator::To($lang) . CoreTranslator::dateFromEn($dateEnd, $lang);
            }
            $objWorkSheet->SetCellValue('A' . $curentLine, $title);

            $curentLine++;
            $curentCol = 1;
            $style = $this->getStylesheet();
            foreach ($clients as $client) {
                $curentCol++;
                $colLetter = $this->get_col_letter($curentCol);
                $objWorkSheet->SetCellValue($colLetter . $curentLine, $client['name']);
                $objWorkSheet->getStyle($colLetter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            }

            for ($i = 0; $i < count($data); $i++) {
                $curentLine++;

                $resource = $data[$i]['resource'];
                $objWorkSheet->SetCellValue('A' . $curentLine, $resource);
                $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);

                $curentCol = 1;
                foreach ($clients as $client) {
                    $curentCol++;
                    $colLetter = $this->get_col_letter($curentCol);
                    $objWorkSheet->SetCellValue($colLetter . $curentLine, $data[$i]["client_" . $client['id']][$catstat]);
                    $objWorkSheet->getStyle($colLetter . $curentLine)->applyFromArray($style['styleBorderedCell']);
                }
            }
        }
        return $spreadsheet;
    }

    /**
     * @deprecated since ec_user depreciation
     */
    public function statsReservationsPerResponsible($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet) {

        $dateBeginArray = explode("-", $dateBegin);
        $month_start = $dateBeginArray[1];
        $year_start = $dateBeginArray[0];
        $dateEndArray = explode("-", $dateEnd);
        $month_end = $dateEndArray[1];
        $year_end = $dateEndArray[0];

        // get data
        $modelGraph = new BkGraph();
        $modelClients = new ClClient();
        
        $clients = $modelClients->getAll($id_space);
        $data = $modelGraph->getStatReservationPerResponsible($dateBegin, $dateEnd, $id_space, $resps, $excludeColorCode);

        $objWorkSheet = $spreadsheet->createSheet();

        $lang = $this->getLanguage();
        $title = BookingTranslator::Reservation_per_responsible($lang);

        // Maximum 31 characters allowed in sheet title
        if (strlen($title) > 31) {
            $title = substr($title, 0, 30);
        }
            
        $objWorkSheet->setTitle($title);
        $objWorkSheet->getRowDimension('1')->setRowHeight(40);

        $curentLine = -1;
        for ($catstat = 0; $catstat <= 1; $catstat++) { // number reservation = 0, time reservation = 1
            $curentLine+=2;
            if ($catstat == 0) {
                $title = BookingTranslator::NumberResaPerClientFrom($lang) . CoreTranslator::dateFromEn($dateBegin, $lang)
                        . BookingTranslator::To($lang) . CoreTranslator::dateFromEn($dateEnd, $lang);
            } else {
                $title = BookingTranslator::TimeResaPerClientFrom($lang) . CoreTranslator::dateFromEn($dateBegin, $lang)
                        . BookingTranslator::To($lang) . CoreTranslator::dateFromEn($dateEnd, $lang);
            }
            $objWorkSheet->SetCellValue('A' . $curentLine, $title);


            $curentLine++;
            $curentCol = 1;
            $style = $this->getStylesheet();
            foreach ($clients as $client) {
                $curentCol++;
                $colLetter = $this->get_col_letter($curentCol);
                $objWorkSheet->SetCellValue($colLetter . $curentLine, $client['name']);
                $objWorkSheet->getStyle($colLetter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            }

            for ($i = 0; $i < count($data); $i++) {
                $curentLine++;

                $resource = $data[$i]['resource'];
                $objWorkSheet->SetCellValue('A' . $curentLine, $resource);
                $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);

                $curentCol = 1;
                foreach ($clients as $client) {
                    $curentCol++;
                    $colLetter = $this->get_col_letter($curentCol);
                    $objWorkSheet->SetCellValue($colLetter . $curentLine, $data[$i]["client_" . $client['id']][$catstat]);
                    $objWorkSheet->getStyle($colLetter . $curentLine)->applyFromArray($style['styleBorderedCell']);
                }
            }
        }
        return $spreadsheet;
    }

    protected function getStylesheet() {

        $styleBorderedCell = array(
            'font' => array(
                'name' => 'Times',
                'size' => 10,
                'bold' => false,
                'color' => array(
                    'rgb' => '000000'
                ),
            ),
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'),
                ),
            ),
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'ffffff',
                ),
            ),
            'alignment' => array(
                'wrap' => false,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ),
        );

        $styleBorderedCenteredCell = array(
            'font' => array(
                'name' => 'Times',
                'size' => 10,
                'bold' => false,
                'color' => array(
                    'rgb' => '000000'
                ),
            ),
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'),
                ),
            ),
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'ffffff',
                ),
            ),
            'alignment' => array(
                'wrap' => false,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
        );

        return array('styleBorderedCell' => $styleBorderedCell,
            'styleBorderedCenteredCell' => $styleBorderedCenteredCell);
    }

    public function getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateclientstats, $spreadsheet) {

        if (!$spreadsheet) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Set properties
            $spreadsheet->getProperties()->setCreator("Platform-Manager");
            $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
            $spreadsheet->getProperties()->setTitle("Booking balance sheet");
            $spreadsheet->getProperties()->setSubject("Booking balance sheet");
            $spreadsheet->getProperties()->setDescription("");
        }

        $spreadsheet = $this->statsReservationsPerMonth($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet);
        $spreadsheet = $this->statsReservationsPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet);
        if ($generateclientstats == 1) {
            $spreadsheet = $this->statsReservationsPerClient($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet);
            // call to deprecated function statsReservationsPerResponsible()
            // $spreadsheet = $this->statsReservationsPerResponsible($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet);
        }
        return $spreadsheet;
    }

    function get_col_letter($num) {
        $comp = 0;
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        //if the number is greater than 26, calculate to get the next letters
        if ($num > 26) {
            //divide the number by 26 and get rid of the decimal
            $comp = floor($num / 26);

            //add the letter to the end of the result and return it
            if ($comp != 0) {
                // don't subtract 1 if the comparative variable is greater than 0
                return $this->get_col_letter($comp) . $letters[($num - $comp * 26)];
            } else {
                return $this->get_col_letter($comp) . $letters[($num - $comp * 26) - 1];
            }
        } else {
            //return the letter
            return $letters[($num - 1)];
        }
    }

}
