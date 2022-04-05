<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreLdapConfiguration.php';

/**
 * @deprecated
 * @author sprigent
 * 
 * Config the LDAP access
 */
class CoreldapconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
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

        //$modelSettings = new CoreConfig();
        $ldapConfig["useLdap"] = CoreLdapConfiguration::get('ldap_use', 0);
        $ldapConfig["ldapDefaultStatus"] = CoreLdapConfiguration::get('ldap_default_status', 1);
        $ldapConfig["ldapSearchAtt"] = CoreLdapConfiguration::get('ldap_search_attr', 'uid');
        $ldapConfig["ldapNameAtt"] = CoreLdapConfiguration::get('ldap_name_attr', 'sn');
        $ldapConfig["ldapFirstnameAtt"] = CoreLdapConfiguration::get('ldap_firstname_attr', 'givenname');
        $ldapConfig["ldapMailAtt"] = CoreLdapConfiguration::get('ldap_mail_attr', 'mail');

        // LDAP connection
        $ldapConnect["ldap_host"] = CoreLdapConfiguration::get("ldap_host", "");
        $ldapConnect["ldap_port"] = CoreLdapConfiguration::get("ldap_port", 389);
        $ldapConnect["ldap_user"] = CoreLdapConfiguration::get("ldap_user", "");
        $ldapConnect["ldap_password"] = CoreLdapConfiguration::get("ldap_password", "");
        $ldapConnect["ldap_dn"] = CoreLdapConfiguration::get("ldap_search_dn", "");
        $ldapConnect["ldap_tls"] = CoreLdapConfiguration::get("ldap_tls", false);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "coreldapconfig");
        
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
        $form->addText("ldap_host", CoreTranslator::ldapAdress($lang), false, $ldapConnect["ldap_host"], false, true);
        $form->addText("ldap_port", CoreTranslator::ldapPort($lang), false, $ldapConnect["ldap_port"], false, true);
        $form->addText("ldap_user", CoreTranslator::ldapId($lang), false, $ldapConnect["ldap_user"], false, true);
        $form->addText("ldap_dn", CoreTranslator::ldapBaseDN($lang), false, $ldapConnect["ldap_dn"], false, true);        
        
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
        
        /*
        $ldapConnect["ldap_host"] = $this->request->getParameter("ldap_host", "");
        $ldapConnect["ldap_port"] = intval($this->request->getParameter("ldap_port", 389));
        $ldapConnect["ldap_user"] = $this->request->getParameter("ldap_user", "");
        $ldapConnect["ldap_password"] = $this->request->getParameter("ldap_password", "");
        $ldapConnect["ldap_dn"] = $this->request->getParameter("ldap_dn", "");
        $ldapConnect["ldap_tls"] = $this->request->getParameter("ldap_tls");
        */
        

        // update the database
        $modelSettings = new CoreConfig();
        $modelSettings->setParam("useLdap", $ldapConfig["useLdap"]);
        $modelSettings->setParam("ldapDefaultStatus", $ldapConfig["ldapDefaultStatus"]);
        $modelSettings->setParam("ldapSearchAtt", $ldapConfig["ldapSearchAtt"]);
        $modelSettings->setParam("ldapNameAtt", $ldapConfig["ldapNameAtt"]);
        $modelSettings->setParam("ldapFirstnameAtt", $ldapConfig["ldapFirstnameAtt"]);
        $modelSettings->setParam("ldapMailAtt", $ldapConfig["ldapMailAtt"]);
        
        // update the config file
        /*
        $useTls = 0;
        if($ldapConnect["ldap_tls"] == "TRUE") {
            $useTls = 1;
        }
        $fileContent = "; Configuration for ldap" . "\n"
                . "ldap_host = \"" . $ldapConnect["ldap_host"] . "\"" . "\n"
                . "ldap_port = \"" . intval($ldapConnect["ldap_port"]) . "\"" . "\n"
                . "ldap_user = \"" . $ldapConnect["ldap_user"] . "\"" . "\n"
                . "ldap_password = \"" . $ldapConnect["ldap_password"] . "\"" . "\n"
                . "ldap_search_dn = \"" . $ldapConnect["ldap_dn"] . "\"" . "\n"
                . "ldap_tns = " . $useTls . "\n";
        file_put_contents("Config/ldap.ini", $fileContent);
        */

        $this->redirect("coreconfig");
    }

}
