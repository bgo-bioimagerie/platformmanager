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
    public function __construct(Request $request) {
        parent::__construct($request);

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
            $modelSpace->setSpaceMenu($id_space, "booking", "booking", "glyphicon glyphicon-calendar", 
                    $this->request->getParameter("bookingmenustatus"),
                    $this->request->getParameter("displayBookingMenu"),
                    0,
                    $this->request->getParameter("colorBookingMenu")
                    );
            $modelSpace->setSpaceMenu($id_space, "booking", "bookingsettings", "glyphicon glyphicon-calendar", 
                    $this->request->getParameter("bookingsettingsmenustatus"),
                    $this->request->getParameter("displaySettingsMenu"),
                    1,
                    $this->request->getParameter("colorSettingsMenu")
                    );
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formAuth = $this->bookingAuthorisationUseVisa($id_space, $lang);
        if($formAuth->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkAuthorisationUseVisa", $this->request->getParameter("BkAuthorisationUseVisa"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formMenuName = $this->menuNameForm($id_space, $lang);
        if($formMenuName->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bookingmenuname", $this->request->getParameter("bookingmenuname"), $id_space);
        
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
        
        
        $formbookingUseRecurentBooking = $this->bookingUseRecurentBooking($id_space, $lang);
        if ($formbookingUseRecurentBooking->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkUseRecurentBooking", $this->request->getParameter("BkUseRecurentBooking"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $formBookingCanUserEditStartedResa = $this->bookingCanUserEditStartedResa($id_space, $lang);
        if ($formBookingCanUserEditStartedResa->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkCanUserEditStartedResa", $this->request->getParameter("BkCanUserEditStartedResa"), $id_space);
        
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        
        $formBookingOption = $this->bookingOptionForm($id_space, $lang);
        if ($formBookingOption->check()){
            $editBookingDescriptionSettings = $this->request->getParameterNoException ( "BkDescriptionFields" );
            $modelCoreConfig = new CoreConfig();
            $modelCoreConfig->setParam("BkDescriptionFields", $editBookingDescriptionSettings, $id_space);
        }
       
        $formEditBookingMailing = $this->editBookingMailingForm($id_space, $lang);
        if($formEditBookingMailing->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkEditBookingMailing", $this->request->getParameter("BkEditBookingMailing"), $id_space);
            $modelConfig->setParam("BkBookingMailingAdmins", $this->request->getParameter("BkBookingMailingAdmins"), $id_space);
            $modelConfig->setParam("BkBookingMailingDelete", $this->request->getParameter("BkBookingMailingDelete"), $id_space);
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $bookingRestrictionForm = $this->bookingRestrictionForm($id_space, $lang);
        if( $bookingRestrictionForm->check() ){
                    
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("Bkmaxbookingperday", $this->request->getParameter("Bkmaxbookingperday"), $id_space);
            $modelConfig->setParam("BkbookingDelayUserCanEdit", $this->request->getParameter("BkbookingDelayUserCanEdit"), $id_space);
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        
        $setbookingoptionsquery = $this->request->getParameterNoException("setbookingoptionsquery");
        if ($setbookingoptionsquery == "yes") {
            $bookingSettings = $this->optionsQuery($id_space);
            
            $this->redirect("bookingconfig/".$id_space);
            return;
        }
        else{
            $modelBookingSettings = new BkBookingSettings();
            $bookingSettings = $modelBookingSettings->entries($id_space, "display_order");
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), 
            $formAuth->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formbookingUseRecurentBooking->getHtml($lang),
            $formBookingCanUserEditStartedResa->getHtml($lang),
            $bookingRestrictionForm->getHtml($lang),
            $formBookingOption->getHtml($lang), 
            $formeditReservation->getHtml($lang), 
            $formEditBookingMailing->getHtml($lang));
        $this->render(array("id_space" => $id_space, "forms" => $forms, "bookingSettings" => $bookingSettings, "lang" => $lang));
    }
 
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
        //$form->addComment(BookingTranslator::Edit_booking_options($lang));
        
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
    
    protected function menusactivationForm($id_space, $lang) {

        $modelMenu = new CoreSpace();
        $statusBookingMenu = $modelMenu->getSpaceMenusRole($id_space, "booking");
        $displayBookingMenu = $modelMenu->getSpaceMenusDisplay($id_space, "booking");
        $colorBookingMenu = $modelMenu->getSpaceMenusColor($id_space, "booking");

        $statusSettingsMenu = $modelMenu->getSpaceMenusRole($id_space, "bookingsettings");
        $displaySettingsMenu = $modelMenu->getSpaceMenusDisplay($id_space, "bookingsettings");
        $colorSettingsMenu = $modelMenu->getSpaceMenusColor($id_space, "bookingsettings");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $modelStatus = new CoreSpace();
        $status = $modelStatus->roles($lang);
        
        $status["names"][] = CoreTranslator::Unactive($lang);
        $status["ids"][] = 0;

        $form->addSelect("bookingmenustatus", BookingTranslator::Booking($lang), $status["names"], $status["ids"], $statusBookingMenu);
        $form->addNumber("displayBookingMenu", CoreTranslator::Display_order($lang), false, $displayBookingMenu);
        $form->addColor("colorBookingMenu", CoreTranslator::color($lang), false, $colorBookingMenu);
        
        $form->addSelect("bookingsettingsmenustatus", BookingTranslator::Booking_settings($lang), $status["names"], $status["ids"], $statusSettingsMenu);
        $form->addNumber("displaySettingsMenu", CoreTranslator::Display_order($lang), false, $displaySettingsMenu);
        $form->addColor("colorSettingsMenu", CoreTranslator::color($lang), false, $colorSettingsMenu);
        
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
    
    protected function bookingRestrictionForm($id_space, $lang){
        
        $modelCoreConfig = new CoreConfig();
	$maxBookingPerDay = $modelCoreConfig->getParamSpace("Bkmaxbookingperday", $id_space);
        $bookingDelayUserCanEdit = $modelCoreConfig->getParamSpace("BkbookingDelayUserCanEdit", $id_space);
        
        $form = new Form($this->request, "BkbookingRestrictionForm");
        $form->addSeparator(BookingTranslator::BookingRestriction($lang));
        
        $form->addNumber("Bkmaxbookingperday", BookingTranslator::Maxbookingperday($lang), false, $maxBookingPerDay);
        $form->addNumber("BkbookingDelayUserCanEdit", BookingTranslator::BookingDelayUserCanEdit($lang), false, $bookingDelayUserCanEdit);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function menuNameForm($id_space, $lang){
        $modelCoreConfig = new CoreConfig();
	$bookingmenuname = $modelCoreConfig->getParamSpace("bookingmenuname", $id_space);
        
        $form = new Form($this->request, "bookingmenunameForm");
        $form->addSeparator(CoreTranslator::MenuName($lang));
        
        $form->addText("bookingmenuname", CoreTranslator::Name($lang), false, $bookingmenuname);
        
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
