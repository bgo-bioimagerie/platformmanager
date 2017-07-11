<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeProject.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesbalanceController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("statistics");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        
        $form = new Form($this->request, "sevicesbalanceform");
        $form->addSeparator(ServicesTranslator::ServicesBalance($lang));
        $form->addDate("period_begin", ServicesTranslator::Beginning_period($lang), true);
        $form->addDate("period_end", ServicesTranslator::End_period($lang), true);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesbalance");
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $date_start = CoreTranslator::dateToEn($form->getParameter("period_begin"), $lang);
            $date_end = CoreTranslator::dateToEn($form->getParameter("period_end"), $lang);
            $this->generateBalance($date_start, $date_end);
            return;
        }
        
        $htmlForm = $form->getHtml($lang);
        $this->render(array("htmlForm" => $htmlForm, "id_space" => $id_space, "lang" => $lang));
    }
    
    private function generateBalance($periodStart, $periodEnd) {

        //echo "not yet implemented <br/> " . $periodStart . "<br/>" . $periodEnd . "<br/>";
        // get all the opened projects informations
        $modelProjects = new SeProject();
        $openedProjects = $modelProjects->getProjectsOpenedPeriod($periodStart, $periodEnd);
        
        // get all the priced projects details
        $projectsBalance = $modelProjects->getPeriodeServicesBalances($periodStart, $periodEnd);
        $projectsBilledBalance = $modelProjects->getPeriodeBilledServicesBalances($periodStart, $periodEnd);

        // get the stats
        $modelStats = new SeStats();
        $stats = $modelStats->computeStatsProjects($periodStart, $periodEnd);

        $this->makeBalanceXlsFile($periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $stats);
    }
    
    private function makeBalanceXlsFile($periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $stats) {

        $modelUser = new EcUser();
        $modelUnit = new EcUnit();

        $lang = $this->getLanguage();
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Platform-Manager");
        $objPHPExcel->getProperties()->setLastModifiedBy("Platform-Manager");
        $objPHPExcel->getProperties()->setTitle("Services balance sheet");
        $objPHPExcel->getProperties()->setSubject("Services balance sheet");
        $objPHPExcel->getProperties()->setDescription("");

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
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'),
                ),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'ffffff',
                ),
            ),
            'alignment' => array(
                'wrap' => false,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'),
                ),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'ffffff',
                ),
            ),
            'alignment' => array(
                'wrap' => false,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );
		
        // ////////////////////////////////////////////////////
        //                  opened projects
        // ////////////////////////////////////////////////////
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($styleBorderedCell);
        
        
        
        $objPHPExcel->getActiveSheet()->setTitle(SpTranslator::OPENED($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', CoreTranslator::Responsible($lang));
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('B2', CoreTranslator::Unit($lang));
        $objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('C2', CoreTranslator::User($lang));
        $objPHPExcel->getActiveSheet()->getStyle('C2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('D2', SpTranslator::Project_number($lang));
        $objPHPExcel->getActiveSheet()->getStyle('D2')->applyFromArray($styleBorderedCell);


        $objPHPExcel->getActiveSheet()->mergeCells('E1:F1');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', SpTranslator::New_team($lang));
        $objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($styleBorderedCenteredCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('E2', SpTranslator::Academique($lang));
        $objPHPExcel->getActiveSheet()->getStyle('E2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('F2', SpTranslator::Industry($lang));
        $objPHPExcel->getActiveSheet()->getStyle('F2')->applyFromArray($styleBorderedCell);


        $objPHPExcel->getActiveSheet()->mergeCells('G1:H1');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', SpTranslator::New_project($lang));
        $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($styleBorderedCenteredCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('G2', SpTranslator::Academique($lang));
        $objPHPExcel->getActiveSheet()->getStyle('G2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('H2', SpTranslator::Industry($lang));
        $objPHPExcel->getActiveSheet()->getStyle('H2')->applyFromArray($styleBorderedCell);


        $objPHPExcel->getActiveSheet()->SetCellValue('I2', SpTranslator::Opened_date($lang));
        $objPHPExcel->getActiveSheet()->getStyle('I2')->applyFromArray($styleBorderedCell);
        
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', SpTranslator::Time_limite($lang));
        $objPHPExcel->getActiveSheet()->getStyle('J2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue('K2', SpTranslator::Closed_date($lang));
        $objPHPExcel->getActiveSheet()->getStyle('K2')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->mergeCells('I1:K1');
        $objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 2;
        foreach ($openedProjects as $proj) {
            // responsable, unité, utilisateur, no dossier, nouvelle equipe (accademique, PME), nouveau proj(ac, pme), delai (def, respecte), date cloture
            $curentLine++;
            $unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);

            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);

            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $objPHPExcel->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);

            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            if ($proj["new_team"] == 1) {
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $curentLine, 1);
            } else if ($proj["new_team"] == 2) {
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $curentLine, 1);
            }
            $objPHPExcel->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


            if ($proj["new_project"] == 1) {
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
            } else if ($proj["new_project"] == 2) {
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $curentLine, 1);
            }
            $objPHPExcel->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $curentLine)->applyFromArray($styleBorderedCenteredCell);

            $dateClosed = "";
            if ($proj["date_close"] != "0000-00-00"){
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $curentLine, $dateClosed);
            $objPHPExcel->getActiveSheet()->getStyle('I' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('K' . $curentLine)->applyFromArray($styleBorderedCell);

        }
        
        for($col = 'A'; $col !== 'L'; $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $objPHPExcel->getActiveSheet()->insertNewRowBefore(1, 1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $text = SpTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . SpTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $text);
                
        // ////////////////////////////////////////////////////
        //                Services billed details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $objPHPExcel->createSheet();
        $objWorkSheet->setTitle(SpTranslator::Sevices_billed_details($lang));
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 1;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $curentLine, SpTranslator::No_Projet($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $curentLine, SpTranslator::Closed_date($lang));
        
        $objPHPExcel->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 5;
        $items = $projectsBilledBalance["items"];
        $modelItem = new SpItem();
        
        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($item);
            $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
            
        }
        $itemIdx++;
        //$objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, SpTranslator::TotalPrice($lang));

        $projects = $projectsBilledBalance["projects"];
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));
            
            $objPHPExcel->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);
            
            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    //print_r($entry);
                    $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($pos + 5) . $curentLine, $entry["sum"]);
                    $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($pos + 5) . $curentLine)->applyFromArray($styleBorderedCell);
                
                    //$itemsTotal[$idx] += floatval($entry["sum"]);
                }
                
            }
            //$objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $proj["total"]);
        }
        
        // total services sum
        $itemIdx = 5;
        $lastLine = $curentLine;
        $curentLine++;
        foreach ($items as $itemsT) {
            $itemIdx++;
            $colLetter = $this->get_col_letter($itemIdx);
            $objPHPExcel->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(".$colLetter."3:".$colLetter.$lastLine.")");
        }
        
        for($r=1 ; $r <= $curentLine ; $r++){
            for($c=1 ; $c <= $itemIdx ; $c++){
                $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($c).$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== $this->get_col_letter($itemIdx+1); $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
        
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(1, 1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $text = SpTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . SpTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $text);
        
        // ////////////////////////////////////////////////////
        //                  Stats
        // ////////////////////////////////////////////////////
        $objWorkSheet = $objPHPExcel->createSheet();
        $objWorkSheet->setTitle(SpTranslator::StatisticsMaj($lang));
        $objPHPExcel->setActiveSheetIndex(3);

        $curentLine = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::numberNewIndustryTeam($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberNewIndustryTeam"] . " (" . $stats["purcentageNewIndustryTeam"] . "%)");
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::numberIndustryProjects($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberIndustryProjects"]);
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::loyaltyIndustryProjects($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["loyaltyIndustryProjects"] . " (" . $stats["purcentageloyaltyIndustryProjects"] . "%)");
        $curentLine++;
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::numberNewAccademicTeam($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberNewAccademicTeam"] . " (" . $stats["purcentageNewAccademicTeam"] . "%)");
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::numberAccademicProjects($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["numberAccademicProjects"]);
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::loyaltyAccademicProjects($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["loyaltyAccademicProjects"] . " (" . $stats["purcentageloyaltyAccademicProjects"] . "%)");
        $curentLine++;
        $curentLine++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, SpTranslator::totalNumberOfProjects($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $stats["totalNumberOfProjects"]);

        for($r=1 ; $r <= $curentLine ; $r++){
            for($c='A' ; $c !== 'C' ; $c++){
                $objPHPExcel->getActiveSheet()->getStyle($c.$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== 'C'; $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
        
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(1, 1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $text = SpTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . SpTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $text);
        
        // ////////////////////////////////////////////////////
        //                Services details
        // ////////////////////////////////////////////////////
        $objWorkSheet = $objPHPExcel->createSheet();
        $objWorkSheet->setTitle(SpTranslator::Sevices_details($lang));
        $objPHPExcel->setActiveSheetIndex(4);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);


        $curentLine = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $curentLine, CoreTranslator::User($lang));
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $curentLine, SpTranslator::No_Projet($lang));
        //$objPHPExcel->getActiveSheet()->SetCellValue('E' . $curentLine, SpTranslator::Closed_date($lang));
        
        $objPHPExcel->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
        $objPHPExcel->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
        //$objPHPExcel->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

        $itemIdx = 4;
        $items = $projectsBalance["items"];
        $modelItem = new SpItem();
        
        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($item);
            $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
            
        }
        $itemIdx++;
        
        $lastItemIdx = $itemIdx-1;
        $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+1) . $curentLine, SpTranslator::Opened_date($lang));
        $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+1) . $curentLine)->applyFromArray($styleBorderedCell);
        
        $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+2) . $curentLine, SpTranslator::Time_limite($lang));
        $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+2) . $curentLine)->applyFromArray($styleBorderedCell);

        $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+3) . $curentLine, SpTranslator::Closed_date($lang));
        $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+3) . $curentLine)->applyFromArray($styleBorderedCell);
        
        //$objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, SpTranslator::TotalPrice($lang));

        $projects = $projectsBalance["projects"];
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            //$objPHPExcel->getActiveSheet()->SetCellValue('E' . $curentLine, CoreTranslator::dateFromEn($proj["date_close"], $lang));
            
            $objPHPExcel->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);
            //$objPHPExcel->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCell);

            $dateClosed = "";
            if ($proj["date_close"] != "0000-00-00"){
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+1) . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+2) . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
            $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+3) . $curentLine, $dateClosed);
            $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+1) . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+2) . $curentLine)->applyFromArray($styleBorderedCell);
            $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+3) . $curentLine)->applyFromArray($styleBorderedCell);
            
            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            $offset = 4;
            $projItemCount = 0;
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    //print_r($entry);
                    $objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($pos + $offset) . $curentLine, $entry["sum"]);
                    $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($pos + $offset) . $curentLine)->applyFromArray($styleBorderedCell);
                    $projItemCount += $entry["sum"];
                    //$itemsTotal[$idx] += floatval($entry["sum"]);
                }
                
            }
            //$objPHPExcel->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $proj["total"]);
        
            //if($projItemCount == 0){
            //    $objPHPExcel->getActiveSheet()->removeRow($curentLine);
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
            $objPHPExcel->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(".$colLetter."3:".$colLetter.$lastLine.")");
        }
        
        for($r=1 ; $r <= $curentLine ; $r++){
            for($c=1 ; $c <= $itemIdx ; $c++){
                $objPHPExcel->getActiveSheet()->getStyle($this->get_col_letter($c).$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== $this->get_col_letter($itemIdx+1); $col++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
        
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(1, 1);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $text = SpTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . SpTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $text);


        // write excel file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

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
