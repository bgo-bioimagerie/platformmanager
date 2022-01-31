<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/statistics/Model/StatisticsTranslator.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeStats.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Model/SeVisa.php';
require_once 'Modules/invoices/Model/InVisa.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/services/Controller/ServicesController.php';

/**
 * 
 * @author sprigent
 * Used by statisticsglobal
 */
class ServicesstatisticsprojectController extends ServicesController {

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

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("begining_period");
        if ($date_begin == "") {
            $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
            if ($date_begin != "") {
                $dateArray = explode("-", $date_begin);
                $y = date("Y") - 1;
                $m = $dateArray[1];
                $d = $dateArray[2];
                $date_begin = $y . "-" . $m . "-" . $d;
            } else {
                $date_begin = date("Y", time()) . "-01-01";
            }
        }
        $date_end = $this->request->getParameterNoException("end_period");
        if ($date_end == "") {
            $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);
            if ($date_end != "") {
                $dateArray = explode("-", $date_end);
                $y = date("Y");
                $m = $dateArray[1];
                $d = $dateArray[2];
                $date_end = $y . "-" . $m . "-" . $d;
            } else {
                $date_end = date("Y", time()) . "-12-31";
            }
        }
        
        // build the form
        $form = new Form($this->request, "formbalancesheet");
        $form->setTitle(ServicesTranslator::Projects_balance($lang), 3);
        $form->addDate("begining_period", ServicesTranslator::Beginning_period($lang), true, $date_begin);
        $form->addDate("end_period", ServicesTranslator::End_period($lang), true, $date_end);

        $form->setValidationButton("Ok", "servicesstatisticsproject/" . $id_space);

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

    public function getBalance($periodStart, $periodEnd, $id_space, $isglobal = false, $spreadsheet=null) {
        return $this->generateBalance($id_space, $periodStart, $periodEnd, false, $isglobal, $spreadsheet);
    }

    private function generateBalance($id_space, $periodStart, $periodEnd, $render = true, $isglobal = false, $spreadsheet=null) {

        //echo "not yet implemented <br/> " . $periodStart . "<br/>" . $periodEnd . "<br/>";
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

        return $this->makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $invoices, $stats, $delayStats, $statsOrigins, $render, $spreadsheet);
    }

    private function makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $invoices, $stats, $delayStats, $statsOrigins, $render, $spreadsheet=null) {

        $modelUser = new CoreUser();
        $modelClient = new ClClient();

        $lang = $this->getLanguage();
        // Create new PHPExcel object
        if($spreadsheet == null) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            //$spreadsheet = new PHPExcel();
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

            $unitName = $modelClient->getName($id_space ,$proj["id_resp"]);
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $visaName = $modelUser->getUserFUllName($visa["id_user"]);
            if(!array_key_exists($visaName, $pstats['in_charge'])) { $pstats['in_charge'][$visaName] = 0; }
            $pstats['in_charge'][$visaName] += 1;
            if(!array_key_exists($unitName, $pstats['client'])) { $pstats['client'][$unitName] = 0; }
            $pstats['client'][$unitName] += 1;
            $institutionName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            if(!array_key_exists($institutionName, $pstats['institution'])) { $pstats['institution'][$institutionName] = 0; }
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
            } else if ($proj["new_team"] == 3) {
                $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, 1);
            }
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


            if ($proj["new_project"] == 2) {
                $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
            } else if ($proj["new_project"] == 3) {
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
                && $proj["date_close"] > $proj["time_limit"]){
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

            $unitName = $modelClient->getInstitution($id_space ,$invoice["id_responsible"]);
            $proj = null;
            $responsibleName = '';
            if ($invoice["controller"] == "servicesinvoiceproject") {
                $proj = $modelProject->getInfoFromInvoice($invoice['id'], $id_space);
                $visa = $modelVisa->get($id_space, $proj["in_charge"]);
                $responsibleName = $modelUser->getUserFUllName($visa["id_user"]);
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
                    } else if ($proj["new_team"] == 3) {
                        $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
                    }
                    $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
                    $spreadsheet->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


                    if ($proj["new_project"] == 2) {
                        $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, 1);
                    } else if ($proj["new_project"] == 3) {
                        $spreadsheet->getActiveSheet()->SetCellValue('I' . $curentLine, 1);
                    }
                    $spreadsheet->getActiveSheet()->getStyle('H' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
                    $spreadsheet->getActiveSheet()->getStyle('I' . $curentLine)->applyFromArray($styleBorderedCenteredCell);

                    //$originName = $modelOrigin->getName($proj["id_origin"]);
                    
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


        //print_r($delayStats);

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
        foreach($pstats['in_charge'] as $key=>$value) {
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
        foreach($pstats['client'] as $key=>$value) {
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
        foreach($pstats['institution'] as $key=>$value) {
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
        $objWorkSheet->setTitle(ServicesTranslator::Sevices_billed_details($lang));
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
            $name = $modelItem->getItemName($id_space, $item);
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
        }
        $itemIdx++;
        //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, ServicesTranslator::TotalPrice($lang));

        $projects = $projectsBilledBalance["projects"];
        
        foreach ($projects as $proj) {
            $curentLine++;
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $unitName = $modelClient->getInstitution($id_space ,$proj["id_resp"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($visa["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
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
        //                Services details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $spreadsheet->createSheet();
        $objWorkSheet->setTitle(ServicesTranslator::Sevices_details($lang));
        $spreadsheet->setActiveSheetIndex(5);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, ClientsTranslator::Institution($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, ServicesTranslator::No_Projet($lang));
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

        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 2) . $curentLine, ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 2) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 3) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx + 3) . $curentLine)->applyFromArray($styleBorderedCell);

        //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, ServicesTranslator::TotalPrice($lang));

        $projects = $projectsBalance["projects"];
        //print_r($projects);
        foreach ($projects as $proj) {
            $curentLine++;
            
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            $visa = $modelVisa->get($id_space, $proj["in_charge"]);
            $visaName = $modelUser->getUserFUllName($visa["id_user"]);
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $visaName);
            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
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
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx + 2) . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
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

        if ($render) {

            // write excel file
            //$objWriter = new PHPExcel_Writer_Excel2007($spreadsheet);
            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

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
        } else {
            return $spreadsheet;
        }
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

    public function samplesreturnAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

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

        if(getenv('PFM_MODE') == 'test'){
            $f = tempnam('/tmp', 'statistics').'.xlsx';
            $objWriter->save($f);
            return ['data' => ['file' => $f]];
        }
        //On enregistre les modifications et on met en téléchargement le fichier Excel obtenu
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="platorm-manager-samples-return.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    public function mailrespsAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("begining_period");
        if ($date_begin == "") {
            $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
            $dateArray = explode("-", $date_begin);
            $y = date("Y") - 1;
            $m = $dateArray[1] ?? 1;
            $d = $dateArray[2] ?? 1;
            $date_begin = $y . "-" . $m . "-" . $d;
        }
        $date_end = $this->request->getParameterNoException("end_period");
        if ($date_end == "") {
            $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);
            $dateArray = explode("-", $date_end);
            $y = date("Y");
            $m = $dateArray[1] ?? 12;
            $d = $dateArray[2] ?? 31;
            $date_end = $y . "-" . $m . "-" . $d;
        }

        // build the form
        $form = new Form($this->request, "formmailresps");
        $form->setTitle(ServicesTranslator::emailsResponsibles($lang), 3);
        $form->addDate("begining_period", ServicesTranslator::Beginning_period($lang), true, $date_begin);
        $form->addDate("end_period", ServicesTranslator::End_period($lang), true, $date_end);

        $form->setValidationButton("Ok", "servicesstatisticsmailresps/" . $id_space);

        if ($form->check()) {

            $modelProject = new SeProject();
            $data = $modelProject->getRespsPeriod(
                    $id_space, 
                    CoreTranslator::dateToEn($this->request->getParameter('begining_period'), $lang), 
                    CoreTranslator::dateToEn($this->request->getParameter('end_period'), $lang)
            );


            $content = "name ; email \r\n";

            foreach ($data as $user) {
                $content.= $user["name"] . ";";
                $content.= $user["email"] . "\r\n";
            }

            if(getenv('PFM_MODE') == 'test') {
                return ['data' => ['stats' => $content]];
            }

            // export csv
            header("Content-Type: application/csv-tab-delimited-table");
            header("Content-disposition: filename=bookingusers.csv");
            echo $content;
            return;
        }

        $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

}
