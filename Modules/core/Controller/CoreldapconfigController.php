<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreMenu.php';
require_once 'Modules/core/Model/CoreLdapConfiguration.php';

/**
 * 
 * @author sprigent
 * 
 * Config the LDAP access
 */
class CoreldapconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorization(CoreStatus::$ADMIN);
    }
    
    /**
     * (non-PHPdoc)
     * Show the config index page
     * 
     * @see Controller::index()
     */
    public function indexAction() {


        // LDAP configuration

        $modelSettings = new CoreConfig();
        $ldapConfig["useLdap"] = $modelSettings->getParam("useLdap");
        $ldapConfig["ldapDefaultStatus"] = $modelSettings->getParam("ldapDefaultStatus");
        $ldapConfig["ldapSearchAtt"] = $modelSettings->getParam("ldapSearchAtt");
        $ldapConfig["ldapNameAtt"] = $modelSettings->getParam("ldapNameAtt");
        $ldapConfig["ldapFirstnameAtt"] = $modelSettings->getParam("ldapFirstnameAtt");
        $ldapConfig["ldapMailAtt"] = $modelSettings->getParam("ldapMailAtt");

        // LDAP connection
        $ldapConnect["ldapAdress"] = CoreLdapConfiguration::get("ldapAdress", "");
        $ldapConnect["ldapPort"] = CoreLdapConfiguration::get("ldapPort", "");
        $ldapConnect["ldapId"] = CoreLdapConfiguration::get("ldapId", "");
        $ldapConnect["ldapPwd"] = CoreLdapConfiguration::get("ldapPwd", "");
        $ldapConnect["ldapBaseDN"] = CoreLdapConfiguration::get("ldapBaseDN", "");
        $ldapConnect["ldapUseTls"] = CoreLdapConfiguration::get("ldapUseTls", "");
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "coreldapconfig");
        //$form->setTitle(CoreTranslator::LdapConfig($lang));
        
        $form->addSeparator(CoreTranslator::LdapConfig($lang));
        $form->addSelect("useLdap", CoreTranslator::UseLdap($lang), 
                array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $ldapConfig["useLdap"]);
        $form->addSelect("ldapDefaultStatus", CoreTranslator::userDefaultStatus($lang), 
                array(CoreTranslator::Translate_status($lang, "visitor"), CoreTranslator::Translate_status($lang, "user")), array(1,2), $ldapConfig["ldapDefaultStatus"]);
        $form->addText("ldapSearchAtt", CoreTranslator::ldapSearch($lang), false, $ldapConfig["ldapSearchAtt"]);
        $form->addText("ldapNameAtt", CoreTranslator::ldapName($lang), false, $ldapConfig["ldapNameAtt"]);
        $form->addText("ldapFirstnameAtt", CoreTranslator::ldapFirstname($lang), false, $ldapConfig["ldapFirstnameAtt"]);
        $form->addText("ldapMailAtt", CoreTranslator::ldapMail($lang), false, $ldapConfig["ldapMailAtt"]);
        
        $form->addSeparator(CoreTranslator::LdapAccess($lang));
        $form->addText("ldapAdress", CoreTranslator::ldapAdress($lang), false, $ldapConnect["ldapAdress"]);
        $form->addText("ldapPort", CoreTranslator::ldapPort($lang), false, $ldapConnect["ldapPort"]);
        $form->addText("ldapId", CoreTranslator::ldapId($lang), false, $ldapConnect["ldapId"]);
        $form->addPassword("ldapPwd", CoreTranslator::ldapPwd($lang), false);
        $form->addText("ldapBaseDN", CoreTranslator::ldapBaseDN($lang), false, $ldapConnect["ldapBaseDN"]);
        $form->addSelect("ldapUseTls", CoreTranslator::UseTLS($lang), 
                array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array("TRUE","FALSE"), $ldapConnect["ldapUseTls"]);
        
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "coreldapconfig");
        
        if ($form->check()){
            $this->editquery();
            $this->redirect("coreconfigadmin");
            return;
        }
        
        $this->render( array("formHtml" => $form->getHtml($lang)));
        
    }

    /**
     * Edit the LDAP access informations
     */
    protected function editquery() {

        // get the post parameters
        $ldapConfig["useLdap"] = $this->request->getParameter("useLdap");
        $ldapConfig["ldapDefaultStatus"] = $this->request->getParameter("ldapDefaultStatus");
        $ldapConfig["ldapSearchAtt"] = $this->request->getParameter("ldapSearchAtt");
        $ldapConfig["ldapNameAtt"] = $this->request->getParameter("ldapNameAtt");
        $ldapConfig["ldapFirstnameAtt"] = $this->request->getParameter("ldapFirstnameAtt");
        $ldapConfig["ldapMailAtt"] = $this->request->getParameter("ldapMailAtt");
        
        
        $ldapConnect["ldapAdress"] = $this->request->getParameter("ldapAdress", "");
        $ldapConnect["ldapPort"] = $this->request->getParameter("ldapPort", "");
        $ldapConnect["ldapId"] = $this->request->getParameter("ldapId", "");
        $ldapConnect["ldapPwd"] = $this->request->getParameter("ldapPwd", "");
        $ldapConnect["ldapBaseDN"] = $this->request->getParameter("ldapBaseDN", "");
        $ldapConnect["ldapUseTls"] = $this->request->getParameter("ldapUseTls");
        

        // update the database
        $modelSettings = new CoreConfig();
        $modelSettings->setParam("useLdap", $ldapConfig["useLdap"]);
        $modelSettings->setParam("ldapDefaultStatus", $ldapConfig["ldapDefaultStatus"]);
        $modelSettings->setParam("ldapSearchAtt", $ldapConfig["ldapSearchAtt"]);
        $modelSettings->setParam("ldapNameAtt", $ldapConfig["ldapNameAtt"]);
        $modelSettings->setParam("ldapFirstnameAtt", $ldapConfig["ldapFirstnameAtt"]);
        $modelSettings->setParam("ldapMailAtt", $ldapConfig["ldapMailAtt"]);
        
        // update the config file

        $fileContent = "; Configuration for ldap" . "\n"
                . "ldapAdress = \"" . $ldapConnect["ldapAdress"] . "\"" . "\n"
                . "ldapPort = \"" . $ldapConnect["ldapPort"] . "\"" . "\n"
                . "ldapId = \"" . $ldapConnect["ldapId"] . "\"" . "\n"
                . "ldapPwd = \"" . $ldapConnect["ldapPwd"] . "\"" . "\n"
                . "ldapBaseDN = \"" . $ldapConnect["ldapBaseDN"] . "\"" . "\n"
                . "ldapUseTls = \"" . $ldapConnect["ldapUseTls"] . "\"" . "\n";
        file_put_contents("Config/ldap.ini", $fileContent);

        $this->redirect("coreconfig");
    }

}
