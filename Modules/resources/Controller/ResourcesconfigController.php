<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesInstall.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ResourcesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        if (!$this->isUserAuthorized(CoreStatus::$SUPERADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        // install form
        $formInstall = $this->installForm($lang);
        if ($formInstall->check()) {
            $message = "<b>Success:</b> the database have been successfully installed";
            try {
                $installModel = new ResourcesInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            $this->redirect("resourcesconfig");
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang);
        if ($formMenusactivation->check()) {

            $modelMenu = new CoreMenu();
            $modelMenu->setDataMenu("resources", "resources", $this->request->getParameter("resourcesmenustatus"), "glyphicon glyphicon-compressed");

            $this->redirect("resourcesconfig");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang), $formMenusactivation->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(ResourcesTranslator::Install_Repair_database($lang));
        $form->addComment(ResourcesTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusactivationForm($lang) {

        $modelMenu = new CoreMenu();
        $statusSiteMenu = $modelMenu->getDataMenusUserType("resources");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $modelStatus = new CoreStatus();
        $status = $modelStatus->allStatusInfo();

        $choices = array();
        $choicesid = array();
        $choices[] = CoreTranslator::disable($lang);
        $choicesid[] = 0;
        for ($i = 0; $i < count($status); $i++) {
            $choices[] = CoreTranslator::Translate_status($lang, $status[$i]["name"]);
            $choicesid[] = $status[$i]["id"];
        }

        $form->addSelect("resourcesmenustatus", ResourcesTranslator::Resources($lang), $choices, $choicesid, $statusSiteMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
