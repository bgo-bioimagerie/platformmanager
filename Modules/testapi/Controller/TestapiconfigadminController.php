<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/testapi/Model/TestapiInstall.php';
require_once 'Modules/testapi/Model/TestapiTranslator.php';

require_once 'Modules/core/Model/CoreInstalledModules.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class TestapiconfigadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
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
                $installModel = new TestapiInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            
            $modelInstMod = new CoreInstalledModules();
            $modelInstMod->setModule("testapi");
            
            $_SESSION["message"] = $message;
            $this->redirect("testapiconfigadmin");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(TestapiTranslator::Install_Repair_database($lang));
        $form->addComment(TestapiTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "testapiconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
