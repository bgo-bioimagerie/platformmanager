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
class BookingconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // menu activation form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'booking', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'booking', 'calendar3');
            return $this->redirect("bookingconfig/".$idSpace);
        }
        $formSettingsMenusactivation = $this->menusactivationForm($idSpace, 'bookingsettings', $lang);
        if ($formSettingsMenusactivation->check()) {
            $this->menusactivation($idSpace, 'bookingsettings', 'calendar3', 'booking');

            $modelAccess = new CoreSpaceAccessOptions();
            $toolname = "bookingauthorisations";
            if ($this->request->getParameter("bookingsettingsMenustatus") > 0) {
                $modelAccess->exists($idSpace, $toolname)
                    ? $modelAccess->reactivate($idSpace, $toolname)
                    : $modelAccess->set($idSpace, $toolname, "booking", $toolname);
            } else {
                $modelAccess->delete($idSpace, $toolname);
            }
            return $this->redirect("bookingconfig/".$idSpace);
        }

        /*
        $formAuth = $this->bookingAuthorisationUseVisa($idSpace, $lang);
        if($formAuth->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkAuthorisationUseVisa", $this->request->getParameter("BkAuthorisationUseVisa"), $idSpace);

            $this->redirect("bookingconfig/".$idSpace);
            return;
        }
        */

        $formMenuName = $this->menuNameForm($idSpace, 'booking', $lang);
        if ($formMenuName->check()) {
            $this->setMenuName($idSpace, 'booking');
            return $this->redirect("bookingconfig/".$idSpace);
        }
        $formSettingsMenuName = $this->menuNameForm($idSpace, 'bookingsettings', $lang);
        if ($formSettingsMenuName->check()) {
            $this->setMenuName($idSpace, 'bookingsettings');
            return $this->redirect("bookingconfig/".$idSpace);
        }

        $formeditReservation = $this->editReservationPlugin($idSpace, $lang);
        if ($formeditReservation->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("bkReservationPlugin", $this->request->getParameter("bkReservationPlugin"), $idSpace);

            return $this->redirect("bookingconfig/".$idSpace);
        }


        $formbookingUseRecurentBooking = $this->bookingUseRecurentBooking($idSpace, $lang);
        if ($formbookingUseRecurentBooking->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkUseRecurentBooking", $this->request->getParameter("BkUseRecurentBooking"), $idSpace);

            return $this->redirect("bookingconfig/".$idSpace);
        }

        $formbookingSetDefaultView = $this->bookingSetDefaultView($idSpace, $lang);
        if ($formbookingSetDefaultView->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkSetDefaultView", $this->request->getParameter("BkSetDefaultView"), $idSpace);
            $modelConfig->setParam("BkDefaultViewType", $this->request->getParameter("BkDefaultViewType"), $idSpace);
            return $this->redirect("bookingconfig/".$idSpace);
        }

        /*
        $formBookingCanUserEditStartedResa = $this->bookingCanUserEditStartedResa($idSpace, $lang);
        if ($formBookingCanUserEditStartedResa->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkCanUserEditStartedResa", $this->request->getParameter("BkCanUserEditStartedResa"), $idSpace);

            $this->redirect("bookingconfig/".$idSpace);
            return;
        }
        */


        $formBookingOption = $this->bookingOptionForm($idSpace, $lang);
        if ($formBookingOption->check()) {
            $editBookingDescriptionSettings = $this->request->getParameterNoException("BkDescriptionFields");
            $modelCoreConfig = new CoreConfig();
            $modelCoreConfig->setParam("BkDescriptionFields", $editBookingDescriptionSettings, $idSpace);
            return $this->redirect("bookingconfig/".$idSpace);
        }

        $formEditBookingMailing = $this->editBookingMailingForm($idSpace, $lang);
        if ($formEditBookingMailing->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("BkEditBookingMailing", $this->request->getParameter("BkEditBookingMailing"), $idSpace);
            $modelConfig->setParam("BkBookingMailingAdmins", $this->request->getParameter("BkBookingMailingAdmins"), $idSpace);
            $modelConfig->setParam("BkBookingMailingDelete", $this->request->getParameter("BkBookingMailingDelete"), $idSpace);

            return $this->redirect("bookingconfig/".$idSpace);
        }

        $setbookingoptionsquery = $this->request->getParameterNoException("setbookingoptionsquery");
        if ($setbookingoptionsquery == "yes") {
            $this->optionsQuery($idSpace);

            return $this->redirect("bookingconfig/".$idSpace);
        } else {
            $modelBookingSettings = new BkBookingSettings();
            $bookingSettings = $modelBookingSettings->entries($idSpace, "display_order");
        }

        // view
        $forms = array(
            $formMenusactivation->getHtml($lang),
            //$formAuth->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formSettingsMenusactivation->getHtml($lang),
            $formSettingsMenuName->getHtml($lang),
            $formbookingUseRecurentBooking->getHtml($lang),
            $formbookingSetDefaultView->getHtml($lang),
            //$formBookingCanUserEditStartedResa->getHtml($lang),
            //$bookingRestrictionForm->getHtml($lang),
            $formBookingOption->getHtml($lang),
            $formeditReservation->getHtml($lang),
            $formEditBookingMailing->getHtml($lang));
        $this->render(array("id_space" => $idSpace, "forms" => $forms, "bookingSettings" => $bookingSettings, "lang" => $lang));
    }

    /**
     * @deprecated  unused
     */
    protected function bookingAuthorisationUseVisa($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkAuthorisationUseVisa = $modelCoreConfig->getParamSpace("BkAuthorisationUseVisa", $idSpace);
        $form = new Form($this->request, "BkAuthorisationUseVisaForm");
        $form->addSeparator(BookingTranslator::Use_Auth_Visa($lang));

        $form->addSelect("BkAuthorisationUseVisa", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkAuthorisationUseVisa);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    /**
     * @deprecated  unused
     */
    protected function bookingCanUserEditStartedResa($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkCanUserEditStartedResa = $modelCoreConfig->getParamSpace("BkCanUserEditStartedResa", $idSpace);

        $form = new Form($this->request, "bookingCanUserEditStartedResaForm");
        $form->addSeparator(BookingTranslator::CanUserEditStartedResa($lang));

        $form->addSelect("BkCanUserEditStartedResa", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkCanUserEditStartedResa);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    protected function bookingUseRecurentBooking($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkUseRecurentBooking = $modelCoreConfig->getParamSpace("BkUseRecurentBooking", $idSpace, 0);

        $form = new Form($this->request, "BkUseRecurentBookingForm");
        $form->addSeparator(BookingTranslator::Use_recurent_booking($lang));

        $form->addSelect("BkUseRecurentBooking", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkUseRecurentBooking);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    protected function bookingSetDefaultView($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkSetDefaultView = $modelCoreConfig->getParamSpace("BkSetDefaultView", $idSpace, "bookingweekarea");
        $BkDefaultViewType = $modelCoreConfig->getParamSpace("BkDefaultViewType", $idSpace, "simple");

        $form = new Form($this->request, "BkSetdefaultViewForm");
        $form->addSeparator(BookingTranslator::Set_default_booking_view($lang));

        $optionsNames = array(
            BookingTranslator::Day($lang),
            BookingTranslator::Day_Area($lang),
            BookingTranslator::Week($lang),
            BookingTranslator::Week_Area($lang),
            BookingTranslator::Month($lang)
        );
        $optionsValues = array(
            "bookingday",
            "bookingdayarea",
            "bookingweek",
            "bookingweekarea",
            "bookingmonth"
        );

        $form->addSelect("BkSetDefaultView", "", $optionsNames, $optionsValues, $BkSetDefaultView);
        $form->addSelect(
            "BkDefaultViewType",
            "",
            [BookingTranslator::SimpleView($lang), BookingTranslator::DetailedView($lang)],
            ['simple', 'detailed'],
            $BkDefaultViewType
        );

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    protected function bookingOptionForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParamSpace("BkDescriptionFields", $idSpace, 0);

        $form = new Form($this->request, "bookingOptionForm");
        $form->addSeparator(BookingTranslator::Edit_booking_options($lang));

        $choices = array(
        ' -- ',
        BookingTranslator::Both_short_and_full_description($lang),
        BookingTranslator::Only_short_description($lang),
        BookingTranslator::Only_full_description($lang));
        $form->addSelect("BkDescriptionFields", BookingTranslator::Description_fields($lang), $choices, array(0,1,2,3), $BkDescriptionFields);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    protected function editReservationPlugin($idSpace, $lang)
    {
        $modelConfig = new CoreConfig();
        $bkReservationPlugin = $modelConfig->getParamSpace("bkReservationPlugin", $idSpace);

        $form = new Form($this->request, "editReservationPluginForm");
        $form->addSeparator(BookingTranslator::EditReservationPlugin($lang));
        $form->addText("bkReservationPlugin", BookingTranslator::Url($lang), false, $bkReservationPlugin);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }


    protected function editBookingMailingForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $BkEditBookingMailing = $modelCoreConfig->getParamSpace("BkEditBookingMailing", $idSpace);
        $BkBookingMailingAdmins = $modelCoreConfig->getParamSpace("BkBookingMailingAdmins", $idSpace);
        $BkBookingMailingDelete = $modelCoreConfig->getParamSpace("BkBookingMailingDelete", $idSpace);
        if ($BkBookingMailingDelete == "") {
            $BkBookingMailingDelete = 0;
        }

        $form = new Form($this->request, "editBookingMailingForm");
        $form->addSeparator(BookingTranslator::EditBookingMailing($lang));

        $form->addSelect('BkEditBookingMailing', BookingTranslator::Send_emails($lang), array(BookingTranslator::Never($lang), BookingTranslator::When_manager_admin_edit_a_reservation($lang)), array(1,2), $BkEditBookingMailing);
        $form->addSelect('BkBookingMailingAdmins', BookingTranslator::EmailManagers($lang), array(BookingTranslator::Never($lang), BookingTranslator::WhenAUserBook($lang)), array(1,2), $BkBookingMailingAdmins);
        $form->addSelect('BkBookingMailingDelete', BookingTranslator::EmailWhenResaDelete($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $BkBookingMailingDelete);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingconfig/".$idSpace);

        return $form;
    }

    protected function optionsQuery($idSpace)
    {
        // set booking settings
        $modelBookingSettings = new BkBookingSettings();

        $optionTags = $modelBookingSettings->getTagNames($idSpace) ?? BkBookingSettings::DEFAULT_TAG_NAMES;

        foreach ($optionTags as $optTag) {
            $trimOptTag = str_replace(' ', '', $optTag);

            $tag_visible = $this->request->getParameterNoException("tag_visible_".$trimOptTag);
            $tag_title_visible = $this->request->getParameterNoException("tag_title_visible_".$trimOptTag);
            $tag_position = $this->request->getParameterNoException("tag_position_".$trimOptTag);
            $tag_font = $this->request->getParameterNoException("tag_font_".$trimOptTag);

            $modelBookingSettings->setEntry(
                $optTag,
                $tag_visible,
                $tag_title_visible,
                $tag_position,
                $tag_font,
                $idSpace
            );
        }
        return $modelBookingSettings->entries($idSpace, "display_order");
    }
}
