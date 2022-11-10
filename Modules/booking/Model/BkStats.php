<?php

require_once 'Framework/Utils.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreTranslator.php';

require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkGraph.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';


require_once 'Modules/clients/Model/ClClient.php';

class BkStats
{
    public const STATS_AUTH_STAT = 'bk_auth_stats';
    public const STATS_AUTH_LIST = 'bk_auth_list';
    public const STATS_BK_USERS = 'bk_users';
    public const STATS_BK = 'bk_bk';
    public const STATS_MANUAL = 'bk_manual';
    public const STATS_QUANTITIES = 'bk_quantities';
    public const STATS_BK_TIME = 'bk_time';

    public function generateStats($file, $id_space, $period_begin, $period_end, $lang='en')
    {
        $modelResource = new ReCategory();
        $resources = $modelResource->getBySpace($id_space);
        $modelVisa = new ReVisa();
        $instructors = $modelVisa->getAllInstructors($id_space);
        $modelAuthorizations = new BkAuthorization();
        $countResourcesInstructor = array();

        // by instructor
        foreach ($resources as $resource) {
            foreach ($instructors as $instructor) {
                $authorizations = $modelAuthorizations->getForResourceInstructorPeriod($id_space, $resource["id"], $instructor["id_instructor"], $period_begin, $period_end);
                $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]] = count($authorizations);
            }
        }


        // by unit
        $modelClients = new ClClient();
        $units = $modelClients->getAll($id_space);
        $countResourcesUnit = array();
        foreach ($resources as $resource) {
            foreach ($units as $unit) {
                $authorizations = $modelAuthorizations->getFormResourceUnitPeriod($id_space, $resource["id"], $unit["id"], $period_begin, $period_end);
                $countResourcesUnit[$resource["id"]][$unit["id"]] = count($authorizations);
            }
        }

        // summary
        $summary["total"] = $modelAuthorizations->getTotalForPeriod($id_space, $period_begin, $period_end);
        $summary["distinctuser"] = $modelAuthorizations->getDistinctUserForPeriod($id_space, $period_begin, $period_end);
        $summary["distinctvisa"] = $modelAuthorizations->getDistinctVisaForPeriod($id_space, $period_begin, $period_end);
        $summary["distinctresource"] = $modelAuthorizations->getDistinctResourceForPeriod($id_space, $period_begin, $period_end);
        $summary["newuser"] = $modelAuthorizations->getNewPeopleForPeriod($id_space, $period_begin, $period_end);

        $this->generateXls($file, $resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end, $lang);
    }

    protected function generateXls($file, $resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end, $lang='en')
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Set properties
        $spreadsheet->getProperties()->setCreator("Platform-Manager");
        $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
        $spreadsheet->getProperties()->setTitle(BookingTranslator::Authorisations_statistics($lang));
        $spreadsheet->getProperties()->setSubject(BookingTranslator::Authorisations_statistics($lang));
        $spreadsheet->getProperties()->setDescription("");

        $stylesheet = $this->getStylesheet();
        // backward compat
        $stylesheet['borderedCell'] = $stylesheet['styleBorderedCell'];

        $cells = 'A1:H1';

        // print by instructors
        $spreadsheet->getActiveSheet()->setTitle(BookingTranslator::authorisationsByInstructor($lang));
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', BookingTranslator::authorisationsByInstructor($lang, CoreTranslator::dateFromEn($period_begin, $lang), CoreTranslator::dateFromEn($period_end, $lang)));


        $curentLine = 3;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = Utils::get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = Utils::get_col_letter($num);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);


        $modelUser = new CoreUser();
        $instructorsStartLine = $curentLine + 1;
        foreach ($instructors as $instructor) {
            $curentLine++;
            $letter = Utils::get_col_letter(1);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $modelUser->getUserFUllName($instructor["id_instructor"]));
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = Utils::get_col_letter($num);
                $val = $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]];
                if ($val == 0) {
                    $val = "";
                }
                $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += intval($val);
            }
            $num++;
            $letter = Utils::get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, 'Total');
        for ($i = 0; $i < count($resources); $i++) {
            $letter = Utils::get_col_letter($i + 2);
            $sumEnd = $curentLine - 1;
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $instructorsStartLine . ':' . $letter . $sumEnd . ')');
        }
        $letter = Utils::get_col_letter(count($resources) + 2);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $instructorsStartLine . ':' . $letter . $sumEnd . ')');

        // by unit
        $objWorkSheet = $spreadsheet->createSheet(1);
        $objWorkSheet->setTitle(BookingTranslator::authorisationsByClient($lang));
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', BookingTranslator::authorisationsByClient($lang, CoreTranslator::dateFromEn($period_begin, $lang), CoreTranslator::dateFromEn($period_end, $lang)));

        $curentLine = 2;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = Utils::get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = Utils::get_col_letter($num);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

        $unitsStartLine = $curentLine;
        foreach ($units as $unit) {
            $curentLine++;
            $letter = Utils::get_col_letter(1);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $unit["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = Utils::get_col_letter($num);
                $val = $countResourcesUnit[$resource["id"]][$unit["id"]];
                $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += intval($val);
            }
            $num++;
            $letter = Utils::get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, 'Total');
        for ($i = 0; $i < count($resources); $i++) {
            $letter = Utils::get_col_letter($i + 2);
            $sumEnd = $curentLine - 1;
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $unitsStartLine . ':' . $letter . $sumEnd . ')');
        }
        $letter = Utils::get_col_letter(count($resources) + 2);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $unitsStartLine . ':' . $letter . $sumEnd . ')');

        // print summary
        $objWorkSheet = $spreadsheet->createSheet(2);
        $objWorkSheet->setTitle(BookingTranslator::authorisationsSummary($lang));
        $spreadsheet->setActiveSheetIndex(2);

        $spreadsheet->getActiveSheet()->setTitle(BookingTranslator::authorisationsSummary($lang));
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', BookingTranslator::authorisationsSummary($lang, CoreTranslator::dateFromEn($period_begin, $lang), CoreTranslator::dateFromEn($period_end, $lang)));

        $spreadsheet->getActiveSheet()->SetCellValue('A3', BookingTranslator::numberOfTrainings($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B3', $summary["total"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A4', BookingTranslator::numberOfUsers($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B4', $summary["distinctuser"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A6', BookingTranslator::numberOfVisas($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B6', $summary["distinctvisa"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A7', BookingTranslator::numberOfResources($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B7', $summary["distinctresource"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A8', BookingTranslator::numberOfNewUsers($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B8', $summary["newuser"]);

        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        // record modifications and download file
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($file);
    }

    /**
     * Internal method to export booking users into csv
     * @param array $table
     * @param string $lang
     */
    public function exportstatbookingusersCSV($file, $users)
    {
        $content = "name ; email \r\n";

        foreach ($users as $user) {
            $content.= $user["name"] . ";";
            $content.= $user["email"] . "\r\n";
        }

        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($file, $content);
    }


    public function getBalanceReport($filepath, $id_space, $dateBegin, $dateEnd, $excludeColorCode, $generateclientstats, $lang='en')
    {
        $spreadsheet = $this->getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateclientstats, null, $lang);
        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        // record modifications and download file
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);
    }

    public function getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateclientstats, $spreadsheet, $lang='en')
    {
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
        }
        return $spreadsheet;
    }

    public function statsReservationsPerMonth($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet, $lang='en')
    {
        if ($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if ($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }
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

    public function statsReservationsPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet, $lang='en')
    {
        if ($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if ($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }

        // get data
        $modelGraph = new BkGraph();
        $data = $modelGraph->getStatReservationPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode);

        $colorModel = new BkColorCode();
        $colorCodes = $colorModel->getColorCodes($id_space);

        $objWorkSheet = $spreadsheet->createSheet();
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
            $letter = Utils::get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $c["name"]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        }
        $num++;
        $letter = Utils::get_col_letter($num);
        $objWorkSheet->SetCellValue($letter . $curentLine, BookingTranslator::ReservationCancelled_number($lang));
        $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
        $num++;
        $letter = Utils::get_col_letter($num);
        $objWorkSheet->SetCellValue($letter . $curentLine, BookingTranslator::ReservationCancelled_time($lang));
        $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
        $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);


        $sups = [];
        for ($i = 0; $i < count($data['resource']); $i++) {
            foreach ($data['supDatas'][$i] as $key => $value) {
                $sups[] = $key;
                $num++;
                $letter = Utils::get_col_letter($num);
                $objWorkSheet->SetCellValue($letter . $curentLine, $key);
                $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
                $spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true); 
            }

        }

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

                $letter = Utils::get_col_letter($num);
                $objWorkSheet->SetCellValue($letter . $curentLine, $timeColor);
                $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            }
            $num++;
            $letter = Utils::get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $data['countCancelled'][$i]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $num++;
            $letter = Utils::get_col_letter($num);
            $objWorkSheet->SetCellValue($letter . $curentLine, $data['timeCancelled'][$i]);
            $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);

            foreach ($sups as $sup) {
                $num++;
                $letter = Utils::get_col_letter($num);
                $objWorkSheet->SetCellValue($letter . $curentLine, $data['supDatas'][$i][$sup] ?? 0);
                $objWorkSheet->getStyle($letter . $curentLine)->applyFromArray($style['styleBorderedCell']);
            }

            $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        }
        return $spreadsheet;
    }

    public function statsReservationsPerClient($dateBegin, $dateEnd, $id_space, $excludeColorCode, $spreadsheet, $lang='en')
    {
        // get data
        $modelGraph = new BkGraph();
        $modelClient = new ClClient();
        $clients = $modelClient->getAll($id_space);
        $data = $modelGraph->getStatReservationPerClient($dateBegin, $dateEnd, $id_space, $clients, $excludeColorCode);

        $objWorkSheet = $spreadsheet->createSheet();
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
                $colLetter = Utils::get_col_letter($curentCol);
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
                    $colLetter = Utils::get_col_letter($curentCol);
                    $objWorkSheet->SetCellValue($colLetter . $curentLine, $data[$i]["client_" . $client['id']][$catstat]);
                    $objWorkSheet->getStyle($colLetter . $curentLine)->applyFromArray($style['styleBorderedCell']);
                }
            }
        }
        return $spreadsheet;
    }

    protected function getStylesheet()
    {
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

    public function getQuantitiesReport($filepath, $id_space, $dateBegin, $dateEnd, $lang='en')
    {
        $modelBooking = new BkCalendarEntry();
        $stats = $modelBooking->getStatsQuantities(
            $id_space,
            $dateBegin,
            $dateEnd,
            $lang
        );
        if (empty($stats)) {
            throw new PfmParamException('no data found for this period');
        }
        $data = '';
        foreach ($stats as $stat) {
            $data .= $stat['name'].';'.$stat['count']."\n";
        }
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($filepath, $data);
    }

    public function getReservationsRespReport($filepath, $id_space, $dateBegin, $dateEnd, $lang='en')
    {
        $modelBooking = new BkCalendarEntry();
        $stats = $modelBooking->getStatTimeResps(
            $id_space,
            $dateBegin,
            $dateEnd
        );
        $csv = ",";
        foreach ($stats["resources"] as $resoure) {
            $csv .= $resoure["name"] . ",";
        }
        $csv .= "\n";
        foreach ($stats["count"] as $data) {
            $csv .= $data["responsible"] . ",";
            foreach ($data["count"] as $count) {
                $csv .= $count["time"] . ",";
            }
            $csv .= "\n";
        }

        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($filepath, $csv);
    }
}
