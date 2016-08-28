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
require_once 'Modules/core/Model/CoreProject.php';

require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingstatisticauthorizationsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("booking");
    }

    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = new Form($this->request, "bookingstatisticauthorizations");
        $form->setTitle(BookingTranslator::Authorisations_statistics($lang));
        $form->addDate("period_begin", BookingTranslator::PeriodBegining($lang));
        $form->addDate("period_end", BookingTranslator::PeriodEnd($lang));
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

        foreach ($resources as $resource) {
            foreach ($instructors as $instructor) {
                $authorizations = $modelAuthorizations->getForResourceInstructorPeriod($resource["id"], $instructor["id_instructor"], $period_begin, $period_end);
                $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]] = count($authorizations);
            }
        }

        $modelUnits = new EcUnit();
        $units = $modelUnits->getUnits("name");
        $countResourcesUnit = array();
        foreach ($resources as $resource) {
            foreach ($units as $unit) {
                $authorizations = $modelAuthorizations->getFormResourceUnitPeriod($resource["id"], $unit["id"], $period_begin, $period_end);
                $countResourcesUnit[$resource["id"]][$unit["id"]] = count($authorizations);
            }
        }

        $this->generateXls($resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $period_begin, $period_end);
    }

    protected function generateXls($resources, $instructors, $units, $countResourcesInstructor, $countResourcesUnit, $period_begin, $period_end) {

        include_once ("externals/PHPExcel/Classes/PHPExcel.php");
        include_once ("externals/PHPExcel/Classes/PHPExcel/Writer/Excel5.php");
        include_once ("externals/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php");
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Platform-Manager");
        $objPHPExcel->getProperties()->setLastModifiedBy("Platform-Manager");
        $objPHPExcel->getProperties()->setTitle("Authorizations statistics");
        $objPHPExcel->getProperties()->setSubject("Authorizations statistics");
        $objPHPExcel->getProperties()->setDescription("");

        $stylesheet = $this->xlsStyleSheet();

        // print period
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', "Authorizations from " . $period_begin . " to " . $period_end);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($stylesheet["borderedCell"]);

        // print by instructors
        $curentLine = 3;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = $this->get_col_letter($num);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = $this->get_col_letter($num);
        $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);


        $modelUser = new EcUser();
        foreach ($instructors as $instructor) {
            $curentLine++;
            $letter = $this->get_col_letter(1);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $modelUser->getUserFUllName($instructor["id_instructor"]));
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = $this->get_col_letter($num);
                $val = $countResourcesInstructor[$resource["id"]][$instructor["id_instructor"]];
                $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += $val;
            }
            $num++;
            $letter = $this->get_col_letter($num);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }

        // by unit
        $curentLine+=2;
        $num = 1;
        foreach ($resources as $resource) {
            $num++;
            $letter = $this->get_col_letter($num);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $resource["name"]);
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }
        $num++;
        $letter = $this->get_col_letter($num);
        $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, "Total");
        $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);


        foreach ($units as $unit) {
            $curentLine++;
            $letter = $this->get_col_letter(1);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $unit["name"]);
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);

            $total = 0;
            $num = 1;
            foreach ($resources as $resource) {
                $num++;
                $letter = $this->get_col_letter($num);
                $val = $countResourcesUnit[$resource["id"]][$unit["id"]];
                $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $val);
                $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
                $total += $val;
            }
            $num++;
            $letter = $this->get_col_letter($num);
            $objPHPExcel->getActiveSheet()->SetCellValue($letter . $curentLine, $total);
            $objPHPExcel->getActiveSheet()->getStyle($letter . $curentLine)->applyFromArray($stylesheet["borderedCell"]);
        }

        // write excel file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

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

        // get the selected resource id
        $resource_id = $this->request->getParameter("resource_id");
        $email = $this->request->getParameterNoException("email");

        $lang = $this->getLanguage();
        // query
        $statUserModel = new BkStatsUser();
        if ($email != "") {
            //header_remove();
            //ob_clean();
            $statUserModel->authorizedUsersMail($resource_id, $id_space);
        } else {
            header_remove();
            //ob_clean();
            $statUserModel->authorizedUsers($resource_id, $id_space, $lang);
        }

        return;
    }

}
