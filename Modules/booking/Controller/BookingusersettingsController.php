<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/booking/Model/BookingTranslator.php';

/**
 * Controller to edit user settings
 *
 * @author sprigent
 *
 */
class BookingusersettingsController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction()
    {
        $user_id = $this->request->getSession()->getAttribut("id_user");
        $userSettingsModel = new CoreUserSettings();
        $calendarDefaultView = $userSettingsModel->getUserSetting($user_id, "calendarDefaultView", "weekarea");
        $bkDefaultViewType = $userSettingsModel->getUserSetting($user_id, "BkDefaultViewType", "simple");

        $lang = $this->getLanguage();
        $form = new Form($this->request, "bokkingusersettingsform");
        $form->setTitle(BookingTranslator::Calendar_Default_view($lang));

        $choicesview = array(BookingTranslator::Day($lang), BookingTranslator::Day_Area($lang), BookingTranslator::Week($lang), BookingTranslator::Week_Area($lang), BookingTranslator::Month($lang));
        $choicesidview = array("bookingday", "bookingdayarea", "bookingweek", "bookingweekarea", "bookingmonth");
        $form->addSelect("calendarDefaultView", BookingTranslator::Default_view($lang), $choicesview, $choicesidview, $calendarDefaultView);

        $form->addSelect(
            "BkDefaultViewType",
            "",
            [BookingTranslator::SimpleView($lang), BookingTranslator::DetailedView($lang)],
            ['simple', 'detailed'],
            $bkDefaultViewType
        );


        $form->setValidationButton(CoreTranslator::Ok($lang), "bookingusersettings");
        $form->setCancelButton(CoreTranslator::Cancel($lang), "coresettings");

        if ($form->check()) {
            $calendarDefaultView = $this->request->getParameter("calendarDefaultView");
            $bkDefaultViewType = $this->request->getParameter("BkDefaultViewType");

            $user_id = $this->request->getSession()->getAttribut("id_user");

            $userSettingsModel = new CoreUserSettings();
            $userSettingsModel->setSettings($user_id, "calendarDefaultView", $calendarDefaultView);
            $userSettingsModel->setSettings($user_id, "BkDefaultViewType", $bkDefaultViewType);

            $userSettingsModel->updateSessionSettingVariable();

            $this->redirect("bookingusersettings");
        }


        $this->render(array(
            'lang' => $this->getLanguage(),
            'form' => $form->getHtml($lang)
        ));
    }
}
