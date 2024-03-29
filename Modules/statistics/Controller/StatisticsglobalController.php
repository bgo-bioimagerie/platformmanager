<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';

require_once 'Modules/services/Controller/ServicesstatisticsprojectController.php';
require_once 'Modules/booking/Controller/BookingstatisticsController.php';

require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/statistics/Controller/StatisticsController.php';


use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class StatisticsglobalController extends StatisticsController
{
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();
        $date_begin = $this->request->getParameterNoException("date_begin");
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
        $date_end = $this->request->getParameterNoException("date_end");
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


        $form = new Form($this->request, "generateGlobalStatForm");
        $form->setTitle(StatisticsTranslator::StatisticsGlobal($lang));
        $form->addDate("date_begin", StatisticsTranslator::Period_begining($lang), true, $date_begin);
        $form->addDate("date_end", StatisticsTranslator::Period_end($lang), true, $date_end, $lang);

        $form->addSelect("generateclientstats", BookingTranslator::GenerateStatsPerClient($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $this->request->getParameterNoException("generateclientstats"));

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


        if ($form->check()) {
            $dateBegin = CoreTranslator::dateToEn($form->getParameter("date_begin"), $lang);
            $dateEnd = CoreTranslator::dateToEn($form->getParameter("date_end"), $lang);
            $generateclientstats = $this->request->getParameter("generateclientstats");

            if ($dateBegin != "" && $dateEnd != "" && $dateBegin > $dateEnd) {
                $_SESSION["flash"] = ServicesTranslator::Dates_are_not_correct($lang);
                return $this->redirect('statisticsglobal/' . $id_space);
            }

            $excludeColorCode = $this->request->getParameter("exclude_color");

            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $name = 'stats_'.GlobalStats::STATS_GLOBAL.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.xlsx';

            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');

            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => GlobalStats::STATS_GLOBAL,
                "excludeColorCode" => $excludeColorCode,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "generateclientstats" => $generateclientstats,
                "user" => ["id" => $_SESSION['id_user']],
                "lang" => $lang,
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);

            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        }

        return $this->render(array("id_space" => $id_space, 'formHtml' => $form->getHtml($lang)));
    }
}
