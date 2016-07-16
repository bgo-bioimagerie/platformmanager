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
        
        $formeditReservation = $this->editReservationPlugin($lang);
        if ($formeditReservation->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bkReservationPlugin", $this->request->getParameter("bkReservationPlugin"));
        
            $this->redirect("bookingconfig");
            return;
        }
        
        $formBookingOption = $this->bookingOptionForm($lang);
        if ($formBookingOption->check()){
            $editBookingDescriptionSettings = $this->request->getParameterNoException ( "BkDescriptionFields" );
            $modelCoreConfig = new CoreConfig();
            $modelCoreConfig->setParam("BkDescriptionFields", $editBookingDescriptionSettings);
        }
        
        $formSettingsMenu = $this->settingsMenuColors($lang);
        if ($formSettingsMenu->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bookingsettingsmenucolor", $this->request->getParameter("bookingsettingsmenucolor"));
            $modelConfig->setParam("bookingsettingsmenucolortxt", $this->request->getParameter("bookingsettingsmenucolortxt"));
            
            $this->redirect("bookingconfig");
            return;
        }
        
        $formEditBookingMailing = $this->editBookingMailingForm($lang);
        if($formEditBookingMailing->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkEditBookingMailing", $this->request->getParameter("BkEditBookingMailing"));
            $modelConfig->setParam("BkBookingMailingAdmins", $this->request->getParameter("BkBookingMailingAdmins"));
            
            $this->redirect("bookingconfig");
            return;
        }
        
        $setbookingoptionsquery = $this->request->getParameterNoException("setbookingoptionsquery");
        if ($setbookingoptionsquery == "yes") {
            $bookingSettings = $this->optionsQuery();
        }
        else{
            $modelBookingSettings = new BkBookingSettings();
            $bookingSettings = $modelBookingSettings->entries();
        }

        // view
        $forms = array($formInstall->getHtml($lang), $formMenusactivation->getHtml($lang), 
            $formSettingsMenu->getHtml($lang), $formBookingOption->getHtml($lang),
            $formeditReservation->getHtml($lang), $formEditBookingMailing->getHtml($lang));
        $this->render(array("forms" => $forms, "bookingSettings" => $bookingSettings, "lang" => $lang));
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(BookingTranslator::Install_Repair_database($lang));
        $form->addComment(BookingTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function bookingOptionForm($lang) {

        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParam("BkDescriptionFields");
            
        $form = new Form($this->request, "bookingOptionForm");
        $form->addSeparator(BookingTranslator::Edit_booking_options($lang));
        //$form->addComment(BookingTranslator::Edit_booking_options($lang));
        
        $choices = array(BookingTranslator::Both_short_and_full_description($lang),
        BookingTranslator::Only_short_description($lang),
	BookingTranslator::Only_full_description($lang));
        $form->addSelect("BkDescriptionFields", BookingTranslator::Description_fields($lang), $choices, array(1,2,3), $BkDescriptionFields);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function settingsMenuColors($lang){
        
        $modelConfig = new CoreConfig();
        $menucolor = $modelConfig->getParam("bookingsettingsmenucolor");
        $menucolortxt = $modelConfig->getParam("bookingsettingsmenucolortxt");
        
        $form = new Form($this->request, "settingsMenuColorsForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $form->addColor("bookingsettingsmenucolor", BookingTranslator::Color($lang), false, $menucolor);
        $form->addColor("bookingsettingsmenucolortxt", BookingTranslator::Text($lang), false, $menucolortxt);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }

    protected function editReservationPlugin($lang){
        $modelConfig = new CoreConfig();
        $bkReservationPlugin = $modelConfig->getParam("bkReservationPlugin");
        
        $form = new Form($this->request, "editReservationPluginForm");
        $form->addSeparator(BookingTranslator::EditReservationPlugin($lang));
        $form->addText("bkReservationPlugin", BookingTranslator::Url($lang), false, $bkReservationPlugin);
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
    
    protected function editBookingMailingForm($lang){
        
        $modelCoreConfig = new CoreConfig();
	$BkEditBookingMailing = $modelCoreConfig->getParam("BkEditBookingMailing");
        $BkBookingMailingAdmins = $modelCoreConfig->getParam("BkBookingMailingAdmins");
                
        $form = new Form($this->request, "editBookingMailingForm");
        $form->addSeparator(BookingTranslator::EditBookingMailing($lang));
        
        $form->addSelect('BkEditBookingMailing', BookingTranslator::Send_emails($lang), array(BookingTranslator::Never($lang), BookingTranslator::When_manager_admin_edit_a_reservation($lang)), array(1,2), $BkEditBookingMailing);
        $form->addSelect('BkBookingMailingAdmins', BookingTranslator::EmailManagers($lang), array(BookingTranslator::Never($lang), BookingTranslator::WhenAUserBook($lang)), array(1,2), $BkBookingMailingAdmins);
  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function optionsQuery() {
        // set booking settings

        $modelBookingSetting = new BkBookingSettings();

        $tag_visible_rname = $this->request->getParameterNoException("tag_visible_rname");
        $tag_title_visible_rname = $this->request->getParameterNoException("tag_title_visible_rname");
        $tag_position_rname = $this->request->getParameterNoException("tag_position_rname");
        $tag_font_rname = $this->request->getParameterNoException("tag_font_rname");
        $modelBookingSetting->setEntry("User", $tag_visible_rname, $tag_title_visible_rname, $tag_position_rname, $tag_font_rname);


        $tag_visible_rphone = $this->request->getParameterNoException("tag_visible_rphone");
        $tag_title_visible_rphone = $this->request->getParameterNoException("tag_title_visible_rphone");
        $tag_position_rphone = $this->request->getParameterNoException("tag_position_rphone");
        $tag_font_rphone = $this->request->getParameterNoException("tag_font_rphone");
        $modelBookingSetting->setEntry("Phone", $tag_visible_rphone, $tag_title_visible_rphone, $tag_position_rphone, $tag_font_rphone);

        $tag_visible_sdesc = $this->request->getParameterNoException("tag_visible_sdesc");
        $tag_title_visible_sdesc = $this->request->getParameterNoException("tag_title_visible_sdesc");
        $tag_position_sdesc = $this->request->getParameterNoException("tag_position_sdesc");
        $tag_font_sdesc = $this->request->getParameterNoException("tag_font_sdesc");
        $modelBookingSetting->setEntry("Short desc", $tag_visible_sdesc, $tag_title_visible_sdesc, $tag_position_sdesc, $tag_font_sdesc);

        $tag_visible_desc = $this->request->getParameterNoException("tag_visible_desc");
        $tag_title_visible_desc = $this->request->getParameterNoException("tag_title_visible_desc");
        $tag_position_desc = $this->request->getParameterNoException("tag_position_desc");
        $tag_font_desc = $this->request->getParameterNoException("tag_font_desc");
        $modelBookingSetting->setEntry("Desc", $tag_visible_desc, $tag_title_visible_desc, $tag_position_desc, $tag_font_desc);

        //$bookingOptionMessage = "Changes have been saved";
        $modelBookingSettings = new BkBookingSettings();
        $bookingSettings = $modelBookingSettings->entries();
        return $bookingSettings;
    }

}
