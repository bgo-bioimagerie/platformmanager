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
class StatisticsconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    public function mainMenu() {
        $id_space = isset($this->args['id_space']) ? $this->args['id_space'] : null;
        if ($id_space) {
            $csc = new CoreSpaceController($this->request);
            return $csc->navbar($id_space);
        }
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu(
                $id_space,
                "statistics",
                "statistics",
                "glyphicon-signal",
                $this->request->getParameter("statisticsmenustatus"),
                $this->request->getParameter("displayMenu"),
                1,
                $this->request->getParameter("displayColor"),
                $this->request->getParameter("displayColorTxt")
            );

            $this->redirect("statisticsconfig/" . $id_space);
            return;
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

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "statistics");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "statistics");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "statistics");
        $displayColorTxt = $modelSpace->getSpaceMenusTxtColor($id_space, "statistics");


        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("statisticsmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor('displayColor', CoreTranslator::color($lang), false, $displayColor);
        $form->addColor('displayColorTxt', CoreTranslator::text_color($lang), false, $displayColorTxt);

        $form->setValidationButton(CoreTranslator::Save($lang), "statisticsconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function periodProjectForm($modelCoreConfig, $id_space, $lang) {
        $projectperiodbegin = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("statisticsperiodbegin", $id_space), $lang);
        $projectperiodend = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("statisticsperiodend", $id_space), $lang);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(StatisticsTranslator::Statisticsperiod($lang));
        $form->addDate("statisticsperiodbegin", StatisticsTranslator::statisticsperiodbegin($lang), true, $projectperiodbegin);
        $form->addDate("statisticsperiodend", StatisticsTranslator::statisticsperiodend($lang), true, $projectperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "statisticsconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
