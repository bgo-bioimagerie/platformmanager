<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/booking/Model/BookingInstall.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
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
        //$formMenusactivation = $this->menusactivationForm($id_space, $lang);
        $formMenusactivation = $this->menusactivationForm($id_space, 'booking', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'booking', 'calendar');
            return $this->redirect("bookingconfig/".$id_space);
        }
        $formSettingsMenusactivation = $this->menusactivationForm($id_space, 'bookingsettings', $lang);
        if ($formSettingsMenusactivation->check()) {
            $this->menusactivation($id_space, 'bookingsettings', 'calendar', 'booking');
                        
            if ( $this->request->getParameter("bookingsettingsMenustatus") > 0 ){
                $modelAccess = new CoreSpaceAccessOptions();
                $modelAccess->set($id_space, "bookingauthorisations", "booking", "bookingauthorisations");
            }
            
            return $this->redirect("bookingconfig/".$id_space);
        }
        
        /*
        $formAuth = $this->bookingAuthorisationUseVisa($id_space, $lang);
        if($formAuth->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkAuthorisationUseVisa", $this->request->getParameter("BkAuthorisationUseVisa"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        */
        
        $formMenuName = $this->menuNameForm($id_space, 'booking', $lang);
        if($formMenuName->check()){
            $this->setMenuName($id_space, 'booking');
            return $this->redirect("bookingconfig/".$id_space);
        }
        $formSettingsMenuName = $this->menuNameForm($id_space, 'bookingsettings', $lang);
        if($formSettingsMenuName->check()){
            $this->setMenuName($id_space, 'bookingsettings');
            return $this->redirect("bookingconfig/".$id_space);
        }
        
        $formeditReservation = $this->editReservationPlugin($id_space, $lang);
        if ($formeditReservation->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bkReservationPlugin", $this->request->getParameter("bkReservationPlugin"), $id_space);
        
            return $this->redirect("bookingconfig/".$id_space);
        }
        
        
        $formbookingUseRecurentBooking = $this->bookingUseRecurentBooking($id_space, $lang);
        if ($formbookingUseRecurentBooking->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkUseRecurentBooking", $this->request->getParameter("BkUseRecurentBooking"), $id_space);
        
            return $this->redirect("bookingconfig/".$id_space);
        }
        
        /*
        $formBookingCanUserEditStartedResa = $this->bookingCanUserEditStartedResa($id_space, $lang);
        if ($formBookingCanUserEditStartedResa->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkCanUserEditStartedResa", $this->request->getParameter("BkCanUserEditStartedResa"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        */
        
        
        $formBookingOption = $this->bookingOptionForm($id_space, $lang);
        if ($formBookingOption->check()){
            $editBookingDescriptionSettings = $this->request->getParameterNoException ( "BkDescriptionFields" );
            $modelCoreConfig = new CoreConfig();
            $modelCoreConfig->setParam("BkDescriptionFields", $editBookingDescriptionSettings, $id_space);
            return $this->redirect("bookingconfig/".$id_space);
        }
       
        $formEditBookingMailing = $this->editBookingMailingForm($id_space, $lang);
        if($formEditBookingMailing->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkEditBookingMailing", $this->request->getParameter("BkEditBookingMailing"), $id_space);
            $modelConfig->setParam("BkBookingMailingAdmins", $this->request->getParameter("BkBookingMailingAdmins"), $id_space);
            $modelConfig->setParam("BkBookingMailingDelete", $this->request->getParameter("BkBookingMailingDelete"), $id_space);
            
            return $this->redirect("bookingconfig/".$id_space);
        }
        
        $setbookingoptionsquery = $this->request->getParameterNoException("setbookingoptionsquery");
        if ($setbookingoptionsquery == "yes") {
            $this->optionsQuery($id_space);
            
            return $this->redirect("bookingconfig/".$id_space);
        }
        else{
            $modelBookingSettings = new BkBookingSettings();
            $bookingSettings = $modelBookingSettings->entries($id_space, "display_order");
        }

        // view
        $forms = array(
            $formMenusactivation->getHtml($lang),
            //$formAuth->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formSettingsMenusactivation->getHtml($lang), 
            $formSettingsMenuName->getHtml($lang),
            $formbookingUseRecurentBooking->getHtml($lang),
            //$formBookingCanUserEditStartedResa->getHtml($lang),
            //$bookingRestrictionForm->getHtml($lang),
            $formBookingOption->getHtml($lang), 
            $formeditReservation->getHtml($lang), 
            $formEditBookingMailing->getHtml($lang));
        $this->render(array("id_space" => $id_space, "forms" => $forms, "bookingSettings" => $bookingSettings, "lang" => $lang));
    }
 
    /**
     * @deprecated  unused
     */
    protected function bookingAuthorisationUseVisa($id_space, $lang){
        $modelCoreConfig = new CoreConfig();
        $BkAuthorisationUseVisa = $modelCoreConfig->getParamSpace("BkAuthorisationUseVisa", $id_space);
        $form = new Form($this->request, "BkAuthorisationUseVisaForm");
        $form->addSeparator(BookingTranslator::Use_Auth_Visa($lang));
        
        $form->addSelect("BkAuthorisationUseVisa", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkAuthorisationUseVisa);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    /**
     * @deprecated  unused
     */
    protected function bookingCanUserEditStartedResa($id_space, $lang){
        $modelCoreConfig = new CoreConfig();
        $BkCanUserEditStartedResa = $modelCoreConfig->getParamSpace("BkCanUserEditStartedResa", $id_space);
        
        $form = new Form($this->request, "bookingCanUserEditStartedResaForm");
        $form->addSeparator(BookingTranslator::CanUserEditStartedResa($lang));
        
        $form->addSelect("BkCanUserEditStartedResa", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkCanUserEditStartedResa);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
    
    protected function bookingUseRecurentBooking($id_space, $lang){
        $modelCoreConfig = new CoreConfig();
        $BkUseRecurentBooking = $modelCoreConfig->getParamSpace("BkUseRecurentBooking", $id_space);
        
        $form = new Form($this->request, "BkUseRecurentBookingForm");
        $form->addSeparator(BookingTranslator::Use_recurent_booking($lang));
        
        $form->addSelect("BkUseRecurentBooking", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkUseRecurentBooking);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function bookingOptionForm($id_space, $lang) {

        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParamSpace("BkDescriptionFields", $id_space);
            
        $form = new Form($this->request, "bookingOptionForm");
        $form->addSeparator(BookingTranslator::Edit_booking_options($lang));
        
        $choices = array(BookingTranslator::Both_short_and_full_description($lang),
        BookingTranslator::Only_short_description($lang),
	    BookingTranslator::Only_full_description($lang));
        $form->addSelect("BkDescriptionFields", BookingTranslator::Description_fields($lang), $choices, array(1,2,3), $BkDescriptionFields);
        
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
    
    
    protected function editBookingMailingForm($id_space, $lang){
        
        $modelCoreConfig = new CoreConfig();
	    $BkEditBookingMailing = $modelCoreConfig->getParamSpace("BkEditBookingMailing", $id_space);
        $BkBookingMailingAdmins = $modelCoreConfig->getParamSpace("BkBookingMailingAdmins", $id_space);
        $BkBookingMailingDelete = $modelCoreConfig->getParamSpace("BkBookingMailingDelete", $id_space);
        if ($BkBookingMailingDelete == ""){
            $BkBookingMailingDelete = 0;
        }
        
        $form = new Form($this->request, "editBookingMailingForm");
        $form->addSeparator(BookingTranslator::EditBookingMailing($lang));
        
        $form->addSelect('BkEditBookingMailing', BookingTranslator::Send_emails($lang), array(BookingTranslator::Never($lang), BookingTranslator::When_manager_admin_edit_a_reservation($lang)), array(1,2), $BkEditBookingMailing);
        $form->addSelect('BkBookingMailingAdmins', BookingTranslator::EmailManagers($lang), array(BookingTranslator::Never($lang), BookingTranslator::WhenAUserBook($lang)), array(1,2), $BkBookingMailingAdmins);
        $form->addSelect('BkBookingMailingDelete', BookingTranslator::EmailWhenResaDelete($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkBookingMailingDelete);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    


    protected function optionsQuery($id_space) {
        // set booking settings

        $modelBookingSetting = new BkBookingSettings();

        $tag_visible_rname = $this->request->getParameterNoException("tag_visible_rname");
        $tag_title_visible_rname = $this->request->getParameterNoException("tag_title_visible_rname");
        $tag_position_rname = $this->request->getParameterNoException("tag_position_rname");
        $tag_font_rname = $this->request->getParameterNoException("tag_font_rname");
        $modelBookingSetting->setEntry("User", $tag_visible_rname, $tag_title_visible_rname, $tag_position_rname, $tag_font_rname, $id_space);

        $tag_visible_rphone = $this->request->getParameterNoException("tag_visible_rphone");
        $tag_title_visible_rphone = $this->request->getParameterNoException("tag_title_visible_rphone");
        $tag_position_rphone = $this->request->getParameterNoException("tag_position_rphone");
        $tag_font_rphone = $this->request->getParameterNoException("tag_font_rphone");
        $modelBookingSetting->setEntry("Phone", $tag_visible_rphone, $tag_title_visible_rphone, $tag_position_rphone, $tag_font_rphone, $id_space);

        $tag_visible_sdesc = $this->request->getParameterNoException("tag_visible_sdesc");
        $tag_title_visible_sdesc = $this->request->getParameterNoException("tag_title_visible_sdesc");
        $tag_position_sdesc = $this->request->getParameterNoException("tag_position_sdesc");
        $tag_font_sdesc = $this->request->getParameterNoException("tag_font_sdesc");
        $modelBookingSetting->setEntry("Short desc", $tag_visible_sdesc, $tag_title_visible_sdesc, $tag_position_sdesc, $tag_font_sdesc, $id_space);

        $tag_visible_desc = $this->request->getParameterNoException("tag_visible_desc");
        $tag_title_visible_desc = $this->request->getParameterNoException("tag_title_visible_desc");
        $tag_position_desc = $this->request->getParameterNoException("tag_position_desc");
        $tag_font_desc = $this->request->getParameterNoException("tag_font_desc");
        $modelBookingSetting->setEntry("Desc", $tag_visible_desc, $tag_title_visible_desc, $tag_position_desc, $tag_font_desc, $id_space);

        //$bookingOptionMessage = "Changes have been saved";
        $modelBookingSettings = new BkBookingSettings();
        $bookingSettings = $modelBookingSettings->entries($id_space, "display_order");
        return $bookingSettings;
    }

}
