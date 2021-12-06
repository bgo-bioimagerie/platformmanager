<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/booking/Model/BookingInstall.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingconfigadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
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
                $installModel = new BookingInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            
            $modelInstMod = new CoreInstalledModules();
            $modelInstMod->setModule("booking");
            
            $this->redirect("bookingconfigadmin");
            return;
        }
        
        // view
        $forms = array($formInstall->getHtml($lang));
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(BookingTranslator::Install_Repair_database($lang));
        $form->addComment(BookingTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }
}
