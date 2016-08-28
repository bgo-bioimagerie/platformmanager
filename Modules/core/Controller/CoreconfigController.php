<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreBackupDatabase.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoreconfigController extends CoresecureController {

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
        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMaintenance = $this->maintenanceForm($modelCoreConfig, $lang);
        if ($formMaintenance->check()) {
            $modelCoreConfig->setParam("is_maintenance", $this->request->getParameter("is_maintenance"));
            $modelCoreConfig->setParam("maintenance_message", $this->request->getParameter("maintenance_message"));
            $this->redirect("coreconfig");
            return;
        }
        // install form
        $formInstall = $this->installForm($lang);
        if ($formInstall->check()) {
            $message = "<b>Success:</b> the database have been successfully installed";
            try {
                $installModel = new CoreInstall();
                $installModel->createDatabase();
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            $this->redirect("coreconfig");
            return;
        }
        /*
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang);
        if ($formMenusactivation->check()) {

            $modelMenu = new CoreMenu();
            $modelMenu->setDataMenu("users", "coreusers", $this->request->getParameter("usermenustatus"), "glyphicon-user");

            $this->redirect("coreconfig");
            return;
        }
         
         */
        // maintenance form
        $formLdap = $this->ldapForm($lang);
        if ($formLdap->check()) {
            
            $this->redirect("coreldapconfig");
            return;
        }
        // homePageForm
        $formHomePage = $this->homePageForm($modelCoreConfig, $lang);
        if ($formHomePage->check()) {
            $modelCoreConfig->setParam("default_home_path", $this->request->getParameter("default_home_path"));
            $this->redirect("coreconfig");
            return;
        }
        // formConnectionPage
        $formConnectionPage = $this->connectionPageForm($modelCoreConfig, $lang);
        if ($formConnectionPage->check()) {
            $modelCoreConfig->setParam("home_title", $this->request->getParameter("home_title"));
            $modelCoreConfig->setParam("home_message", $this->request->getParameter("home_message"));
            $modelCoreConfig->setParam("home_view_carousel", $this->request->getParameter("home_view_carousel"));

            for ($i = 1; $i < 4; $i++) {
                $target_dir = "data/core/";
                if ($_FILES["image_url" . $i]["name"] != "") {
                    Upload::uploadFile($target_dir, "image_url" . $i);
                    $modelCoreConfig->setParam("connection_carousel" . strval($i), $target_dir . $_FILES["image_url" . $i]["name"]);
                }
            }
            $this->redirect("coreconfig");
            return;
        }
        // desactivateUserForm
        $formDesactivateUser = $this->desactivateUserForm($modelCoreConfig, $lang);
        if ($formDesactivateUser->check()){
            $modelCoreConfig->setParam("user_desactivate", $this->request->getParameter("user_desactivate"));
        
            $this->redirect("coreconfig");
            return;
        }
        // email form
        $formEmail = $this->emailForm($modelCoreConfig, $lang);
        if ($formEmail->check()){
            $modelCoreConfig->setParam("admin_email", $this->request->getParameter("admin_email"));
        
            $this->redirect("coreconfig");
            return;
        }
        $formNavbar = $this->navbarColorForm($modelCoreConfig, $lang);
        if($formNavbar->check()){
            
            $modelCoreConfig->setParam("navbar_bg_color", $this->request->getParameter("navbar_bg_color"));
            $modelCoreConfig->setParam("navbar_bg_highlight", $this->request->getParameter("navbar_bg_highlight"));
            $modelCoreConfig->setParam("navbar_text_color", $this->request->getParameter("navbar_text_color"));
            $modelCoreConfig->setParam("navbar_text_highlight", $this->request->getParameter("navbar_text_highlight"));
            
            $css = file_get_contents("Modules/core/Theme/navbar-fixed-top.css");
            $css = str_replace("navbar_bg_color", $this->request->getParameter("navbar_bg_color"), $css);
            $css = str_replace("navbar_bg_highlight", $this->request->getParameter("navbar_bg_highlight"), $css);
            $css = str_replace("navbar_text_color", $this->request->getParameter("navbar_text_color"), $css);
            $css = str_replace("navbar_text_highlight", $this->request->getParameter("navbar_text_highlight"), $css);

            file_put_contents("data/core/theme/navbar-fixed-top.css", $css);
        
            $this->redirect("coreconfig");
            return;
        }
        // backup form
        $formBackup = $this->backupForm($lang);
        if ($formBackup->check()){
            $modelBackup = new CoreBackupDatabase();
            $modelBackup->run();
            $this->redirect("coreconfig");
            return;
        }
        // view
        $forms = array($formMaintenance->getHtml($lang), $formInstall->getHtml($lang), 
            $formDesactivateUser->getHtml($lang), 
            $formLdap->getHtml($lang), $formHomePage->getHtml($lang),
            $formConnectionPage->getHtml($lang), 
            $formEmail->getHtml($lang), $formNavbar->getHtml($lang), $formBackup->getHtml($lang));
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function maintenanceForm($modelCoreConfig, $lang) {
        $is_maintenance = $modelCoreConfig->getParam("is_maintenance");
        $maintenance_message = $modelCoreConfig->getParam("maintenance_message");

        $formMaintenance = new Form($this->request, "maintenanceForm");
        $formMaintenance->addSeparator(CoreTranslator::Maintenance_Mode($lang));
        $formMaintenance->addSelect("is_maintenance", CoreTranslator::InMaintenance($lang), array(CoreTranslator::No($lang), CoreTranslator::Yes($lang)), array(0, 1), $is_maintenance);
        $formMaintenance->addTextArea("maintenance_message", CoreTranslator::MaintenanceMessage($lang), false, $maintenance_message, false);
        $formMaintenance->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        $formMaintenance->setButtonsWidth(2, 9);

        return $formMaintenance;
    }

    protected function installForm($lang) {

        $form = new Form($this->request, "installForm");
        $form->addSeparator(CoreTranslator::Install_Repair_database($lang));
        $form->addComment(CoreTranslator::Install_Txt($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusactivationForm($lang) {

        $modelMenu = new CoreMenu();
        $statusUserMenu = $modelMenu->getDataMenusUserType("users");

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

        $form->addSelect("usermenustatus", CoreTranslator::Users($lang), $choices, $choicesid, $statusUserMenu);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function ldapForm($lang){
        
        $form = new Form($this->request, "ldapForm");
        $form->addSeparator(CoreTranslator::LdapConfig($lang));
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Config($lang), "coreconfig");
        return $form;
    }
    
    protected function homePageForm($modelCoreConfig, $lang){
        
        $form = new Form($this->request, "homePageForm");
        $form->addSeparator(CoreTranslator::Home($lang));
        $form->addText("default_home_path", CoreTranslator::Home_page($lang), true, $modelCoreConfig->getParam("default_home_path"));
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        return $form;
    }
    
    protected function connectionPageForm($modelCoreConfig, $lang) {

        $home_title = $modelCoreConfig->getParam("home_title");
        $home_message = $modelCoreConfig->getParam("home_message");
        $home_view_carousel = $modelCoreConfig->getParam("home_view_carousel");

        $form = new Form($this->request, "connectionPageForm");
        $form->addSeparator(CoreTranslator::ConnectionPageData($lang));
        $form->addText("home_title", CoreTranslator::title($lang), false, $home_title);
        $form->addText("home_message", CoreTranslator::Description($lang), false, $home_message);
        $form->addSelect("home_view_carousel", CoreTranslator::ViewCarousel($lang), array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0, 1), $home_view_carousel);

        for ($i = 1; $i < 4; $i++) {
            $form->addSeparator2(CoreTranslator::Carousel($lang) . " " . strval($i));
            $form->addUpload("image_url" . strval($i), CoreTranslator::Image_Url($lang));
        }
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        return $form;
    }
    
    protected function desactivateUserForm($modelCoreConfig, $lang){
        
	$value = $modelCoreConfig->getParam("user_desactivate");
        
        $choices = array();
        $choicesid = array();
        $choicesid[] = 1; $choices[] = CoreTranslator::never($lang);
        $choicesid[] = 2; $choices[] = CoreTranslator::contract_ends($lang);
        $choicesid[] = 3; $choices[] = CoreTranslator::does_not_login_for_n_year(1, $lang);
        $choicesid[] = 4; $choices[] = CoreTranslator::does_not_login_for_n_year(2, $lang);
        $choicesid[] = 5; $choices[] = CoreTranslator::does_not_login_for_n_year(3, $lang);
        
        $form = new Form($this->request, "desactivateUserForm");
        $form->addSeparator(CoreTranslator::non_active_users($lang));
        $form->addSelect("user_desactivate", CoreTranslator::Disable_user_account_when($lang), 
                $choices, $choicesid, $value);
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        return $form;
    }

    protected function emailForm($modelCoreConfig, $lang){
        $value = $modelCoreConfig->getParam("admin_email");
        
        $form = new Form($this->request, "emailForm");
        $form->addSeparator(CoreTranslator::non_active_users($lang));
        $form->addText("admin_email", CoreTranslator::Email($lang), false, $value);
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        return $form;
    }
    
    protected function backupForm($lang){
        
        $form = new Form($this->request, "backupForm");
        $form->addSeparator(CoreTranslator::Backup($lang));
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Run_backup($lang), "coreconfig");
        return $form;
    }
    
    protected function navbarColorForm($modelCoreConfig, $lang){
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
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreconfig");
        return $form;
    }
}
