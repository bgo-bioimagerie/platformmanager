<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/statistics/Model/StatisticsInstall.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class StatisticsconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }


    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'statistics', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'statistics', 'bar-chart');
            return $this->redirect("statisticsconfig/" . $idSpace);
        }

        // period projects
        $modelCoreConfig = new CoreConfig();
        $formPerodProject = $this->periodProjectForm($modelCoreConfig, $idSpace, $lang);
        if ($formPerodProject->check()) {
            $modelCoreConfig->setParam("statisticsperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("statisticsperiodbegin"), $lang), $idSpace);
            $modelCoreConfig->setParam("statisticsperiodend", CoreTranslator::dateToEn($this->request->getParameter("statisticsperiodend"), $lang), $idSpace);

            $this->redirect("statisticsconfig/" . $idSpace);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formPerodProject->getHtml($lang)
        );
        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }


    public function periodProjectForm($modelCoreConfig, $idSpace, $lang)
    {
        $projectperiodbegin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $idSpace);
        $projectperiodend = $modelCoreConfig->getParamSpace("statisticsperiodend", $idSpace);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(StatisticsTranslator::Statisticsperiod($lang));
        $form->addDate("statisticsperiodbegin", StatisticsTranslator::statisticsperiodbegin($lang), true, $projectperiodbegin);
        $form->addDate("statisticsperiodend", StatisticsTranslator::statisticsperiodend($lang), true, $projectperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "statisticsconfig/" . $idSpace);


        return $form;
    }
}
