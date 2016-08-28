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

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        
        // menu activation form
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace = new CoreSpace();
            $modelSpace->setSpaceMenu($id_space, "booking", "booking", "glyphicon glyphicon-calendar", $this->request->getParameter("bookingmenustatus"));
            $modelSpace->setSpaceMenu($id_space, "booking", "bookingsettings", "glyphicon glyphicon-calendar", $this->request->getParameter("bookingsettingsmenustatus"));
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formeditReservation = $this->editReservationPlugin($id_space, $lang);
        if ($formeditReservation->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bkReservationPlugin", $this->request->getParameter("bkReservationPlugin"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formBookingOption = $this->bookingOptionForm($id_space, $lang);
        if ($formBookingOption->check()){
            $editBookingDescriptionSettings = $this->request->getParameterNoException ( "BkDescriptionFields" );
            $modelCoreConfig = new CoreConfig();
            $modelCoreConfig->setParam("BkDescriptionFields", $editBookingDescriptionSettings, $id_space);
        }
        
        $formSettingsMenu = $this->settingsMenuColors($id_space, $lang);
        if ($formSettingsMenu->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bookingsettingsmenucolor", $this->request->getParameter("bookingsettingsmenucolor"), $id_space);
            $modelConfig->setParam("bookingsettingsmenucolortxt", $this->request->getParameter("bookingsettingsmenucolortxt"), $id_space);
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formEditBookingMailing = $this->editBookingMailingForm($id_space, $lang);
        if($formEditBookingMailing->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkEditBookingMailing", $this->request->getParameter("BkEditBookingMailing"), $id_space);
            $modelConfig->setParam("BkBookingMailingAdmins", $this->request->getParameter("BkBookingMailingAdmins"), $id_space);
            
            $this->redirect("bookingconfig/".$id_space);
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
        $forms = array($formMenusactivation->getHtml($lang), 
            $formSettingsMenu->getHtml($lang), $formBookingOption->getHtml($lang),
            $formeditReservation->getHtml($lang), $formEditBookingMailing->getHtml($lang));
        $this->render(array("id_space" => $id_space, "forms" => $forms, "bookingSettings" => $bookingSettings, "lang" => $lang));
    }
 
    protected function bookingOptionForm($id_space, $lang) {

        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParamSpace("BkDescriptionFields", $id_space);
            
        $form = new Form($this->request, "bookingOptionForm");
        $form->addSeparator(BookingTranslator::Edit_booking_options($lang));
        //$form->addComment(BookingTranslator::Edit_booking_options($lang));
        
        $choices = array(BookingTranslator::Both_short_and_full_description($lang),
        BookingTranslator::Only_short_description($lang),
	BookingTranslator::Only_full_description($lang));
        $form->addSelect("BkDescriptionFields", BookingTranslator::Description_fields($lang), $choices, array(1,2,3), $BkDescriptionFields);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function settingsMenuColors($id_space, $lang){
        
        $modelConfig = new CoreConfig();
        $menucolor = $modelConfig->getParamSpace("bookingsettingsmenucolor",$id_space);
        $menucolortxt = $modelConfig->getParamSpace("bookingsettingsmenucolortxt",$id_space);
        
        $form = new Form($this->request, "settingsMenuColorsForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $form->addColor("bookingsettingsmenucolor", BookingTranslator::Color($lang), false, $menucolor);
        $form->addColor("bookingsettingsmenucolortxt", BookingTranslator::Text($lang), false, $menucolortxt);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }

    protected function editReservationPlugin($id_space, $lang){
        $modelConfig = new CoreConfig();
        $bkReservationPlugin = $modelConfig->getParamSpace("bkReservationPlugin", $id_space);
        
        $form = new Form($this->request, "editReservationPluginForm");
        $form->addSeparator(BookingTranslator::EditReservationPlugin($lang));
        $form->addText("bkReservationPlugin", BookingTranslator::Url($lang), false, $bkReservationPlugin);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    } 
    
    protected function menusactivationForm($id_space, $lang) {

        $modelMenu = new CoreSpace();
        $statusBookingMenu = $modelMenu->getSpaceMenusRole($id_space, "booking");
        $statusSettingsMenu = $modelMenu->getSpaceMenusRole($id_space, "bookingsettings");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $modelStatus = new CoreSpace();
        $status = $modelStatus->roles($lang);
        
        $status["names"][] = CoreTranslator::Unactive($lang);
        $status["ids"][] = 0;

        $form->addSelect("bookingmenustatus", BookingTranslator::Booking($lang), $status["names"], $status["ids"], $statusBookingMenu);
        $form->addSelect("bookingsettingsmenustatus", BookingTranslator::Booking_settings($lang), $status["names"], $status["ids"], $statusSettingsMenu);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function editBookingMailingForm($id_space, $lang){
        
        $modelCoreConfig = new CoreConfig();
	$BkEditBookingMailing = $modelCoreConfig->getParam("BkEditBookingMailing", $id_space);
        $BkBookingMailingAdmins = $modelCoreConfig->getParam("BkBookingMailingAdmins", $id_space);
                
        $form = new Form($this->request, "editBookingMailingForm");
        $form->addSeparator(BookingTranslator::EditBookingMailing($lang));
        
        $form->addSelect('BkEditBookingMailing', BookingTranslator::Send_emails($lang), array(BookingTranslator::Never($lang), BookingTranslator::When_manager_admin_edit_a_reservation($lang)), array(1,2), $BkEditBookingMailing);
        $form->addSelect('BkBookingMailingAdmins', BookingTranslator::EmailManagers($lang), array(BookingTranslator::Never($lang), BookingTranslator::WhenAUserBook($lang)), array(1,2), $BkBookingMailingAdmins);
  
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
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
