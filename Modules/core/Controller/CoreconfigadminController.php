<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';

require_once 'Framework/Form.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreInstall.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreConfig.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoreconfigadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied");
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {


        $lang = $this->getLanguage();
        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMaintenance = $this->maintenanceForm($modelCoreConfig, $lang);
        if ($formMaintenance->check()) {
            $modelCoreConfig->setParam("is_maintenance", $this->request->getParameter("is_maintenance"));
            $modelCoreConfig->setParam("maintenance_message", $this->request->getParameter("maintenance_message"));
            $this->redirect("coreconfigadmin");
            return;
        }

        // ldap form
        /*
        $formLdap = $this->ldapForm($lang);
        if ($formLdap->check()) {

            $this->redirect("coreldapconfig");
            return;
        }
        */
        



        // homePageForm
        $formHomePage = $this->homePageForm($modelCoreConfig, $lang);
        if ($formHomePage->check()) {
            $modelCoreConfig->setParam("default_home_path", $this->request->getParameter("default_home_path"));
            $this->redirect("coreconfigadmin");
            return;
        }

        // formConnectionPage
        $formConnectionPage = $this->connectionPageForm($modelCoreConfig, $lang);
        if ($formConnectionPage->check()) {
            $modelCoreConfig->setParam("home_title", $this->request->getParameter("home_title"));
            $modelCoreConfig->setParam("home_message", $this->request->getParameter("home_message"));
            $this->redirect("coreconfigadmin");
            return;
        }

        // space dashboard icons
        $formSpaceIconsForm = $this->spaceIconForm($modelCoreConfig, $lang);
        if ($formSpaceIconsForm->check()) {
            $modelCoreConfig->setParam("space_icon_type", $this->request->getParameter("space_icon_type"));

            $this->redirect("coreconfigadmin");
            return;
        }

        // desactivateUserForm
        $formDesactivateUser = $this->desactivateUserForm($modelCoreConfig, $lang);
        if ($formDesactivateUser->check()) {
            $modelCoreConfig->setParam("user_desactivate", $this->request->getParameter("user_desactivate"));

            $this->redirect("coreconfigadmin");
            return;
        }

        // email form
        $formEmail = $this->emailForm($modelCoreConfig, $lang);
        if ($formEmail->check()) {
            $modelCoreConfig->setParam("admin_email", $this->request->getParameter("admin_email"));

            $this->redirect("coreconfigadmin");
            return;
        }

        $formNavbar = $this->navbarColorForm($modelCoreConfig, $lang);
        if ($formNavbar->check()) {

            $modelCoreConfig->setParam("navbar_bg_color", $this->request->getParameter("navbar_bg_color"));
            $modelCoreConfig->setParam("navbar_bg_highlight", $this->request->getParameter("navbar_bg_highlight"));
            $modelCoreConfig->setParam("navbar_text_color", $this->request->getParameter("navbar_text_color"));
            $modelCoreConfig->setParam("navbar_text_highlight", $this->request->getParameter("navbar_text_highlight"));

            $css = file_get_contents("Modules/core/Theme/navbar-fixed-top.css");
            $css = str_replace("navbar_bg_color", $this->request->getParameter("navbar_bg_color"), $css);
            $css = str_replace("navbar_bg_highlight", $this->request->getParameter("navbar_bg_highlight"), $css);
            $css = str_replace("navbar_text_color", $this->request->getParameter("navbar_text_color"), $css);
            $css = str_replace("navbar_text_highlight", $this->request->getParameter("navbar_text_highlight"), $css);

            file_put_contents("externals/core/theme/navbar-fixed-top.css", $css);

            $this->redirect("coreconfigadmin");
            return;
        }

        // view
        $forms = array($formMaintenance->getHtml($lang),
            $formSpaceIconsForm->getHtml($lang),
            $formDesactivateUser->getHtml($lang),
            $formHomePage->getHtml($lang),
            $formConnectionPage->getHtml($lang),
            $formEmail->getHtml($lang),
        );

        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    /**
     *
     * @param type $modelCoreConfig
     * @param type $lang
     * @return \Form
     */
    protected function maintenanceForm($modelCoreConfig, $lang) {
        $is_maintenance = $modelCoreConfig->getParam("is_maintenance", 0);
        $maintenance_message = $modelCoreConfig->getParam("maintenance_message");

        $formMaintenance = new Form($this->request, "maintenanceForm");
        $formMaintenance->addSeparator(CoreTranslator::Maintenance_Mode($lang));
        $formMaintenance->addSelect("is_maintenance", CoreTranslator::InMaintenance($lang), array(CoreTranslator::No($lang), CoreTranslator::Yes($lang)), array(0, 1), $is_maintenance);
        $formMaintenance->addTextArea("maintenance_message", CoreTranslator::MaintenanceMessage($lang), false, $maintenance_message, false);
        $formMaintenance->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");


        return $formMaintenance;
    }

    /**
     *
     * @param type $lang
     * @return \Form
     */
    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(CoreTranslator::Install_Repair_database($lang));
        $form->addComment(CoreTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");


        return $form;
    }

    /**
     *
     * @param type $lang
     * @return \Form
     */
    protected function ldapForm($lang) {

        $form = new Form($this->request, "ldapForm");
        $form->addSeparator(CoreTranslator::LdapConfig($lang));


        $form->setValidationButton(CoreTranslator::Config($lang), "coreconfigadmin");
        return $form;
    }

    /**
     *
     * @param type $modelCoreConfig
     * @param string $lang
     * @return \Form
     */
    protected function homePageForm($modelCoreConfig, $lang) {

        $form = new Form($this->request, "homePageForm");
        $form->addSeparator(CoreTranslator::Home($lang));
        $form->addText("default_home_path", CoreTranslator::Home_page($lang), true, $modelCoreConfig->getParam("default_home_path"));


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    /**
     *
     * @param type $modelCoreConfig
     * @param string $lang
     * @return \Form
     */
    protected function connectionPageForm($modelCoreConfig, $lang) {

        $home_title = $modelCoreConfig->getParam("home_title");
        $home_message = $modelCoreConfig->getParam("home_message");

        $form = new Form($this->request, "connectionPageForm");
        $form->addSeparator(CoreTranslator::ConnectionPageData($lang));
        $form->addText("home_title", CoreTranslator::title($lang), false, $home_title);
        $form->addText("home_message", CoreTranslator::Description($lang), false, $home_message);

        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    protected function spaceIconForm($modelCoreConfig, $lang) {
        $value = $modelCoreConfig->getParam("space_icon_type", 2);
        $choices = array();
        $choicesid = array();
        $choicesid[] = 1;
        $choices[] = CoreTranslator::smallIcons($lang);
        $choicesid[] = 2;
        $choices[] = CoreTranslator::IconsWithDescription($lang);

        $form = new Form($this->request, "spaceIconForm");
        $form->addSeparator(CoreTranslator::SpaceIcons($lang));
        $form->addSelect("space_icon_type", CoreTranslator::Choice($lang), $choices, $choicesid, $value);


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    /**
     *
     * @param type $modelCoreConfig
     * @param string $lang
     * @return \Form
     */
    protected function desactivateUserForm($modelCoreConfig, $lang) {

        $value = $modelCoreConfig->getParam("user_desactivate", 1);

        $cc = new CoreConfig();
        $expirationChoices = $cc->getExpirationChoices($lang);
        $choices = $expirationChoices['labels'];
        $choicesid = $expirationChoices['ids'];


        $form = new Form($this->request, "desactivateUserForm");
        $form->addSeparator(CoreTranslator::non_active_users($lang));
        $form->addSelect("user_desactivate", CoreTranslator::Disable_user_account_when($lang), $choices, $choicesid, $value);


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    /**
     *
     * @param type $modelCoreConfig
     * @param type $lang
     * @return \Form
     */
    protected function emailForm($modelCoreConfig, $lang) {
        $value = $modelCoreConfig->getParam("admin_email");

        $form = new Form($this->request, "emailForm");
        $form->addSeparator(CoreTranslator::Admin($lang));
        $form->addText("admin_email", CoreTranslator::Email($lang), false, $value);


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    /**
     * @deprecated
     * @param type $lang
     * @return \Form
     */
    protected function backupForm($lang) {

        $form = new Form($this->request, "backupForm");
        $form->addSeparator(CoreTranslator::Backup($lang));


        $form->setValidationButton(CoreTranslator::Run_backup($lang), "coreconfigadmin");
        return $form;
    }

    /**
     * @deprecated
     * @param type $modelCoreConfig
     * @param type $lang
     * @return \Form
     */
    protected function navbarColorForm($modelCoreConfig, $lang) {
        $navbar_bg_color = $modelCoreConfig->getParam("navbar_bg_color");
        $navbar_bg_highlight = $modelCoreConfig->getParam("navbar_bg_highlight");
        $navbar_text_color = $modelCoreConfig->getParam("navbar_text_color");
        $navbar_text_highlight = $modelCoreConfig->getParam("navbar_text_highlight");

        $form = new Form($this->request, "navbarColorForm");
        $form->addSeparator(CoreTranslator::menu_color($lang));
        $form->addColor("navbar_bg_color", CoreTranslator::Background_color($lang), false, $navbar_bg_color);
        $form->addColor("navbar_bg_highlight", CoreTranslator::Background_highlight($lang), false, $navbar_bg_highlight);
        $form->addColor("navbar_text_color", CoreTranslator::Text_color($lang), false, $navbar_text_color);
        $form->addColor("navbar_text_highlight", CoreTranslator::Text_highlight($lang), false, $navbar_text_highlight);


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

    /**
     * @deprecated
     */
    protected function whoCanDeleteUserForm($modelCoreConfig, $lang) {
        $who_can_delete_user = $modelCoreConfig->getParam("who_can_delete_user", 2);

        $form = new Form($this->request, "whoCanDeleteUserForm");
        $form->addSeparator(CoreTranslator::Who_can_delete_users($lang));

        $choices = array(
            CoreTranslator::User($lang),
            CoreTranslator::Admin($lang)
        );
        $choicesid = array(1, 2);

        $form->addSelect("who_can_delete_user", CoreTranslator::Select($lang), $choices, $choicesid, $who_can_delete_user);


        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfigadmin");
        return $form;
    }

}
