<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/booking/Model/BookingInstall.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingconfigController extends CoresecureController {

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
                $installModel = new BookingInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            $this->redirect("bookingconfig");
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang);
        if ($formMenusactivation->check()) {

            $modelMenu = new CoreMenu();
            $modelMenu->setDataMenu("booking", "booking", $this->request->getParameter("bookingmenustatus"), "glyphicon glyphicon-calendar");
            $modelMenu->setDataMenu("bookingsettings", "bookingsettings", $this->request->getParameter("bookingsettingsmenustatus"), "glyphicon glyphicon-calendar");

            $this->redirect("bookingconfig");
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang), $formMenusactivation->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(BookingTranslator::Install_Repair_database($lang));
        $form->addComment(BookingTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusactivationForm($lang) {

        $modelMenu = new CoreMenu();
        $statusBookingMenu = $modelMenu->getDataMenusUserType("booking");
        $statusSettingsMenu = $modelMenu->getDataMenusUserType("bookingsettings");

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

        $form->addSelect("bookingmenustatus", BookingTranslator::Booking($lang), $choices, $choicesid, $statusBookingMenu);
        $form->addSelect("bookingsettingsmenustatus", BookingTranslator::Booking_settings($lang), $choices, $choicesid, $statusSettingsMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
