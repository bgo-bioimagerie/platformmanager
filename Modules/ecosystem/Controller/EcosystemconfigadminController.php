<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/ecosystem/Model/EcInstall.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class EcosystemconfigadminController extends CoresecureController {

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
            
            $modInstMod = new CoreInstalledModules();
            $modInstMod->setModule("ecosystem");
            
            $this->redirect("ecosystemconfigadmin");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang));
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(EcosystemTranslator::Install_Repair_database($lang));
        $form->addComment(EcosystemTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "ecosystemconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
