<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/ecosystem/Model/EcInstall.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class EcosystemconfigController extends CoresecureController {

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
        //$modelCoreConfig = new CoreConfig();

        // install form
        $formInstall = $this->installForm($lang);
        if ($formInstall->check()) {
            $message = "<b>Success:</b> the database have been successfully installed";
            try {
                $installModel = new EcInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            $this->redirect("ecosystemconfig");
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang);
        if ($formMenusactivation->check()) {

            $modelMenu = new CoreMenu();
            $modelMenu->setDataMenu("sites", "ecsites", $this->request->getParameter("sitemenustatus"), "glyphicon-map-marker");
            $modelMenu->setDataMenu("users/institutions", "ecusers", $this->request->getParameter("usermenustatus"), "glyphicon-user");

            $this->redirect("ecosystemconfig");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang), $formMenusactivation->getHtml($lang));
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(EcosystemTranslator::Install_Repair_database($lang));
        $form->addComment(EcosystemTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "ecosystemconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusactivationForm($lang) {

        $modelMenu = new CoreMenu();
        $statusSiteMenu = $modelMenu->getDataMenusUserType("sites");
        $statusUserMenu = $modelMenu->getDataMenusUserType("users/institutions");

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

        $form->addSelect("sitemenustatus", EcosystemTranslator::Sites($lang), $choices, $choicesid, $statusSiteMenu);
        $form->addSelect("usermenustatus", CoreTranslator::Users($lang), $choices, $choicesid, $statusUserMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "ecosystemconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
