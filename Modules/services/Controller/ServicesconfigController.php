<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/services/Model/ServicesInstall.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu(
                $id_space,
                "services",
                "services",
                "glyphicon glyphicon-plus",
                $this->request->getParameter("servicesmenustatus"),
                $this->request->getParameter("displayMenu"),
                1,
                $this->request->getParameter("displayColor"),
                $this->request->getParameter("displayColorTxt")
            );

            $this->redirect("servicesconfig/" . $id_space);

            return;
        }

        $formWarning = $this->warningForm($id_space, $lang);
        if($formWarning->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("SeProjectDelayWarning", $this->request->getParameter("SeProjectDelayWarning"), $id_space);
        
            $this->redirect("servicesconfig/".$id_space);
            return;
        }
        
        
        $formMenuName = $this->menuNameForm($id_space, $lang);
        if ($formMenuName->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("servicesmenuname", $this->request->getParameter("servicesmenuname"), $id_space);

            $this->redirect("servicesconfig/" . $id_space);
            return;
        }

        // period projects
        $formPerodProject = $this->periodProjectForm($modelCoreConfig, $id_space, $lang);
        if ($formPerodProject->check()) {
            $modelCoreConfig->setParam("projectperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("projectperiodbegin"), $lang), $id_space);
            $modelCoreConfig->setParam("projectperiodend", CoreTranslator::dateToEn($this->request->getParameter("projectperiodend"), $lang), $id_space);

            $this->redirect("servicesconfig/" . $id_space);
            return;
        }

        // project command form
        $formProjectCommand = $this->projectCommandForm($modelCoreConfig, $id_space, $lang);
        if ($formProjectCommand->check()) {
            $modelCoreConfig->setParam("servicesuseproject", $this->request->getParameter("servicesuseproject"), $id_space);
            $modelCoreConfig->setParam("servicesusecommand", $this->request->getParameter("servicesusecommand"), $id_space);

            $this->redirect("servicesconfig/" . $id_space);
            return;
        }

        // use stock
        $formStock = $this->stockForm($modelCoreConfig, $id_space, $lang);
        if ($formStock->check()) {
            $modelCoreConfig->setParam("servicesusestock", $this->request->getParameter("servicesusestock"), $id_space);

            $this->redirect("servicesconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formWarning->getHtml($lang),
            $formPerodProject->getHtml($lang), $formProjectCommand->getHtml($lang),
            $formStock->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($id_space, $lang) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "services");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "services");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "services");
        $displayColorTxt = $modelSpace->getSpaceMenusTxtColor($id_space, "services");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("servicesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("displayColor", CoreTranslator::color($lang), false, $displayColor);
        $form->addColor("displayColorTxt", CoreTranslator::text_color($lang), false, $displayColorTxt);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function warningForm($id_space, $lang) {

        $modelCoreConfig = new CoreConfig();
        $SeProjectDelayWarning = $modelCoreConfig->getParamSpace("SeProjectDelayWarning", $id_space);

        $form = new Form($this->request, "SeProjectDelayWarningForm");
        $form->addSeparator(ServicesTranslator::DelayWarningInDays($lang));

        $form->addText("SeProjectDelayWarning", "", false, $SeProjectDelayWarning);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function periodProjectForm($modelCoreConfig, $id_space, $lang) {
        $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $id_space);
        $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $id_space);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(ServicesTranslator::projectperiod($lang));
        $form->addDate("projectperiodbegin", ServicesTranslator::projectperiodbegin($lang), true, $projectperiodbegin);
        $form->addDate("projectperiodend", ServicesTranslator::projectperiodend($lang), true, $projectperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function projectCommandForm($modelCoreConfig, $id_space, $lang) {
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $id_space);
        if($servicesuseproject === "") {
            $servicesuseproject = 0;
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $id_space);
        if($servicesusecommand === "") {
            $servicesusecommand = 0;
        }
        $form = new Form($this->request, "periodCommandForm");
        $form->addSeparator(ServicesTranslator::Project($lang) . " & " . ServicesTranslator::Orders($lang));
        $form->addSelect("servicesuseproject", ServicesTranslator::UseProject($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesuseproject);
        $form->addSelect("servicesusecommand", ServicesTranslator::UseCommand($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesusecommand);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function stockForm($modelCoreConfig, $id_space, $lang) {
        $servicesusestock = $modelCoreConfig->getParamSpace("servicesusestock", $id_space);
        if($servicesusestock === "") {
            $servicesusestock = 0;
        }
        $form = new Form($this->request, "stockForm");
        $form->addSeparator(ServicesTranslator::Stock($lang));
        $form->addSelect("servicesusestock", ServicesTranslator::UseStock($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesusestock);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menuNameForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $bookingmenuname = $modelCoreConfig->getParamSpace("servicesmenuname", $id_space);

        $form = new Form($this->request, "servicesmenunameForm");
        $form->addSeparator(CoreTranslator::MenuName($lang));

        $form->addText("servicesmenuname", CoreTranslator::Name($lang), false, $bookingmenuname);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
