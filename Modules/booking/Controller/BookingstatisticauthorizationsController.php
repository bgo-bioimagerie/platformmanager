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

require_once 'Modules/core/Model/CoreUser.php';

require_once 'Modules/statistics/Controller/StatisticsController.php';
/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingstatisticauthorizationsController extends StatisticsController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("booking");

    }

    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelConfig = new CoreConfig();
        $modelCoreConfig = new CoreConfig();

        $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
        $dateArray = explode("-", $date_begin);
        $y = date("Y") - 1;
        $m = $dateArray[1] ?? '1';
        $d = $dateArray[2] ?? '1';
        $date_begin = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);

        $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);
        $dateArray = explode("-", $date_end);
        $y = date("Y");
        $m = $dateArray[1] ?? '12';
        $d = $dateArray[2] ?? '31';
        $date_end = CoreTranslator::dateFromEn($y . "-" . $m . "-" . $d, $lang);


        $form = new Form($this->request, "bookingstatisticauthorizations");
        $form->setTitle(BookingTranslator::Authorisations_statistics($lang));
        $form->addDate("period_begin", BookingTranslator::PeriodBegining($lang), true, $date_begin);
        $form->addDate("period_end", BookingTranslator::PeriodEnd($lang), true, $date_end);
        $form->setButtonsWidth(3, 9);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingstatisticauthorizations/" . $id_space);

        if ($form->check()) {
            $period_begin = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $period_end = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);

            $this->generateStats($id_space, $period_begin, $period_end);
            return;
        }

        $this->render(array("lang" => $lang, "id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

    protected function generateStats($id_space, $period_begin, $period_end) {

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
        $summary["distinctunit"] = $modelAuthorizations->getDistinctUnitForPeriod($id_space, $period_begin, $period_end);
        $summary["distinctvisa"] = $modelAuthorizations->getDistinctVisaForPeriod($id_space, $period_begin, $period_end);
        $summary["distinctresource"] = $modelAuthorizations->getDistinctResourceForPeriod($id_space, $period_begin, $period_end);
        $summary["newuser"] = $modelAuthorizations->getNewPeopleForPeriod($id_space, $period_begin, $period_end);

        $this->generateXls($resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end);
    }

    protected function generateXls($resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $summary, $period_begin, $period_end) {

        //echo "generateXls 1 <br/>";
        //include_once ("externals/PHPExcel/Classes/PHPExcel.php");
        //include_once ("externals/PHPExcel/Classes/PHPExcel/Writer/Excel5.php");
        //include_once ("externals/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php");
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        

        //echo "generateXls 2 <br/>";
        // Set properties
        $spreadsheet->getProperties()->setCreator("Platform-Manager");
        $spreadsheet->getProperties()->setLastModifiedBy("Platform-Manager");
        $spreadsheet->getProperties()->setTitle("Authorizations statistics");
        $spreadsheet->getProperties()->setSubject("Authorizations statistics");
        $spreadsheet->getProperties()->setDescription("");

        $stylesheet = $this->xlsStyleSheet();

        // print by instructors
        $spreadsheet->getActiveSheet()->setTitle("Autorisations par formateur");
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Autorisations par formateur du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));


        $curentLine = 3;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = $this->get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = $this->get_col_letter($num);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);


        $modelUser = new CoreUser();
        $instructorsStartLine = $curentLine + 1;
        foreach ($instructors as $instructor) {
            $curentLine++;
            $letter = $this->get_col_letter(1);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $modelUser->getUserFUllName($instructor["id_instructor"]));
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = $this->get_col_letter($num);
                $val = $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]];
                if ($val == 0) {
                    $val = "";
                }
                $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += intval($val);
            }
            $num++;
            $letter = $this->get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, 'Total');
        for ($i = 0; $i < count($resources); $i++) {
            $letter = $this->get_col_letter($i + 2);
            $sumEnd = $curentLine - 1;
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $instructorsStartLine . ':' . $letter . $sumEnd . ')');
        }
        $letter = $this->get_col_letter(count($resources) + 2);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $instructorsStartLine . ':' . $letter . $sumEnd . ')');

        // by unit
        $objWorkSheet = $spreadsheet->createSheet(1);
        $objWorkSheet->setTitle("Authorisations par unité");
        $spreadsheet->setActiveSheetIndex(1);
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Autorisations par unité du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));

        $curentLine = 2;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = $this->get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = $this->get_col_letter($num);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

        $unitsStartLine = $curentLine;
        foreach ($units as $unit) {
            $curentLine++;
            $letter = $this->get_col_letter(1);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $unit["name"]);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = $this->get_col_letter($num);
                $val = $countResourcesUnit[$resource["id"]][$unit["id"]];
                $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += intval($val);
            }
            $num++;
            $letter = $this->get_col_letter($num);
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $spreadsheet->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $curentLine++;
        $spreadsheet->getActiveSheet()->SetCellValue('A' . $curentLine, 'Total');
        for ($i = 0; $i < count($resources); $i++) {
            $letter = $this->get_col_letter($i + 2);
            $sumEnd = $curentLine - 1;
            $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $unitsStartLine . ':' . $letter . $sumEnd . ')');
        }
        $letter = $this->get_col_letter(count($resources) + 2);
        $spreadsheet->getActiveSheet()->SetCellValue($letter . $curentLine, '=SUM(' . $letter . $unitsStartLine . ':' . $letter . $sumEnd . ')');

        // print summary
        $objWorkSheet = $spreadsheet->createSheet(2);
        $objWorkSheet->setTitle("Authorisations résumé");
        $spreadsheet->setActiveSheetIndex(2);

        $spreadsheet->getActiveSheet()->setTitle("Autorisations résumé");
        $spreadsheet->getActiveSheet()->mergeCells('A1:H1');
        $spreadsheet->getActiveSheet()->SetCellValue('A1', "Résumé des autorisations du " . CoreTranslator::dateFromEn($period_begin, "fr") . " au " . CoreTranslator::dateFromEn($period_end, "fr"));

        $spreadsheet->getActiveSheet()->SetCellValue('A3', "Nombre de formations");
        $spreadsheet->getActiveSheet()->SetCellValue('B3', $summary["total"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A4', "Nombre d'utilisateurs");
        $spreadsheet->getActiveSheet()->SetCellValue('B4', $summary["distinctuser"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A5', "Nombre d'unités");
        $spreadsheet->getActiveSheet()->SetCellValue('B5', $summary["distinctunit"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A6', "Nombre de Visas");
        $spreadsheet->getActiveSheet()->SetCellValue('B6', $summary["distinctvisa"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A7', "Nombre de ressources");
        $spreadsheet->getActiveSheet()->SetCellValue('B7', $summary["distinctresource"]);

        $spreadsheet->getActiveSheet()->SetCellValue('A8', "Nombre de nouveaux utilisateurs");
        $spreadsheet->getActiveSheet()->SetCellValue('B8', $summary["newuser"]);

        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        //On enregistre les modifications et on met en téléchargement le fichier Excel obtenu
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="platorm-manager-authorizations-stats.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    protected function xlsStyleSheet() {
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

        return array("borderedCell" => $styleBorderedCell, "borderedCenteredCell" => $styleBorderedCenteredCell);
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

    /**
     * Form to export the list of authorized user per resource category
     */
    public function authorizedusersAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        // get the resource list
        $resourceModel = new ReCategory();
        $resourcesCategories = $resourceModel->getBySpace($id_space);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'resourcesCategories' => $resourcesCategories
        ));
    }

    /**
     * Query to export the list of authorized user per resource category
     */
    public function authorizedusersqueryAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        // get the selected resource id
        $resource_id = $this->request->getParameter("resource_id");
        $email = $this->request->getParameterNoException("email");

        $lang = $this->getLanguage();
        // query
        $statUserModel = new BkStatsUser();
        if ($email != "") {
            $statUserModel->authorizedUsersMail($resource_id, $id_space);
        } else {
            $statUserModel->authorizedUsers($resource_id, $id_space, $lang);
        }
    }

}
