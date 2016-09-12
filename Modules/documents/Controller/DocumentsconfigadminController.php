<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/documents/Model/DocumentsInstall.php';
require_once 'Modules/documents/Model/DocumentsTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DocumentsconfigadminController extends CoresecureController {

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
                $installModel = new DocumentsInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            
            $modelInstMod = new CoreInstalledModules();
            $modelInstMod->setModule("documents");
            
            $this->redirect("documentsconfigadmin");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(DocumentsTranslator::Install_Repair_database($lang));
        $form->addComment(DocumentsTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "documentsconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
