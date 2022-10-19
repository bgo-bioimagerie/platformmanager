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

require_once 'Modules/core/Model/CoreTranslator.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Model/InInvoice.php';

require_once 'Modules/services/Controller/ServicesController.php';


class ServicesstatisticsorderController extends ServicesController
{
    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->serviceModel = new SeService();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("statistics", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // build the form
        $form = new Form($this->request, "formbalancesheet");
        $form->setTitle(ServicesTranslator::OrderBalance($lang), 3);
        $form->addDate("begining_period", ServicesTranslator::Beginning_period($lang), true, "");
        $form->addDate("end_period", ServicesTranslator::End_period($lang), true, "");


        $form->setValidationButton("Ok", "servicesstatisticsorder/".$idSpace);

        $stats = "";
        if ($form->check()) {
            $c = new CoreFiles();
            $cs = new CoreSpace();
            $role = $cs->getSpaceMenusRole($idSpace, 'statistics');
            $dateBegin = $form->getParameter("begining_period");
            $dateEnd = $form->getParameter("end_period");
            $name = 'stats_'.SeStats::STATS_ORDERS.'_'.str_replace('/', '-', $dateBegin).'_'.str_replace('/', '-', $dateEnd).'.xlsx';

            $fid = $c->set(0, $idSpace, $name, $role, 'statistics', $_SESSION['id_user']);
            $c->status($idSpace, $fid, CoreFiles::$PENDING, '');

            Events::send([
                "action" => Events::ACTION_STATISTICS_REQUEST,
                "stat" => SeStats::STATS_ORDERS,
                "dateBegin" => $dateBegin,
                "dateEnd" => $dateEnd,
                "user" => ["id" => $_SESSION['id_user']],
                "lang" => $lang,
                "file" => ["id" => $fid],
                "space" => ["id" => $idSpace]
            ]);

            return $this->redirect('statistics/'.$idSpace, [], ['stats' => ['id' => $fid]]);
        }

        // set the view
        $formHtml = $form->getHtml($lang);
        // view
        $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            'formHtml' => $formHtml,
            'stats' => $stats
        ));
    }
}
