<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/core/Model/CoreTranslator.php';

/**
 * Controller to edit user settings
 *
 * @author sprigent
 *
 */
class CoreusersettingsController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction()
    {
        $user_id = $this->request->getSession()->getAttribut("id_user");
        $userSettingsModel = new CoreUserSettings();
        $arrayDefaultDisplay = $userSettingsModel->getUserSetting($user_id, "arrayDefaultDisplay", "10");

        $lang = $this->getLanguage();
        $form = new Form($this->request, "coreusersettingsform");
        $form->setTitle(CoreTranslator::CoreDefaults($lang));

        $form->addSelect("arrayDefaultDisplay", CoreTranslator::DefaultsArrayView($lang), ["10", "50", "100", "All"], ["10", "50", "100", "-1"], $arrayDefaultDisplay);

        $form->setValidationButton(CoreTranslator::Ok($lang), "coreusersettings");
        $form->setCancelButton(CoreTranslator::Cancel($lang), "coresettings");

        if ($form->check()) {
            $arrayDefaultDisplay = $this->request->getParameter("arrayDefaultDisplay");

            $user_id = $this->request->getSession()->getAttribut("id_user");

            $userSettingsModel = new CoreUserSettings();
            $userSettingsModel->setSettings($user_id, "arrayDefaultDisplay", $arrayDefaultDisplay);

            $userSettingsModel->updateSessionSettingVariable();

            $this->redirect("coreusersettings");
        }


        $this->render(array(
            'lang' => $this->getLanguage(),
            'form' => $form->getHtml($lang)
        ));
    }
}
