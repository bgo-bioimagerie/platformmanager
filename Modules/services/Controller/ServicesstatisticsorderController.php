<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/statistics/Model/StatisticsTranslator.php';


require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SePrice.php';
//require_once 'Modules/services/Model/SeStats.php';

require_once 'Modules/core/Model/CoreTranslator.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Model/InInvoice.php';

// require_once 'externals/PHPExcel/Classes/PHPExcel.php';
require_once 'Modules/services/Controller/ServicesController.php';


class ServicesstatisticsorderController extends ServicesController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // build the form
        $form = new Form($this->request, "formbalancesheet");
        $form->setTitle(ServicesTranslator::OrderBalance($lang), 3);
        $form->addDate("begining_period", ServicesTranslator::Beginning_period($lang), true, "");
        $form->addDate("end_period", ServicesTranslator::End_period($lang), true, "");

        $form->setButtonsWidth(2, 9);
        $form->setValidationButton("Ok", "servicesstatisticsorder/".$id_space);

        $stats = "";
        if ($form->check()) {
            $date_start = CoreTranslator::dateToEn($form->getParameter("begining_period"), $lang);
            $date_end = CoreTranslator::dateToEn($form->getParameter("end_period"), $lang);
            $f = $this->generateBalance($id_space, $date_start, $date_end);
            return ['data' => ['file' => $f]];
        }

        // set the view
        $formHtml = $form->getHtml($lang);
        // view
        $this->render(array(
            "id_space" => $id_space, 
            "lang" => $lang,
            'formHtml' => $formHtml,
            'stats' => $stats
        ));
        
    }

    private function generateBalance($id_space, $periodStart, $periodEnd, $spreadsheet=null) {

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

        return $this->makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedOrders, $ordersBalance, $ordersBilledBalance, $invoices, $spreadsheet);
    }

    private function makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedOrders, $projectsBalance, $ordersBilledBalance, $invoices, $spreadsheet=null) {

        $modelUser = new CoreUser();
        $modelClient = new ClClient();

        $lang = $this->getLanguage();
        // Create new PHPExcel object
        if($spreadsheet == null) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            // $spreadsheet = new PHPExcel();

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

        //$spreadsheet->getActiveSheet()->mergeCells('I1:K1');
        //$spreadsheet->getActiveSheet()->getStyle('I1')->applyFromArray($styleBorderedCell);

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
            // $unitName = $modelUnit->getUnitName($modelUser->getUnit($proj["id_resp"]));

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
            $name = $modelItem->getItemName($id_space, $item);
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;
        //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, ServicesTranslator::TotalPrice($lang));

        $orders = $ordersBilledBalance["orders"];
        foreach ($orders as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUnit($proj["id_resp"]));
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
                    $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($pos + 5) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($pos + 5) . $curentLine)->applyFromArray($styleBorderedCell);

                    //$itemsTotal[$idx] += floatval($entry["sum"]);
                }
            }
            //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $proj["total"]);
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = $this->get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== $this->get_col_letter($itemIdx + 1); $col++) {
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
        //$spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, InvoicesTranslator::Number($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::Total_HT($lang));

        $total = 0;
        foreach ($invoices as $invoice) {
            $curentLine++;

            $unitName = $modelClient->getInstitution($id_space, $invoice["id_responsible"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUnit($invoice["id_responsible"]));
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($invoice["id_responsible"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            //$spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($invoice["id_user"]));
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
        //$spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, ServicesTranslator::Closed_date($lang));

        $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
        //$spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 4;
        $items = $projectsBalance["items"];
        //print_r($items);
        $modelItem = new SeService();

        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item[0]);
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;

        $lastItemIdx = $itemIdx - 1;
        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 1) . $curentLine, ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 2) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 3) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

        //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, ServicesTranslator::TotalPrice($lang));

        $projects = $projectsBalance["orders"];
        //print_r($projects);
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUnit($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["no_identification"]);
            //$spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));

            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            //$spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

            $dateClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00") {
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 1) . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 2) . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 3) . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 1) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            $offset = 4;
            $projItemCount = 0;
            foreach ($entries as $entry) {
                // print_r($entry);
                // echo "<br/>";
                $idx++;
                $pos = $this->findItemPos2($items, $entry["id"]);
                //echo "id = " . $entry["id"] . " pos = " . $pos . "<br/>";
                if ($pos > 0 && $entry["pos"] > 0) {
                   
                    $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($pos + $offset) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($pos + $offset) . $curentLine)->applyFromArray($styleBorderedCell);
                    $projItemCount += $entry["sum"];
                    //$itemsTotal[$idx] += floatval($entry["sum"]);
                }
            }
            //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $proj["total"]);
            //if($projItemCount == 0){
            //    $spreadsheet->getActiveSheet()->removeRow($curentLine);
            //    $curentLine--;
            //}
        }

        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = $this->get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(" . $colLetter . "2:" . $colLetter . $lastLine . ")");
        }

        for ($r = 1; $r <= $curentLine; $r++) {
            for ($c = 1; $c <= $itemIdx; $c++) {
                $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($c) . $r)->applyFromArray($styleBorderedCell);
            }
        }
        for ($col = 'A'; $col !== $this->get_col_letter($itemIdx + 1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);

        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        if(getenv('PFM_MODE') == 'test') {
            $f = tempnam('/tmp', 'statistics').'.xlsx';
            $objWriter->save($f);
            return $f;

        }
        //On enregistre les modifications et on met en téléchargement le fichier Excel obtenu
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="platorm-manager-projet-bilan.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    private function findItemPos($items, $id) {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item == $id) {
                return $c;
            }
        }
        return 0;
    }
    
    private function findItemPos2($items, $id) {
        $c = 0;
        foreach ($items as $item) {
            $c++;
            if ($item["id"] == $id) {
                return $c;
            }
        }
        return 0;
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
