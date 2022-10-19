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
class BookingstatisticauthorizationsController extends StatisticsController
{
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("statistics", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();

        $date_begin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $idSpace);
        $dateArray = explode("-", $date_begin);
        $y = date("Y") - 1;
        $m = $dateArray[1] ?? '01';
        $d = $dateArray[2] ?? '01';
        $date_begin = $y . "-" . $m . "-" . $d;

        $date_end = $modelCoreConfig->getParamSpace("statisticsperiodend", $idSpace);
        $dateArray = explode("-", $date_end);
        $y = date("Y");
        $m = $dateArray[1] ?? '12';
        $d = $dateArray[2] ?? '31';
        $date_end = $y . "-" . $m . "-" . $d;


        $form = new Form($this->request, "bookingstatisticauthorizations");
        $form->setTitle(BookingTranslator::Authorisations_statistics($lang));
        $form->addDate("period_begin", BookingTranslator::PeriodBegining($lang), true, $date_begin);
        $form->addDate("period_end", BookingTranslator::PeriodEnd($lang), true, $date_end);

        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingstatisticauthorizations/" . $idSpace);

        if ($form->check()) {
            $period_begin = $this->request->getParameter("period_begin");
            $period_end = $this->request->getParameter("period_end");

            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($idSpace, 'statistics');
            $name = 'stats_'.BkStats::STATS_AUTH_STAT.'_'.str_replace('/', '-', $period_begin).'_'.str_replace('/', '-', $period_end).'.xlsx';
            $fid = $c->set(0, $idSpace, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($idSpace, $fid, CoreFiles::$PENDING, '');
            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => BkStats::STATS_AUTH_STAT,
                "dateBegin" => $period_begin,
                "dateEnd" => $period_end,
                "lang" => $lang,
                "user" => ["id" => $_SESSION['id_user']],
                "file" => ["id" => $fid],
                "space" => ["id" => $idSpace]
            ]);
            return $this->redirect('statistics/'.$idSpace, [], ['stats' => ['id' => $fid]]);
        }

        $this->render(array("lang" => $lang, "id_space" => $idSpace, "formHtml" => $form->getHtml($lang)));
    }

    /**
     * Form to export the list of authorized user per resource category
     */
    public function authorizedusersAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("statistics", $idSpace, $_SESSION["id_user"]);
        // get the resource list
        $resourceModel = new ReCategory();
        $resourcesCategories = $resourceModel->getBySpace($idSpace);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $idSpace,
            'resourcesCategories' => $resourcesCategories
        ));
    }

    /**
     * Query to export the list of authorized user per resource category
     */
    public function authorizedusersqueryAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("statistics", $idSpace, $_SESSION["id_user"]);
        // get the selected resource id
        $resource_id = $this->request->getParameter("resource_id");
        $email = $this->request->getParameterNoException("email");

        $lang = $this->getLanguage();

        $c = new CoreFiles();
        $cs = new CoreSpace();
        $role = $cs->getSpaceMenusRole($idSpace, 'statistics');
        $name = 'stats_'.BkStats::STATS_AUTH_LIST.'_'.$resource_id.'.xlsx';
        $fid = $c->set(0, $idSpace, $name, $role, 'statistics', $_SESSION['id_user']);
        $c->status($idSpace, $fid, CoreFiles::$PENDING, '');
        Events::send([
            "action" => Events::ACTION_STATISTICS_REQUEST,
            "stat" => BkStats::STATS_AUTH_LIST,
            "resource_id" => $resource_id,
            "email" => $email,
            "user" => ["id" => $_SESSION['id_user']],
            "lang" => $lang,
            "file" => ["id" => $fid],
            "space" => ["id" => $idSpace]
        ]);
        return $this->redirect('statistics/'.$idSpace, [], ['stats' => ['id' => $fid]]);
    }
}
