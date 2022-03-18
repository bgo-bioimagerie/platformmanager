<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Controller/ServicesController.php';
/**
 * @deprecated
 * @author sprigent
 * Controller for the home page
 */
class ServicesbalanceController extends ServicesController {
    
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
            $this->generateBalance($id_space, $date_start, $date_end);
            return;
        }
        
        $htmlForm = $form->getHtml($lang);
        $this->render(array("htmlForm" => $htmlForm, "id_space" => $id_space, "lang" => $lang));
    }
    
    private function generateBalance($id_space, $periodStart, $periodEnd, $spreadsheet=null) {

        //echo "not yet implemented <br/> " . $periodStart . "<br/>" . $periodEnd . "<br/>";
        // get all the opened projects informations
        $modelProjects = new SeProject();
        $openedProjects = $modelProjects->getProjectsOpenedPeriod($periodStart, $periodEnd, $id_space);
        
        // get all the priced projects details
        $projectsBalance = $modelProjects->getPeriodeServicesBalances($id_space, $periodStart, $periodEnd);
        $projectsBilledBalance = $modelProjects->getPeriodeBilledServicesBalances($id_space, $periodStart, $periodEnd);

        // get the stats
        $modelStats = new SeStats();
        $stats = $modelStats->computeStatsProjects($id_space, $periodStart, $periodEnd);

        $this->makeBalanceXlsFile($id_space, $periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $stats, $spreadsheet);
    }
    
    private function makeBalanceXlsFile($id_space ,$periodStart, $periodEnd, $openedProjects, $projectsBalance, $projectsBilledBalance, $stats, $spreadsheet=null) {

        $modelUser = new CoreUser();

        $lang = $this->getLanguage();
        if($spreadsheet == null) {
        // Create new PHPExcel object
            //$spreadsheet = new PHPExcel();
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Set properties
            $spreadsheet->getProperties()->setCreator("Platform-Manager");
            $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
            $spreadsheet->getProperties()->setTitle("Services balance sheet");
            $spreadsheet->getProperties()->setSubject("Services balance sheet");
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
        
        
        
        $spreadsheet->getActiveSheet()->setTitle(ServicesTranslator::OPENED($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('A2', CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->getStyle('A2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('B2', CoreTranslator::Unit($lang));
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


        $spreadsheet->getActiveSheet()->SetCellValue('I2', ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('I2')->applyFromArray($styleBorderedCell);
        
        $spreadsheet->getActiveSheet()->SetCellValue('J2', ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->getStyle('J2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue('K2', ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle('K2')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->mergeCells('I1:K1');
        $spreadsheet->getActiveSheet()->getStyle('I1')->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

        $curentLine = 2;
        $modelClient = new ClClient();
        foreach ($openedProjects as $proj) {
            // responsable, unité, utilisateur, no dossier, nouvelle equipe (accademique, PME), nouveau proj(ac, pme), delai (def, respecte), date cloture
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));

            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->getStyle('A' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, $unitName);
            $spreadsheet->getActiveSheet()->getStyle('B' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('C' . $curentLine, $modelUser->getUserFUllName($proj["id_user"]));
            $spreadsheet->getActiveSheet()->getStyle('C' . $curentLine)->applyFromArray($styleBorderedCell);

            $spreadsheet->getActiveSheet()->SetCellValue('D' . $curentLine, $proj["name"]);
            $spreadsheet->getActiveSheet()->getStyle('D' . $curentLine)->applyFromArray($styleBorderedCell);

            if ($proj["new_team"] == 1) {
                $spreadsheet->getActiveSheet()->SetCellValue('E' . $curentLine, 1);
            } else if ($proj["new_team"] == 2) {
                $spreadsheet->getActiveSheet()->SetCellValue('F' . $curentLine, 1);
            }
            $spreadsheet->getActiveSheet()->getStyle('E' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $spreadsheet->getActiveSheet()->getStyle('F' . $curentLine)->applyFromArray($styleBorderedCenteredCell);


            if ($proj["new_project"] == 1) {
                $spreadsheet->getActiveSheet()->SetCellValue('G' . $curentLine, 1);
            } else if ($proj["new_project"] == 2) {
                $spreadsheet->getActiveSheet()->SetCellValue('H' . $curentLine, 1);
            }
            $spreadsheet->getActiveSheet()->getStyle('G' . $curentLine)->applyFromArray($styleBorderedCenteredCell);
            $spreadsheet->getActiveSheet()->getStyle('H' . $curentLine)->applyFromArray($styleBorderedCenteredCell);

            $dateClosed = "";
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00"){
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue('I' . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('J' . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue('K' . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle('I' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('J' . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle('K' . $curentLine)->applyFromArray($styleBorderedCell);

        }
        
        for($col = 'A'; $col !== 'L'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
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
        //$modelClient = new ClClient();
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
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
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(".$colLetter."3:".$colLetter.$lastLine.")");
        }
        
        for($r=1 ; $r <= $curentLine ; $r++){
            for($c=1 ; $c <= $itemIdx ; $c++){
                $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($c).$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== $this->get_col_letter($itemIdx+1); $col++) {
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
        $spreadsheet->setActiveSheetIndex(3);

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

        for($r=1 ; $r <= $curentLine ; $r++){
            for($c='A' ; $c !== 'C' ; $c++){
                $spreadsheet->getActiveSheet()->getStyle($c.$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== 'C'; $col++) {
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
        $spreadsheet->setActiveSheetIndex(4);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(40);


        $curentLine = 1;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, CoreTranslator::Responsible($lang));
        $spreadsheet->getActiveSheet()->SetCellValue('B' . $curentLine, CoreTranslator::Unit($lang));
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
        $modelItem = new SeService();
        
        foreach ($items as $item) {
            $itemIdx++;
            $name = $modelItem->getItemName($id_space, $item);
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, $name);
            
        }
        $itemIdx++;
        
        $lastItemIdx = $itemIdx-1;
        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+1) . $curentLine, ServicesTranslator::Opened_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+1) . $curentLine)->applyFromArray($styleBorderedCell);
        
        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+2) . $curentLine, ServicesTranslator::Time_limite($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+2) . $curentLine)->applyFromArray($styleBorderedCell);

        $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+3) . $curentLine, ServicesTranslator::Closed_date($lang));
        $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+3) . $curentLine)->applyFromArray($styleBorderedCell);
        
        //$spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($itemIdx) . $curentLine, ServicesTranslator::TotalPrice($lang));

        $projects = $projectsBalance["projects"];
        foreach ($projects as $proj) {
            $curentLine++;
            $unitName = $modelClient->getInstitution($id_space, $proj["id_resp"]);
            //$unitName = $modelUnit->getUnitName($modelUser->getUserUnit($proj["id_resp"]));
            $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, $modelUser->getUserFUllName($proj["id_resp"]));
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
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00"){
                $dateClosed = CoreTranslator::dateFromEn($proj["date_close"], $lang);
            }
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+1) . $curentLine, CoreTranslator::dateFromEn($proj["date_open"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+2) . $curentLine, CoreTranslator::dateFromEn($proj["time_limit"], $lang));
            $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($lastItemIdx+3) . $curentLine, $dateClosed);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+1) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+2) . $curentLine)->applyFromArray($styleBorderedCell);
            $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($lastItemIdx+3) . $curentLine)->applyFromArray($styleBorderedCell);
            
            // "entries"
            $idx = -1;
            $entries = $proj["entries"];
            $offset = 4;
            $projItemCount = 0;
            foreach ($entries as $entry) {
                $idx++;
                $pos = $this->findItemPos($items, $entry["id"]);
                if ($pos > 0 && $entry["pos"] > 0) {
                    $spreadsheet->getActiveSheet()->SetCellValue($this->get_col_letter($pos + $offset) . $curentLine, $entry["sum"]);
                    $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($pos + $offset) . $curentLine)->applyFromArray($styleBorderedCell);
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
            $colLetter = $this->get_col_letter($itemIdx);
            $spreadsheet->getActiveSheet()->SetCellValue($colLetter . $curentLine, "=SUM(".$colLetter."3:".$colLetter.$lastLine.")");
        }
        
        for($r=1 ; $r <= $curentLine ; $r++){
            for($c=1 ; $c <= $itemIdx ; $c++){
                $spreadsheet->getActiveSheet()->getStyle($this->get_col_letter($c).$r)->applyFromArray($styleBorderedCell);
            }
        }
        for($col = 'A'; $col !== $this->get_col_letter($itemIdx+1); $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }
        
        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
        $text = ServicesTranslator::BalanceSheetFrom($lang) . CoreTranslator::dateFromEn($periodStart, $lang)
                . ServicesTranslator::To($lang) . CoreTranslator::dateFromEn($periodEnd, $lang);
        $spreadsheet->getActiveSheet()->setCellValue('A1', $text);


        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');


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
