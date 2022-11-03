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
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'statistics', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'statistics', 'bar-chart');
            return $this->redirect("statisticsconfig/" . $id_space);
        }

        // period projects
        $modelCoreConfig = new CoreConfig();
        $formPerodProject = $this->periodProjectForm($modelCoreConfig, $id_space, $lang);
        if ($formPerodProject->check()) {
            $modelCoreConfig->setParam("statisticsperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("statisticsperiodbegin"), $lang), $id_space);
            $modelCoreConfig->setParam("statisticsperiodend", CoreTranslator::dateToEn($this->request->getParameter("statisticsperiodend"), $lang), $id_space);

            $this->redirect("statisticsconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formPerodProject->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }


    public function periodProjectForm($modelCoreConfig, $id_space, $lang)
    {
        $projectperiodbegin = $modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("statisticsperiodend", $id_space);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(StatisticsTranslator::Statisticsperiod($lang));
        $form->addDate("statisticsperiodbegin", StatisticsTranslator::statisticsperiodbegin($lang), true, $projectperiodbegin);
        $form->addDate("statisticsperiodend", StatisticsTranslator::statisticsperiodend($lang), true, $projectperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "statisticsconfig/" . $id_space);


        return $form;
    }
}
