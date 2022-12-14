<?php

require_once 'Framework/Model.php';
require_once 'Framework/Utils.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/services/Model/ServicesTranslator.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/core/Model/CoreTranslator.php';


class SeOrderStats
{
    public function generateBalance($filepath, $id_space, $periodStart, $periodEnd, $spreadsheet=null, $lang='en')
    {
        // get all the opened projects informations
        $modelOrders = new SeOrder();
        $openedOrders = $modelOrders->getOrdersOpenedPeriod($id_space, $periodStart, $periodEnd);

        // get all the priced projects details
        $ordersBalance = $modelOrders->getPeriodeServicesBalancesOrders($id_space, $periodStart, $periodEnd);
        $ordersBilledBalance = $modelOrders->getPeriodeBilledServicesBalancesOrders($id_space, $periodStart, $periodEnd);

        // get the bill manager list
        $modelBillManager = new InInvoice();
        $controller = "servicesinvoiceorder";
        $invoices = $modelBillManager->getInvoicesPeriod($controller, $periodStart, $periodEnd, $id_space);

        $this->makeBalanceXlsFile($filepath, $id_space, $periodStart, $periodEnd, $openedOrders, $ordersBalance, $ordersBilledBalance, $invoices, $spreadsheet, $lang);
    }

    private function makeBalanceXlsFile($filepath, $id_space, $periodStart, $periodEnd, $openedOrders, $projectsBalance, $ordersBilledBalance, $invoices, $spreadsheet=null, $lang='en')
    {
        $modelUser = new CoreUser();
        $modelClient = new ClClient();

        // Create new PHPExcel object
        if ($spreadsheet == null) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Set properties
            $spreadsheet->getProperties()->setCreator("Platform-Manager");
            $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
            $spreadsheet->getProperties()->setTitle("Order balance sheet");
            $spreadsheet->getProperties()->setSubject("Order balance sheet");
            $spreadsheet->getProperties()->setDescription("");
        }

        // ////////////////////////////////////////////////////
        //              stylesheet
        // ////////////////////////////////////////////////////
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
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
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

        // ////////////////////////////////////////////////////
        //                  opened Orders
        // ////////////////////////////////////////////////////
        $spreadsheet->getActiveSheet()->mergeCells('A1:F1');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('E1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('F1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->setTitle(ServicesTranslator::OPENED($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('A2', CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->getStyle('A2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('B2', CoreTranslator::Unit($lang));
        $spreadsheet->getActiveSheet()->getStyle('B2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('C2', CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->getStyle('C2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('D2', ServicesTranslator::No_identification($lang));
        $spreadsheet->getActiveSheet()->getStyle('D2')->applyFromArray($styleBorderedCell);


        $spreadsheet->getActiveSheet()->SetCellValue('E2', ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('E2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('F2', ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('F2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 2;
        foreach ($openedOrders as $proj) {
            // responsable, unité, utilisateur, no dossier, nouvelle equipe (accademique, PME), nouveau proj(ac, pme), delai (def, respecte), date cloture
            $curentLine++;

            // getting client from user
            $user = $modelUser->getInfo($proj["id_user"]);
            $id_user = $user['id'];
            $modelClUser = new ClClientUser();
            // FIXME: array to string conversion that is displayed in generated spreadsheet instead of anything else when in debug mode
            // Should work not in debug mode
            $client = $modelClUser->getUserClientAccounts($id_space, $id_user);
            $clientName = $client ? $client[0]["name"] : "n/a";

            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $clientName /*$modelUser->getUserFUllName($proj["id_resp"])*/);
            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $clientName);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["no_identification"]);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            $dateClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCell);
        }

        for ($col = 'A'; $col !== 'G'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:F1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // ////////////////////////////////////////////////////
        //                Services billed details
        // ////////////////////////////////////////////////////

        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::Services_billed_details($lang));
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::No_identification($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, ServicesTranslator::Closed_date($lang));

        $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 5;
        $items = $ordersBilledBalance["items"];
        $modelItem = new SeService();

        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item, true) ?? Constants::UNKNOWN ;
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;

        $orders = $ordersBilledBalance["orders"];
        foreach ($orders as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["no_identification"]);
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    //print_r($entry);
                    $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($pos + 5) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($pos + 5) . $curentLine)->applyFromArray($styleBorderedCell);

                    //$itemsTotal[$idx] += floatval($entry["sum"]);
                }
            }
            //$spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($itemIdx) . $curentLine, $proj["total"]);
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = Utils::get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== Utils::get_col_letter($itemIdx + 1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // ////////////////////////////////////////////////////
        //                  Bill list
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::invoices($lang));
        $spreadsheet->setActiveSheetIndex(2);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);


        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, InvoicesTranslator::Number($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::Total_HT($lang));

        $total = 0;
        foreach ($invoices as $invoice) {
            $curentLine++;

            $unitName = $modelClient->getInstitution($id_space, $invoice["id_responsible"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($invoice["id_responsible"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $invoice["number"]);
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $invoice["total_ht"]);
            $total += $invoice["total_ht"];
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->mergeCells('A' . $curentLine . ':C' . $curentLine);
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, ServicesTranslator::Total_HT($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $total);

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'A'; $c !== 'E'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== 'E'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);


        // ////////////////////////////////////////////////////
        //                Services details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::Services_details($lang));
        $spreadsheet->setActiveSheetIndex(3);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::No_identification($lang));

        $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 4;
        $items = $projectsBalance["items"];
        $modelItem = new SeService();

        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item[0], true) ?? Constants::UNKNOWN;
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;

        $lastItemIdx = $itemIdx - 1;
        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 1) . $curentLine, ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 2) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 3) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);


        $projects = $projectsBalance["orders"];
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["no_identification"]);

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            $dateClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 1) . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 2) . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 3) . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            $offset = 4;
            $projItemCount = 0;
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos2($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($pos + $offset) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($pos + $offset) . $curentLine)->applyFromArray($styleBorderedCell);
                    $projItemCount += $entry["sum"];
                }
            }
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = Utils::get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== Utils::get_col_letter($itemIdx + 1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);
    }

    private function findItemPos($items, $id)
    {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item == $id) {
                return $c;
            }
        }
        return 0;
    }

    private function findItemPos2($items, $id)
    {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item["id"] == $id) {
                return $c;
            }
        }
        return 0;
    }
}

