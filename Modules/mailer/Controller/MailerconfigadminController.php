<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class MailerconfigadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
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
            $message = "<b>Success:</b> the module have been successfully installed";
            
            $_SESSION["message"] = $message;
            
            $modInstMod = new CoreInstalledModules();
            $modInstMod->setModule("mailer");
            
            $this->redirect("mailerconfigadmin");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(MailerTranslator::Install_Repair_database($lang));
        $form->addComment(MailerTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
