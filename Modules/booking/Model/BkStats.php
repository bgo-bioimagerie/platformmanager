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

    public function generateStats($file, $idSpace, $period_begin, $period_end)
    {
        $modelResource = new ReCategory();
        $resources = $modelResource->getBySpace($idSpace);
        $modelVisa = new ReVisa();
        $instructors = $modelVisa->getAllInstructors($idSpace);
        $modelAuthorizations = new BkAuthorization();
        $countResourcesInstructor = array();

        // by instructor
        foreach ($resources as $resource) {
            foreach ($instructors as $instructor) {
                $authorizations = $modelAuthorizations->getForResourceInstructorPeriod($idSpace, $resource["id"], $instructor["id_instructor"], $period_begin, $period_end);
                $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]] = count($authorizations);
            }
        }


        // by unit
        $modelClients = new ClClient();
        $units = $modelClients->getAll($idSpace);
        $countResourcesUnit = array();
        foreach ($resources as $resource) {
            foreach ($units as $unit) {
                $authorizations = $modelAuthorizations->getFormResourceUnitPeriod($idSpace, $resource["id"], $unit["id"], $period_begin, $period_end);
                $countResourcesUnit[$resource["id"]][$unit["id"]] = count($authorizations);
            }
        }

        // summary
        $summary["total"] = $modelAuthorizations->getTotalForPeriod($idSpace, $period_begin, $period_end);
        $summary["distinctuser"] = $modelAuthorizations->getDistinctUserForPeriod($idSpace, $period_begin, $period_end);
        $summary["distinctvisa"] = $modelAuthorizations->getDistinctVisaForPeriod($idSpace, $period_begin, $period_end);
        $summary["distinctresource"] = $modelAuthorizations->getDistinctResourceForPeriod($idSpace, $period_begin, $period_end);
        $summary["newuser"] = $modelAuthorizations->getNewPeopleForPeriod($idSpace, $period_begin, $period_end);

        $this->generateXls($file, $resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end);
    }

    protected function generateXls($file, $resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Set properties
        $spreadsheet->getProperties()->setCreator("Platform-Manager");
        $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
        $spreadsheet->getProperties()->setTitle("Authorizations statistics");
        $spreadsheet->getProperties()->setSubject("Authorizations statistics");
        $spreadsheet->getProperties()->setDescription("");

        $stylesheet = $this->getStylesheet();
        // backward compat
        $stylesheet['borderedCell'] = $stylesheet['styleBorderedCell'];

        $cells = 'A1:H1';

        // print by instructors
        $spreadsheet->getActiveSheet()->setTitle("Autorisations par formateur");
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Autorisations par formateur du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));


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
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $modelUser->getUserFullName($instructor["id_instructor"]));
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
        $objWorkSheet->setTitle("Authorisations par unité");
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Autorisations par unité du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));

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
        $objWorkSheet->setTitle("Authorisations résumé");
        $spreadsheet->setActiveSheetIndex(2);

        $spreadsheet->getActiveSheet()->setTitle("Autorisations résumé");
        $spreadsheet->getActiveSheet()->mergeCells($cells);
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Résumé des autorisations du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));

        $spreadsheet->getActiveSheet()->SetCellValue('A3', "Nombre de formations");
        $spreadsheet->getActiveSheet()->SetCellValue('B3', $summary["total"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A4', "Nombre d'utilisateurs");
        $spreadsheet->getActiveSheet()->SetCellValue('B4', $summary["distinctuser"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A6', "Nombre de Visas");
        $spreadsheet->getActiveSheet()->SetCellValue('B6', $summary["distinctvisa"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A7', "Nombre de ressources");
        $spreadsheet->getActiveSheet()->SetCellValue('B7', $summary["distinctresource"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A8', "Nombre de nouveaux utilisateurs");
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


    public function getBalanceReport($filepath, $idSpace, $dateBegin, $dateEnd, $excludeColorCode, $generateclientstats, $lang='en')
    {
        $spreadsheet = $this->getBalance($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $generateclientstats, null, $lang);
        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        // record modifications and download file
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);
    }

    public function getBalance($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $generateclientstats, $spreadsheet, $lang='en')
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

        $spreadsheet = $this->statsReservationsPerMonth($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet);
        $spreadsheet = $this->statsReservationsPerResource($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet);
        if ($generateclientstats == 1) {
            $spreadsheet = $this->statsReservationsPerClient($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet);
        }
        return $spreadsheet;
    }

    public function statsReservationsPerMonth($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet, $lang='en')
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
        $data = $modelGraph->getStatReservationPerMonth($month_start, $year_start, $month_end, $year_end, $idSpace, $excludeColorCode);
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

    public function statsReservationsPerResource($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet, $lang='en')
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
        $data = $modelGraph->getStatReservationPerResource($dateBegin, $dateEnd, $idSpace, $excludeColorCode);

        $colorModel = new BkColorCode();
        $colorCodes = $colorModel->getColorCodes($idSpace);

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
                $timeColor = $modelGraph->getReservationPerResourceColor($idSpace, $dateBegin, $dateEnd, $resourcesids[$i], $c['id']);

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

            $objWorkSheet->getStyle('A' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('B' . $curentLine)->applyFromArray($style['styleBorderedCell']);
            $objWorkSheet->getStyle('C' . $curentLine)->applyFromArray($style['styleBorderedCell']);
        }
        return $spreadsheet;
    }

    public function statsReservationsPerClient($dateBegin, $dateEnd, $idSpace, $excludeColorCode, $spreadsheet, $lang='en')
    {
        // get data
        $modelGraph = new BkGraph();
        $modelClient = new ClClient();
        $clients = $modelClient->getAll($idSpace);
        $data = $modelGraph->getStatReservationPerClient($dateBegin, $dateEnd, $idSpace, $clients, $excludeColorCode);

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

    public function getQuantitiesReport($filepath, $idSpace, $dateBegin, $dateEnd, $lang='en')
    {
        $modelBooking = new BkCalendarEntry();
        $stats = $modelBooking->getStatsQuantities(
            $idSpace,
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

    public function getReservationsRespReport($filepath, $idSpace, $dateBegin, $dateEnd, $lang='en')
    {
        $modelBooking = new BkCalendarEntry();
        $stats = $modelBooking->getStatTimeResps(
            $idSpace,
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
