<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';

require_once 'Modules/services/Controller/ServicesstatisticsprojectController.php';
require_once 'Modules/booking/Controller/BookingstatisticsController.php';

require_once 'Modules/booking/Model/BkColorCode.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class StatisticsglobalController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("statistics");
    }

    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);


        $lang = $this->getLanguage();
        $form = new Form($this->request, "generateGlobalStatForm");
        $form->setTitle(StatisticsTranslator::StatisticsGlobal($lang));
        $form->addDate("date_begin", StatisticsTranslator::Period_begining($lang), true, $this->request->getParameterNoException("date_begin"));
        $form->addDate("date_end", StatisticsTranslator::Period_end($lang), true, $this->request->getParameterNoException("date_end"));
        $form->addSelect("generateunitstats", BookingTranslator::GenerateStatsPerUnit($lang), 
                array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), 
                array(1,0), $this->request->getParameterNoException("generateunitstats"));

        $modelColorCode = new BkColorCode();
        $colorCodes = $modelColorCode->getForList($id_space);
        $formAdd = new FormAdd($this->request, 'generateGlobalStatFormAdd');
        $values = $this->request->getParameterNoException("exclude_color");
        if ($values == "") {
            $values = array();
        }
        $formAdd->addSelect("exclude_color", "", $colorCodes["names"], $colorCodes["ids"], $values);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, StatisticsTranslator::Exclude_colorcodes($lang));
        $form->setValidationButton(CoreTranslator::Ok($lang), 'statisticsglobal/' . $id_space);
        $form->setButtonsWidth(2, 8);

        if ($form->check()) {
            $dateBegin = CoreTranslator::dateToEn($form->getParameter("date_begin"), $lang);
            $dateEnd = CoreTranslator::dateToEn($form->getParameter("date_end"), $lang);
            $generateunitstats = $this->request->getParameter("generateunitstats");

            if ($dateBegin != "" && $dateEnd != "" && $dateBegin > $dateEnd) {
                $_SESSION['message'] = ServicesTranslator::Dates_are_not_correct($lang);
                $this->redirect('statisticsglobal/' . $id_space);
                return;
            }

            $excludeColorCode = $this->request->getParameter("exclude_color");
            
            $this->generateStats($dateBegin, $dateEnd, $excludeColorCode, $generateunitstats, $id_space);
        }

        $this->render(array("id_space" => $id_space, 'formHtml' => $form->getHtml($lang)));
    }

    protected function generateStats($dateBegin, $dateEnd, $excludeColorCode, $generateunitstats, $id_space) {

        $controllerServices = new ServicesstatisticsprojectController();
        $objPHPExcel = $controllerServices->getBalance($dateBegin, $dateEnd, $id_space);

        $controllerBooking = new BookingstatisticsController();
        $objPHPExcel = $controllerBooking->getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateunitstats, $objPHPExcel);

        // write excel file
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        //On enregistre les modifications et on met en téléchargement le fichier Excel obtenu
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="platorm-manager-bilan.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

}