class SeStats extends Model
{
    public const STATS_PROJECTS = 'se_projects';
    public const STATS_PROJECT_SAMPLES = 'se_samples';
    public const STATS_MAIL_RESPS = 'se_mailresps';
    public const STATS_ORDERS = 'se_orders';

    public function generateBalanceReport($filepath, $id_space, $dateBegin, $dateEnd, $lang)
    {
        $spreadsheet = $this->getBalance($dateBegin, $dateEnd, $id_space);
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);
    }

    public function emailRespsReport($filepath, $id_space, $dateBegin, $dateEnd, $lang='en')
    {
        $modelProject = new SeProject();
        $data = $modelProject->getRespsPeriod(
            $id_space,
            $dateBegin,
            $dateEnd
        );


        $content = "name ; email \r\n";

        foreach ($data as $user) {
            $content.= $user["name"] . ";";
            $content.= $user["email"] . "\r\n";
        }

        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($filepath, $content);
    }

    public function samplesReport($filepath, $id_space, $lang)
    {
        $modelProject = new SeProject();
        $returnedSamples = $modelProject->getReturnedSamples($id_space);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Set properties
        $spreadsheet->getProperties()->setCreator("Platform-Manager");
        $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
        $spreadsheet->getProperties()->setTitle("Project balance sheet");
        $spreadsheet->getProperties()->setSubject("Project balance sheet");
        $spreadsheet->getProperties()->setDescription("");

        // ////////////////////////////////////////////////////
        //              stylesheet
        // ////////////////////////////////////////////////////
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

        $spreadsheet->getActiveSheet()->setTitle(ServicesTranslator::SamplesStock($lang));

        //responsable, unité,  utilisateur, no projet

        $spreadsheet->getActiveSheet()->SetCellValue('A1', CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('B1', ClientsTranslator::Institution($lang));
        $spreadsheet->getActiveSheet()->getStyle('B1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('C1', CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->getStyle('C1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('D1', ServicesTranslator::Project($lang));
        $spreadsheet->getActiveSheet()->getStyle('D1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('E1', ServicesTranslator::SampleReturn($lang));
        $spreadsheet->getActiveSheet()->getStyle('E1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('F1', CoreTranslator::Date($lang));
        $spreadsheet->getActiveSheet()->getStyle('F1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('G1', ServicesTranslator::StockSamples($lang));
        $spreadsheet->getActiveSheet()->getStyle('G1')->applyFromArray($styleBorderedCell);

        // projet; responsable, récupération matériel, date
        $curentLine = 1;
        foreach ($returnedSamples as $r) {
            $curentLine++;
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $r['resp']);
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $r['unit']);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $r['user']);
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $r['name']);
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, $r['samplereturn']);
            $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, CoreTranslator::dateFromEn($r['samplereturndate'], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, $r['sample_cabinet']);

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCell);
        }

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);
    }

    public function computeStatsProjects($id_space, $startDate_min, $startDate_max)
    {
        // total number of projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();

        // number of accademic and industry projects
        $numberAccademicProjects = 0;
        $numberIndustryProjects = 0;

        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {
            // get the responsible unit
            $clientInfo = $modelClient->get($id_space, $project["id_resp"]);
            $pricingInfo = $modelPricing->get($id_space, $clientInfo["pricing"]);
            if ($pricingInfo["type"] == 1) {
                $numberAccademicProjects++;
            } else {
                $numberIndustryProjects++;
            }
        }

        // number of new academic team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2, $id_space));
        $numberNewAccademicTeam = $req->rowCount();

        // number of new industry team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3, $id_space));
        $numberNewIndustryTeam = $req->rowCount();

        $purcentageNewIndustryTeam = 0;
        $purcentageloyaltyIndustryProjects = 0;
        if ($numberIndustryProjects > 0) {
            $purcentageNewIndustryTeam = round(100 * $numberNewIndustryTeam / $numberIndustryProjects);
            $purcentageloyaltyIndustryProjects = round(100 * ($numberIndustryProjects - $numberNewIndustryTeam) / $numberIndustryProjects);
        }

        $purcentageNewAccademicTeam = 0;
        $purcentageloyaltyAccademicProjects = 0;
        if ($numberAccademicProjects > 0) {
            $purcentageNewAccademicTeam = round(100 * $numberNewAccademicTeam / $numberAccademicProjects);
            $purcentageloyaltyAccademicProjects = round(100 * ($numberAccademicProjects - $numberNewAccademicTeam) / $numberAccademicProjects);
        }

        return array("numberNewIndustryTeam" => $numberNewIndustryTeam,
            "purcentageNewIndustryTeam" => $purcentageNewIndustryTeam,
            "numberIndustryProjects" => $numberIndustryProjects,
            "loyaltyIndustryProjects" => $numberIndustryProjects - $numberNewIndustryTeam,
            "purcentageloyaltyIndustryProjects" => $purcentageloyaltyIndustryProjects,
            "numberNewAccademicTeam" => $numberNewAccademicTeam,
            "purcentageNewAccademicTeam" => $purcentageNewAccademicTeam,
            "numberAccademicProjects" => $numberAccademicProjects,
            "loyaltyAccademicProjects" => $numberAccademicProjects - $numberNewAccademicTeam,
            "purcentageloyaltyAccademicProjects" => $purcentageloyaltyAccademicProjects,
            "totalNumberOfProjects" => $totalNumberOfProjects
        );
    }

    public function computeDelayStats($id_space, $periodStart, $periodEnd)
    {
        // total number of projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($periodStart, $periodEnd, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();

        if (count($projects) == 0) {
            return array(
                "numberIndustryProjectInDelay" => 0,
                      "percentageIndustryProjectInDelay" => 0,
                      "numberIndustryProjectOutDelay" => 0,
                      "percentageIndustryProjectOutDelay" => 0,
                      "numberAcademicProjectInDelay" => 0,
                      "percentageAcademicProjectInDelay" => 0,
                      "numberAcademicProjectOutDelay" => 0,
                      "percentageAcademicProjectOutDelay" => 0,

            );
        }

        // number of accademic and industry projects
        $numberIndustryProjectInDelay = 0;
        $numberIndustryProjectOutDelay = 0;
        $numberAcademicProjectInDelay = 0;
        $numberAcademicProjectOutDelay = 0;

        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {
            // get the responsible unit
            $clientInfo = $modelClient->get($id_space, $project["id_resp"]);
            $pricingInfo = $modelPricing->get($id_space, $clientInfo["pricing"]);

            $onTime = true;
            if ($project["date_close"] != "" && $project["date_close"] != null
                && $project["time_limit"] != "" && $project["time_limit"] != null) {
                if ($project["date_close"] > $project["time_limit"]) {
                    $onTime = false;
                }
            }

            if ($pricingInfo["type"] == 1) {
                if ($onTime) {
                    $numberIndustryProjectInDelay++;
                } else {
                    $numberIndustryProjectOutDelay++;
                }
            } else {
                if ($onTime) {
                    $numberAcademicProjectInDelay++;
                } else {
                    $numberAcademicProjectOutDelay++;
                }
            }
        }

        return array( "numberIndustryProjectInDelay" => $numberIndustryProjectInDelay,
                      "percentageIndustryProjectInDelay" => 100*($numberIndustryProjectInDelay / $totalNumberOfProjects),
                      "numberIndustryProjectOutDelay" => $numberIndustryProjectOutDelay,
                      "percentageIndustryProjectOutDelay" => 100*($numberIndustryProjectOutDelay / $totalNumberOfProjects),
                      "numberAcademicProjectInDelay" => $numberAcademicProjectInDelay,
                      "percentageAcademicProjectInDelay" => 100*($numberAcademicProjectInDelay / $totalNumberOfProjects),
                      "numberAcademicProjectOutDelay" => $numberAcademicProjectOutDelay,
                      "percentageAcademicProjectOutDelay" => 100*($numberAcademicProjectOutDelay / $totalNumberOfProjects),
            );
    }

    public function getResponsiblesCsv($id_space, $startDate_min, $startDate_max, $lang)
    {
        $sql = "select distinct id_resp from se_project where date_open >= ? AND date_open <= ?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();


        $modelUser = new CoreUser();


        $content = CoreTranslator::Name($lang) . ";" . CoreTranslator::Email($lang) . "\r\n";
        foreach ($projects as $project) {
            $userName = $modelUser->getUserFUllName($project["id_resp"]);
            $userMail = $modelUser->getEmail($project["id_resp"]);
            $content .= $userName . ";" . $userMail . "\r\n";
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=listing_responsible_sproject.csv");
        echo $content;
    }

    public function computeOriginStats($id_space, $periodStart, $periodEnd)
    {
        $academique = $this->computeSingleOriginStats($id_space, $periodStart, $periodEnd, 2);
        $private = $this->computeSingleOriginStats($id_space, $periodStart, $periodEnd, 3);

        return array("academique" => $academique, "private" => $private);
    }

    public function computeSingleOriginStats($id_space, $periodStart, $periodEnd, $academic_private)
    {
        $stats = array();

        $sql = "SELECT * FROM se_origin WHERE id_space = ? AND deleted=0 ORDER BY display_order ASC;";
        $origins = $this->runRequest($sql, array($id_space))->fetchAll();

        foreach ($origins as $origin) {
            $sql = "SELECT * FROM se_project WHERE date_open >= ? AND date_open <= ? AND id_space=? AND deleted=0 AND new_project=? AND id_origin=?";
            $req = $this->runRequest($sql, array($periodStart, $periodEnd, $id_space, $academic_private, $origin["id"]));

            $stats[] = array("id_origin" => $origin["id"], "origin" => $origin["name"], "count" => $req->rowCount());
        }
        return $stats;
    }

    public function computeStats($id_space, $startDate_min, $startDate_max)
    {
        // total number of projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, $id_space));
        $totalNumberOfProjects = $req->rowCount();
        $projects = $req->fetchAll();

        // number of accademic and industry projects
        $numberAccademicProjects = 0;
        $numberIndustryProjects = 0;

        $modelClient = new ClClient();
        $modelPricing = new ClPricing();

        foreach ($projects as $project) {
            // get the responsible unit
            $clientInfo = $modelClient->get($id_space, $project["id_resp"]);

            $pricingInfo = $modelPricing->get($id_space, $clientInfo["pricing"]);

            if ($pricingInfo["type"] == 1) {
                $numberAccademicProjects++;
            } else {
                $numberIndustryProjects++;
            }
        }

        // number of new academic projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2, $id_space));
        $numberNewAccademicProject = $req->rowCount();

        //echo "numberNewAccademicProject = " . $numberNewAccademicProject . "<br/>";
        // number of new academic team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 2, $id_space));
        $numberNewAccademicTeam = $req->rowCount();

        //echo "numberNewAccademicTeam = " . $numberNewAccademicTeam . "<br/>";
        // number of new industry projects
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_project=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3, $id_space));
        $numberNewIndustryProject = $req->rowCount();

        // number of new industry team
        $sql = "select * from se_project where date_open >= ? AND date_open <= ? AND new_team=?  AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($startDate_min, $startDate_max, 3, $id_space));
        $numberNewIndustryTeam = $req->rowCount();


        $purcentageNewIndustryTeam = 0;
        $purcentageloyaltyIndustryProjects = 0;
        if ($numberIndustryProjects > 0) {
            $purcentageNewIndustryTeam = round(100 * $numberNewIndustryTeam / $numberIndustryProjects);
            $purcentageloyaltyIndustryProjects = round(100 * ($numberIndustryProjects - $numberNewIndustryTeam) / $numberIndustryProjects);
        }

        $purcentageNewAccademicTeam = 0;
        $purcentageloyaltyAccademicProjects = 0;
        if ($numberAccademicProjects > 0) {
            $purcentageNewAccademicTeam = round(100 * $numberNewAccademicTeam / $numberAccademicProjects);
            $purcentageloyaltyAccademicProjects = round(100 * ($numberAccademicProjects - $numberNewAccademicTeam) / $numberAccademicProjects);
        }

        $output = array("numberNewIndustryTeam" => $numberNewIndustryTeam,
            "purcentageNewIndustryTeam" => $purcentageNewIndustryTeam,
            "numberIndustryProjects" => $numberIndustryProjects,
            "loyaltyIndustryProjects" => $numberIndustryProjects - $numberNewIndustryTeam,
            "purcentageloyaltyIndustryProjects" => $purcentageloyaltyIndustryProjects,
            "numberNewAccademicTeam" => $numberNewAccademicTeam,
            "purcentageNewAccademicTeam" => $purcentageNewAccademicTeam,
            "numberAccademicProjects" => $numberAccademicProjects,
            "loyaltyAccademicProjects" => $numberAccademicProjects - $numberNewAccademicTeam,
            "purcentageloyaltyAccademicProjects" => $purcentageloyaltyAccademicProjects,
            "totalNumberOfProjects" => $totalNumberOfProjects
        );
        return $output;
    }

    public function getBalance($periodStart, $periodEnd, $id_space, $isglobal = false, $spreadsheet=null, $lang='en')
    {
        // get all the opened projects informations
        $modelProjects = new SeProject();
        $openedProjects = $modelProjects->getProjectsOpenedPeriod($periodStart, $periodEnd, $id_space);

        // get all the priced projects details
        $projectsBalance = $modelProjects->getPeriodeServicesBalances($id_space, $periodStart, $periodEnd);

        $projectsBilledBalance = $modelProjects->getPeriodeBilledServicesBalances($id_space, $periodStart, $periodEnd);

        // get the stats
        $modelStats = new SeStats();
        $stats = $modelStats->computeStats($id_space, $periodStart, $periodEnd);
        $delayStats = $modelStats->computeDelayStats($id_space, $periodStart, $periodEnd);
        $statsOrigins = $modelStats->computeOriginStats($id_space, $periodStart, $periodEnd);

        // get the bill manager list
        $modelBillManager = new InInvoice();
        if ($isglobal) {
            $invoices = $modelBillManager->getAllInvoicesPeriod($periodStart, $periodEnd, $id_space);
        } else {
            $controller = "servicesinvoiceproject";
            $invoices = $modelBillManager->getInvoicesPeriod($controller, $periodStart, $periodEnd, $id_space);
        }

        return $this->makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $invoices, $stats, $delayStats, $statsOrigins, $spreadsheet, $lang);
    }

    private function makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $invoices, $stats, $delayStats, $statsOrigins, $spreadsheet=null, $lang='en')
    {
        $modelUser = new CoreUser();
        $modelClient = new ClClient();

        // Create new PHPExcel object
        if ($spreadsheet == null) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            // Set properties
            $spreadsheet->getProperties()->setCreator("Platform-Manager");
            $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
            $spreadsheet->getProperties()->setTitle("Project balance sheet");
            $spreadsheet->getProperties()->setSubject("Project balance sheet");
            $spreadsheet->getProperties()->setDescription("");
        }

        // ////////////////////////////////////////////////////
        //              stylesheet
        // ////////////////////////////////////////////////////
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
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
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
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
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

        // ////////////////////////////////////////////////////
        //                  opened projects
        // ////////////////////////////////////////////////////
        $spreadsheet->getActiveSheet()->mergeCells('A1:D1');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('F1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('H1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('J1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('K1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('L1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('M1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('N1')->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('O1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->setTitle(ServicesTranslator::OpenedUpper($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('A2', CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->getStyle('A2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('B2', ClientsTranslator::Client($lang));
        $spreadsheet->getActiveSheet()->getStyle('B2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('C2', CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->getStyle('C2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('D2', ServicesTranslator::Project_number($lang));
        $spreadsheet->getActiveSheet()->getStyle('D2')->applyFromArray($styleBorderedCell);


        $spreadsheet->getActiveSheet()->mergeCells('E1:F1');
        $spreadsheet->getActiveSheet()->SetCellValue('E1', ServicesTranslator::New_team($lang));
        $spreadsheet->getActiveSheet()->getStyle('E1')->applyFromArray($styleBorderedCenteredCell);

        $spreadsheet->getActiveSheet()->SetCellValue('E2', ServicesTranslator::Academique($lang));
        $spreadsheet->getActiveSheet()->getStyle('E2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('F2', ServicesTranslator::Industry($lang));
        $spreadsheet->getActiveSheet()->getStyle('F2')->applyFromArray($styleBorderedCell);


        $spreadsheet->getActiveSheet()->mergeCells('G1:H1');
        $spreadsheet->getActiveSheet()->SetCellValue('G1', ServicesTranslator::New_project($lang));
        $spreadsheet->getActiveSheet()->getStyle('G1')->applyFromArray($styleBorderedCenteredCell);

        $spreadsheet->getActiveSheet()->SetCellValue('G2', ServicesTranslator::Academique($lang));
        $spreadsheet->getActiveSheet()->getStyle('G2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('H2', ServicesTranslator::Industry($lang));
        $spreadsheet->getActiveSheet()->getStyle('H2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('I2', ServicesTranslator::Origin($lang));
        $spreadsheet->getActiveSheet()->getStyle('I2')->applyFromArray($styleBorderedCell);


        $spreadsheet->getActiveSheet()->SetCellValue('J2', ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('J2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('K2', ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->getStyle('K2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('L2', ServicesTranslator::OutDelay($lang));
        $spreadsheet->getActiveSheet()->getStyle('L2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('M2', ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('M2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('N2', ServicesTranslator::Visa($lang));
        $spreadsheet->getActiveSheet()->getStyle('N2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->mergeCells('O1:P1');
        $spreadsheet->getActiveSheet()->SetCellValue('O2', ServicesTranslator::SampleReturn($lang));
        $spreadsheet->getActiveSheet()->getStyle('O2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('P2', CoreTranslator::Date($lang));
        $spreadsheet->getActiveSheet()->getStyle('P2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('Q2', ServicesTranslator::StockSamples($lang));
        $spreadsheet->getActiveSheet()->getStyle('Q2')->applyFromArray($styleBorderedCell);


        $spreadsheet->getActiveSheet()->mergeCells('I1:N1');
        $spreadsheet->getActiveSheet()->getStyle('I1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $modelOrigin = new SeOrigin();
        $curentLine = 2;

        $modelVisa = new SeVisa();
        $pstats = ['in_charge' => [], 'client' => [], 'institution' => []];


        foreach ($openedProjects as $proj) {
            // responsable, client, utilisateur, no dossier, nouvelle equipe (accademique, PME), nouveau proj(ac, pme), delai (def, respecte), date cloture
            $curentLine++;

            $unitName = $modelClient->getName($id_space, $proj["id_resp"]);
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $visaName = $modelUser->getUserFUllName($visa["id_user"]);
            if (!array_key_exists($visaName, $pstats['in_charge'])) {
                $pstats['in_charge'][$visaName] = 0;
            }
            $pstats['in_charge'][$visaName] += 1;
            if (!array_key_exists($unitName, $pstats['client'])) {
                $pstats['client'][$unitName] = 0;
            }
            $pstats['client'][$unitName] += 1;
            $institutionName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            if (!array_key_exists($institutionName, $pstats['institution'])) {
                $pstats['institution'][$institutionName] = 0;
            }
            $pstats['institution'][$institutionName] += 1;
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $visaName);

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            if ($proj["new_team"] == 2) {
                $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, 1);
            } elseif ($proj["new_team"] == 3) {
                $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, 1);
            }
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


            if ($proj["new_project"] == 2) {
                $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
            } elseif ($proj["new_project"] == 3) {
                $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, 1);
            }
            $spreadsheet->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $spreadsheet->getActiveSheet()->getStyle('H' . $curentLine)->applyFromArray($styleBorderedCenteredCell);

            $dateClosed = "";
            $visaClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
                $proj["closed_by_in"] = array_key_exists("closed_by_in", $proj) ?: "n/a";
            }

            $spreadsheet->getActiveSheet()->SetCellValue('I' . $curentLine, $modelOrigin->getName($id_space, $proj["id_origin"]));
            $spreadsheet->getActiveSheet()->getStyle('I' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('J' . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('K' . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));

            $outDelay = 0;
            if (($proj["date_close"] != "" && $proj["date_close"] != "0000-00-00"
                && $proj["time_limit"] != "" && $proj["time_limit"] != "0000-00-00")
                && $proj["date_close"] > $proj["time_limit"]) {
                $outDelay = 1;
            }

            $proj["sample_cabinet"] = array_key_exists("sample_cabinet", $proj) ?: "n/a";

            $spreadsheet->getActiveSheet()->SetCellValue('L' . $curentLine, $outDelay);
            $spreadsheet->getActiveSheet()->SetCellValue('M' . $curentLine, $dateClosed);

            $spreadsheet->getActiveSheet()->SetCellValue('N' . $curentLine, $visaClosed);
            $spreadsheet->getActiveSheet()->SetCellValue('O' . $curentLine, $proj["samplereturn"]);
            $spreadsheet->getActiveSheet()->SetCellValue('P' . $curentLine, CoreTranslator::dateFromEn($proj["samplereturndate"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('Q' . $curentLine, $proj["sample_cabinet"]);

            $spreadsheet->getActiveSheet()->getStyle('J' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('K' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('L' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('M' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('N' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('O' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('P' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('Q' . $curentLine)->applyFromArray($styleBorderedCell);
        }

        for ($col = 'A'; $col !== 'Q'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:M1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // ////////////////////////////////////////////////////
        //                  Bill list
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::invoices($lang));
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->mergeCells('A' . $curentLine . ":" . 'E' . $curentLine);
        $spreadsheet->getActiveSheet()->mergeCells('F' . $curentLine . ":" . 'G' . $curentLine);
        $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, ServicesTranslator::New_team($lang));
        $spreadsheet->getActiveSheet()->mergeCells('H' . $curentLine . ":" . 'I' . $curentLine);
        $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, ServicesTranslator::New_project($lang));


        $curentLine = 2;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));

        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, ClientsTranslator::Institution($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, InvoicesTranslator::Number($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::Title($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, ServicesTranslator::Total_HT($lang));

        $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, ServicesTranslator::Academique($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, ServicesTranslator::Industry($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, ServicesTranslator::Academique($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('I' . $curentLine, ServicesTranslator::Industry($lang));

        $spreadsheet->getActiveSheet()->SetCellValue('J' . $curentLine, ServicesTranslator::Origin($lang));

        $spreadsheet->getActiveSheet()->SetCellValue('K' . $curentLine, ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('L' . $curentLine, ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('M' . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('N' . $curentLine, ServicesTranslator::Visa($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('O' . $curentLine, ServicesTranslator::Date_Send_Invoice($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('P' . $curentLine, ServicesTranslator::Visa_Send_Invoice($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('Q' . $curentLine, ServicesTranslator::SampleReturn($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('R' . $curentLine, CoreTranslator::Date($lang));


        $total = 0;
        $modelProject = new SeProject();
        $modelInvoiceVisa = new InVisa();
        $modelOrigin = new SeOrigin();



        foreach ($invoices as $invoice) {
            $curentLine++;

            $unitName = $modelClient->getInstitution($id_space, $invoice["id_responsible"]);
            $proj = null;
            $responsibleName = '';
            if ($invoice["controller"] == "servicesinvoiceproject") {
                $proj = $modelProject->getInfoFromInvoice($invoice['id'], $id_space);
                if ($proj != null) {
                    $visa = $modelVisa->get($id_space, $proj["in_charge"] ?? 0);
                    $responsibleName = $modelUser->getUserFUllName($visa["id_user"] ?? 0);
                }
            }

            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $responsibleName);
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $invoice["number"]);
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $invoice["title"]);
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, $invoice["total_ht"]);
            $spreadsheet->getActiveSheet()->SetCellValue('O' . $curentLine, CoreTranslator::dateFromEn($invoice["date_send"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('P' . $curentLine, $modelInvoiceVisa->getVisaNameShort($id_space, $invoice["visa_send"]));

            if ($invoice["controller"] == "servicesinvoiceproject" && $proj) {
                if (isset($proj["new_team"])) {
                    if ($proj["new_team"] == 2) {
                        $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, 1);
                    } elseif ($proj["new_team"] == 3) {
                        $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
                    }
                    $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
                    $spreadsheet->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


                    if ($proj["new_project"] == 2) {
                        $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, 1);
                    } elseif ($proj["new_project"] == 3) {
                        $spreadsheet->getActiveSheet()->SetCellValue('I' . $curentLine, 1);
                    }
                    $spreadsheet->getActiveSheet()->getStyle('H' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
                    $spreadsheet->getActiveSheet()->getStyle('I' . $curentLine)->applyFromArray($styleBorderedCenteredCell);

                    $spreadsheet->getActiveSheet()->SetCellValue('J' . $curentLine, $modelOrigin->getName($id_space, $proj["id_origin"]));
                    $spreadsheet->getActiveSheet()->getStyle('J' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


                    $dateClosed = "";
                    $visaClosed = "";
                    if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                        $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
                        $visaClosed = $proj["closed_by_in"];
                    }
                    $spreadsheet->getActiveSheet()->SetCellValue('K' . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
                    $spreadsheet->getActiveSheet()->SetCellValue('L' . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
                    $spreadsheet->getActiveSheet()->SetCellValue('M' . $curentLine, $dateClosed);
                    $spreadsheet->getActiveSheet()->SetCellValue('N' . $curentLine, $visaClosed);
                    $spreadsheet->getActiveSheet()->SetCellValue('Q' . $curentLine, $proj["samplereturn"]);
                    $spreadsheet->getActiveSheet()->SetCellValue('R' . $curentLine, CoreTranslator::dateFromEn($proj["samplereturndate"], $lang));
                    $spreadsheet->getActiveSheet()->getStyle('K' . $curentLine)->applyFromArray($styleBorderedCell);
                    $spreadsheet->getActiveSheet()->getStyle('L' . $curentLine)->applyFromArray($styleBorderedCell);
                    $spreadsheet->getActiveSheet()->getStyle('M' . $curentLine)->applyFromArray($styleBorderedCell);
                    $spreadsheet->getActiveSheet()->getStyle('N' . $curentLine)->applyFromArray($styleBorderedCell);
                    $spreadsheet->getActiveSheet()->getStyle('Q' . $curentLine)->applyFromArray($styleBorderedCell);
                    $spreadsheet->getActiveSheet()->getStyle('R' . $curentLine)->applyFromArray($styleBorderedCell);
                }
            }

            $total += $invoice["total_ht"];
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->mergeCells('A' . $curentLine . ':D' . $curentLine);
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::Total_HT($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, $total);

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'A'; $c !== 'Q'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== 'Q'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // ////////////////////////////////////////////////////
        //                  Stats
        // ////////////////////////////////////////////////////

        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::StatisticsMaj($lang));
        $spreadsheet->setActiveSheetIndex(2);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::numberNewIndustryTeam($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberNewIndustryTeam"] . " (" . $stats["purcentageNewIndustryTeam"] . "%)");
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::numberIndustryProjects($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberIndustryProjects"]);
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::loyaltyIndustryProjects($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["loyaltyIndustryProjects"] . " (" . $stats["purcentageloyaltyIndustryProjects"] . "%)");
        $curentLine++;
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::numberNewAccademicTeam($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberNewAccademicTeam"] . " (" . $stats["purcentageNewAccademicTeam"] . "%)");
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::numberAccademicProjects($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberAccademicProjects"]);
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::loyaltyAccademicProjects($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["loyaltyAccademicProjects"] . " (" . $stats["purcentageloyaltyAccademicProjects"] . "%)");
        $curentLine++;
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::totalNumberOfProjects($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["totalNumberOfProjects"]);

        $curentLine++;
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::industryProjectInDelay($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $delayStats["numberIndustryProjectInDelay"] . " (" . round($delayStats["percentageIndustryProjectInDelay"]) . "%)");
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::industryProjectOutDelay($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $delayStats["numberIndustryProjectOutDelay"] . " (" . round($delayStats["percentageIndustryProjectOutDelay"]) . "%)");
        $curentLine++;
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::academicProjectInDelay($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $delayStats["numberAcademicProjectInDelay"] . " (" . round($delayStats["percentageAcademicProjectInDelay"]) . "%)");
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, ServicesTranslator::academicProjectOutDelay($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $delayStats["numberAcademicProjectOutDelay"] . " (" . round($delayStats["percentageAcademicProjectOutDelay"]) . "%)");


        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'A'; $c !== 'C'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== 'C'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        $curentLine = 3;
        $spreadsheet->getActiveSheet()->setCellValue('D2', 'Projects per responsible');
        foreach ($pstats['in_charge'] as $key=>$value) {
            $spreadsheet->getActiveSheet()->setCellValue('D'.$curentLine, $key);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$curentLine, $value);
            $curentLine += 1;
        }
        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'D'; $c !== 'F'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'D'; $col !== 'F'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $curentLine = 3;
        $spreadsheet->getActiveSheet()->setCellValue('G2', 'Projects per client');
        foreach ($pstats['client'] as $key=>$value) {
            $spreadsheet->getActiveSheet()->setCellValue('G'.$curentLine, $key);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$curentLine, $value);
            $curentLine += 1;
        }
        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'G'; $c !== 'I'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'G'; $col !== 'I'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $curentLine = 3;
        $spreadsheet->getActiveSheet()->setCellValue('J2', 'Projects per institution');
        foreach ($pstats['institution'] as $key=>$value) {
            $spreadsheet->getActiveSheet()->setCellValue('J'.$curentLine, $key);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$curentLine, $value);
            $curentLine += 1;
        }
        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 'J'; $c !== 'L'; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($c . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'J'; $col !== 'L'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        // ////////////////////////////////////////////////////
        //                  Origin
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::OriginsMaj($lang));
        $spreadsheet->setActiveSheetIndex(3);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, ServicesTranslator::Academique($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, ServicesTranslator::Industry($lang));

        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);

        $acc = $statsOrigins['academique'];
        $private = $statsOrigins['private'];
        for ($i = 0; $i < count($acc); $i++) {
            $curentLine++;
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $acc[$i]['origin']);
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $acc[$i]['count']);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $private[$i]['count']);

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::OriginsFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);


        // ////////////////////////////////////////////////////
        //                Services billed details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::Services_billed_details($lang));
        $spreadsheet->setActiveSheetIndex(4);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->setCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, ClientsTranslator::Institution($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::No_Projet($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, ServicesTranslator::Closed_date($lang));

        $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 5;
        $items = $projectsBilledBalance["items"];
        $modelItem = new SeService();

        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item, true) ?? Constants::UNKNOWN;
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;

        $projects = $projectsBilledBalance["projects"];

        foreach ($projects as $proj) {
            $curentLine++;
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($visa["id_user"] ?? 0));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"] ?? 0));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($pos + 5) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($pos + 5) . $curentLine)->applyFromArray($styleBorderedCell);
                }
            }
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = Utils::get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== Utils::get_col_letter($itemIdx + 1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);



        // ////////////////////////////////////////////////////
        //                Services details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::Services_details($lang));
        $spreadsheet->setActiveSheetIndex(5);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, ClientsTranslator::Institution($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::No_Projet($lang));

        $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 4;
        $items = $projectsBalance["items"];
        $modelItem = new SeService();

        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item[0], true) ?? Constants::UNKNOWN;
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;

        $lastItemIdx = $itemIdx - 1;
        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 1) . $curentLine, ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 2) . $curentLine, ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 3) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

        $projects = $projectsBalance["projects"];
        foreach ($projects as $proj) {
            $curentLine++;

            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $visaName = $modelUser->getUserFUllName($visa["id_user"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $visaName);
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            $dateClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 1) . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 2) . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($lastItemIdx + 3) . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            $offset = 4;
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos2($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    $spreadsheet->getActiveSheet()->SetCellValue(Utils::get_col_letter($pos + $offset) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($pos + $offset) . $curentLine)->applyFromArray($styleBorderedCell);
                }
            }
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = Utils::get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle(Utils::get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== Utils::get_col_letter($itemIdx + 1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        return $spreadsheet;
    }

    private function findItemPos($items, $id)
    {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item == $id) {
                return $c;
            }
        }
        return 0;
    }

    private function findItemPos2($items, $id)
    {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item["id"] == $id) {
                return $c;
            }
        }
        return 0;
    }
}
