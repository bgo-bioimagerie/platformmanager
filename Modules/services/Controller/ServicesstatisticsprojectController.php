<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Events.php';

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
            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $dateBegin = $form->getParameter("begining_period");
            $dateEnd = $form->getParameter("end_period");
            $name = 'stats_'.SeStats::STATS_PROJECTS.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.xlsx';
            
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');

            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => SeStats::STATS_PROJECTS,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "user" => ["id" => $_SESSION['id_user']],
                "lang" => $lang,
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);

            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
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

    
    public function samplesreturnAction($id_space) {
        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $c = new CoreFiles();
        $cs = new CoreSpace();
        $role = $cs->getSpaceMenusRole($id_space, 'statistics');
        $date = date('Y-m-d');
        $name = 'stats_'.SeStats::STATS_PROJECT_SAMPLES.'_'.str_replace('/', '-', $date).'.xlsx';
        
        $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
        $c->status($id_space, $fid, CoreFiles::$PENDING, '');

        Events::send([
            "action" => Events::ACTION_STATISTICS_REQUEST,
            "stat" => SeStats::STATS_PROJECT_SAMPLES,
            "user" => ["id" => $_SESSION['id_user']],
            "lang" => $lang,
            "file" => ["id" => $fid],
            "space" => ["id" => $id_space]
        ]);

        return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);

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

            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($id_space, 'statistics');
            $dateBegin = $this->request->getParameter('begining_period');
            $dateEnd = $this->request->getParameter('end_period');
            $name = 'stats_'.SeStats::STATS_MAIL_RESPS.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.csv';
            
            $fid = $c->set(0, $id_space, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($id_space, $fid, CoreFiles::$PENDING, '');

            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => SeStats::STATS_MAIL_RESPS,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "user" => ["id" => $_SESSION['id_user']],
                "lang" => $lang,
                "file" => ["id" => $fid],
                "space" => ["id" => $id_space]
            ]);

            return $this->redirect('statistics/'.$id_space, [], ['stats' => ['id' => $fid]]);
        }

        $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

}
