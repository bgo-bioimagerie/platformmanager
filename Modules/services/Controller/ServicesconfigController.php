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
class ServicesconfigController extends CoresecureController
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
        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'services', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'services', 'basket');
            return $this->redirect("servicesconfig/" . $idSpace);
        }

        $formWarning = $this->warningForm($idSpace, $lang);
        if ($formWarning->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("SeProjectDelayWarning", $this->request->getParameter("SeProjectDelayWarning"), $idSpace);

            $this->redirect("servicesconfig/".$idSpace);
            return;
        }

        $formCloseAtInvoice = $this->closeAtInvoiceForm($idSpace, $lang);
        if ($formCloseAtInvoice->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam(
                "seProjectCloseAtInvoice",
                $this->request->getParameter("seProjectCloseAtInvoice"),
                $idSpace
            );

            $this->redirect("servicesconfig/".$idSpace);
            return;
        }


        $formMenuName = $this->menuNameForm($idSpace, 'services', $lang);
        if ($formMenuName->check()) {
            $this->setMenuName($idSpace, 'services');
            return $this->redirect("servicesconfig/" . $idSpace);
        }

        // period projects
        $formPerodProject = $this->periodProjectForm($modelCoreConfig, $idSpace, $lang);
        if ($formPerodProject->check()) {
            $modelCoreConfig->setParam("projectperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("projectperiodbegin"), $lang), $idSpace);
            $modelCoreConfig->setParam("projectperiodend", CoreTranslator::dateToEn($this->request->getParameter("projectperiodend"), $lang), $idSpace);

            $this->redirect("servicesconfig/" . $idSpace);
            return;
        }

        // project command form
        $formProjectCommand = $this->projectCommandForm($modelCoreConfig, $idSpace, $lang);
        if ($formProjectCommand->check()) {
            $modelCoreConfig->setParam("servicesuseproject", $this->request->getParameter("servicesuseproject"), $idSpace);
            $modelCoreConfig->setParam("servicesusecommand", $this->request->getParameter("servicesusecommand"), $idSpace);

            $this->redirect("servicesconfig/" . $idSpace);
            return;
        }

        // use stock
        $formStock = $this->stockForm($modelCoreConfig, $idSpace, $lang);
        if ($formStock->check()) {
            $modelCoreConfig->setParam("servicesusestock", $this->request->getParameter("servicesusestock"), $idSpace);

            $this->redirect("servicesconfig/" . $idSpace);
            return;
        }

        // use tracking sheet
        $formKanban = $this->kanbanForm($modelCoreConfig, $idSpace, $lang);
        if ($formKanban->check()) {
            $modelCoreConfig->setParam("servicesusekanban", $this->request->getParameter("servicesusekanban"), $idSpace);

            $this->redirect("servicesconfig/" . $idSpace);
            return;
        }

        // view
        $forms = array(
            $formMenusactivation->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formWarning->getHtml($lang),
            $formCloseAtInvoice->getHtml($lang),
            $formPerodProject->getHtml($lang), $formProjectCommand->getHtml($lang),
            $formStock->getHtml($lang),
            $formKanban->getHtml($lang)
        );
        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }

    public function warningForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $SeProjectDelayWarning = $modelCoreConfig->getParamSpace("SeProjectDelayWarning", $idSpace);

        $form = new Form($this->request, "SeProjectDelayWarningForm");
        $form->addSeparator(ServicesTranslator::DelayWarningInDays($lang));

        $form->addText("SeProjectDelayWarning", "", false, $SeProjectDelayWarning);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);

        return $form;
    }

    public function closeAtInvoiceForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $seProjectCloseAtInvoice = $modelCoreConfig->getParamSpace("seProjectCloseAtInvoice", $idSpace, 0);
        $form = new Form($this->request, "seProjectCloseAtInvoice");
        $form->addSeparator(ServicesTranslator::ProjectClosure($lang));
        $form->addSelect(
            "seProjectCloseAtInvoice",
            ServicesTranslator::CloseProjectAtInvoice($lang),
            array(CoreTranslator::yes($lang), CoreTranslator::no($lang)),
            array(1, 0),
            $seProjectCloseAtInvoice
        );

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);

        return $form;
    }

    public function periodProjectForm($modelCoreConfig, $idSpace, $lang)
    {
        $projectperiodbegin = $modelCoreConfig->getParamSpace("projectperiodbegin", $idSpace);
        $projectperiodend = $modelCoreConfig->getParamSpace("projectperiodend", $idSpace);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(ServicesTranslator::projectperiod($lang));
        $form->addDate("projectperiodbegin", ServicesTranslator::projectperiodbegin($lang), true, $projectperiodbegin);
        $form->addDate("projectperiodend", ServicesTranslator::projectperiodend($lang), true, $projectperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);


        return $form;
    }

    public function projectCommandForm($modelCoreConfig, $idSpace, $lang)
    {
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $idSpace);
        if ($servicesuseproject === "") {
            $servicesuseproject = 0;
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $idSpace);
        if ($servicesusecommand === "") {
            $servicesusecommand = 0;
        }
        $form = new Form($this->request, "periodCommandForm");
        $form->addSeparator(ServicesTranslator::Project($lang) . " & " . ServicesTranslator::Orders($lang));
        $form->addSelect("servicesuseproject", ServicesTranslator::UseProject($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesuseproject);
        $form->addSelect("servicesusecommand", ServicesTranslator::UseCommand($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesusecommand);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);


        return $form;
    }

    public function stockForm($modelCoreConfig, $idSpace, $lang)
    {
        $servicesusestock = $modelCoreConfig->getParamSpace("servicesusestock", $idSpace);
        if ($servicesusestock === "") {
            $servicesusestock = 0;
        }
        $form = new Form($this->request, "stockForm");
        $form->addSeparator(ServicesTranslator::Stock($lang));
        $form->addSelect("servicesusestock", ServicesTranslator::UseStock($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesusestock);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);


        return $form;
    }

    public function kanbanForm($modelCoreConfig, $idSpace, $lang)
    {
        $servicesusekanban = $modelCoreConfig->getParamSpace("servicesusekanban", $idSpace);
        if ($servicesusekanban === "") {
            $servicesusekanban = 0;
        }
        $form = new Form($this->request, "kanbanForm");
        $form->addSeparator(ServicesTranslator::Kanban($lang));
        $form->addSelect("servicesusekanban", ServicesTranslator::UseKanban($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $servicesusekanban);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/" . $idSpace);
        return $form;
    }
}
