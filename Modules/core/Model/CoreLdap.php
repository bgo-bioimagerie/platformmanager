<?php

require_once 'Modules/core/Model/CoreLdapConfiguration.php';
require_once 'Framework/Configuration.php';

/**
 * Class defining the  LDAP model to connect 
 * user from LDAP
 * 
 * @author Sylvain Prigent from GRR
 */
class CoreLdap {

    /**
     * Get the user information using LDAP
     * @param string $_login User login
     * @param string $_password User password 
     * @return multitype: User informatins (name, firstname, login, email)
     */
    public function getUser($_login, $_password) {
        $user_dn = $this->grr_opensession($_login, $_password);
        return $this->grr_getinfo_ldap($user_dn, $_login, $_password);
    }

    /**
     * Open LDAP session (adapted from GRR)
     * @param string $_login User login
     * @param string $_password User password
     * @param string $_user_ext_authentifie
     * @param array $tab_login
     * @param array $tab_groups
     * @return multitype: User informatins (name, firstname, login, email)
     */
    private function grr_opensession($_login, $_password, $_user_ext_authentifie = '', $tab_login = array(), $tab_groups = array()) {
        // Initialisation de $auth_ldap
        $auth_ldap = 'no';
        // Initialisation de $auth_imap
        $auth_imap = 'no';
        // Initialisation de $est_authentifie_sso
        $est_authentifie_sso = FALSE;

        // On traite le cas NON SSO
        // -> LDAP sans SSO
        // -> Imap
        $passwd_md5 = md5($_password);
        if (@function_exists("ldap_connect")) {
            $login_search = preg_replace("/[^\-@._[:space:]a-zA-Z0-9]/", "", $_login);
            if ($login_search != $_login){
                return "6";
            }
            $user_dn = $this->grr_verif_ldap($_login, $_password);

            if ($user_dn == "error_1") {
                return "7";
            } else if ($user_dn == "error_2") {
                return "8";
            } else if ($user_dn == "error_3") {
                return "9";
            } else if ($user_dn) {
                $auth_ldap = 'yes';
                return $user_dn;
            } else {
                return "4";
            }
        }
    }

    /**
     * Check if a user can connect with LDAP
     * @param string $_login User login
     * @param string $_password User password
     * @return boolean 
     */
    private function grr_verif_ldap($_login, $_password) {
        global $ldap_filter;
        if ($_password == '') {
            echo "password empty";
            return false;
        }

        $ldap_adresse = CoreLdapConfiguration::get("ldap_host");
        $ldap_port = CoreLdapConfiguration::get("ldap_port", 389);
        $ldap_login = CoreLdapConfiguration::get("ldap_user");
        $ldap_pwd = CoreLdapConfiguration::get("ldap_password");
        $ldap_base = CoreLdapConfiguration::get("ldap_search_dn");
        $use_tls = CoreLdapConfiguration::get("ldap_tls", false);

        $ldap_filter = "";

        $ds = $this->grr_connect_ldap($ldap_adresse, $ldap_port, $ldap_login, $ldap_pwd, $use_tls);

        // Test with login and password of the user
        if (!$ds) {
            $ds = $this->grr_connect_ldap($ldap_adresse, $ldap_port, $_login, $_password, $use_tls);
        }
        if ($ds) {

            //$modelCoreconfig = new CoreConfig();
            // Attributs testés pour egalite avec le login
            $atts = explode("|", CoreLdapConfiguration::get('ldap_search_attr', "uid"));
            $login_search = preg_replace("/[^\-@._[:space:]a-zA-Z0-9]/", "", $_login);
            // Tenter une recherche pour essayer de retrouver le DN
            reset($atts);
            foreach ($atts as $att) {
                $dn = $this->grr_ldap_search_user($ds, $ldap_base, $att, $login_search, $ldap_filter);
                if (($dn == "error_1") || ( $dn == "error_2") || ( $dn == "error_3")){
                    return $dn;
                }
                else if ($dn) {
                    Configuration::getLogger()->debug("[ldap] search user", ["dn" => $dn]);
                    // on a le dn
                    if (ldap_bind($ds, $dn, $_password)) {
                        Configuration::getLogger()->debug('[ldap] user bind ok', ["dn" => $dn]);
                        @ldap_unbind($ds);
                        return $dn;
                    } else {
                        Configuration::getLogger()->debug('[ldap] user bind failure', ["dn" => $dn]);
                    }
                }
            }
            // Si echec, essayer de deviner le DN, dans le cas où il n'y a pas de filtre supplémentaires
            reset($atts);
            if (!isset($ldap_filter) || ( $ldap_filter = "")) {
                foreach ($atts as $att) {
                //while (list (, $att ) = each($atts)) {
                    $dn = $att . "=" . $login_search . "," . $ldap_base;
                    Configuration::getLogger()->debug('[ldap] try to bind user', ["dn" => $dn]);
                    if (@ldap_bind($ds, $dn, $_password)) {
                        Configuration::getLogger()->debug('[ldap] user bind ok', ["dn" => $dn]);
                        @ldap_unbind($ds);
                        return $dn;
                    } else {
                        Configuration::getLogger()->debug('[ldap] user bind failure', ["dn" => $dn]);
                    }
                }
            }
            return false;
        } else{
            return false;
        }
    }

    /**
     * LDAP bind
     * @param string $l_adresse LDAP adress
     * @param string $l_port Connection port
     * @param string $l_login User login
     * @param string $l_pwd User password
     * @param boolean $use_tls
     * @param string $msg_error
     * @return mixed|boolean error message or true/false if $msg_error=="no"
     */
    private function grr_connect_ldap($l_adresse, $l_port, $l_login, $l_pwd, $use_tls, $msg_error = "no") {
        

        $ldap_dsn = "ldap://$l_adresse:$l_port";
        if($use_tls) {
            $ldap_dsn = "ldaps://$l_adresse:$l_port";
        }
        Configuration::getLogger()->debug('[ldap][grr_connect_ldap]', ['dsn' => $ldap_dsn]);
        $ds = ldap_connect($ldap_dsn);
        if ($ds) {
            
            // On dit qu'on utilise LDAP V3, sinon la V2 par défaut est utilisé et le bind ne passe pas.
            if (!(ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3))) {
                if ($msg_error != "no") {
                    return "error_1";
                }
                return false;
            }
            
            // Option LDAP_OPT_REFERRALS à désactiver dans le cas d'active directory
            @ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
            if ($use_tls && !@ldap_start_tls($ds)) {
                    if ($msg_error != "no") {
                        return "error_2";
                    }
                    return false;
            }
            
            // Accès non anonyme
            if ($l_login != '') {
                Configuration::getLogger()->debug('[ldap] user bind');
                // On tente un bind
                $b = ldap_bind($ds, $l_login, $l_pwd);
            } else {
                Configuration::getLogger()->debug('[ldap] anon bind');
                // Accès anonyme
                $b = ldap_bind($ds);
            }
            Configuration::getLogger()->debug('[ldap] bind ok');
            
            
            if ($b) {
                return $ds;
            } else {
                if ($msg_error != "no") {
                    return "error_3";
                }
                return false;
            }
        } else {
            if ($msg_error != "no") {
                return "error_4";
            }
            return false;
        }
    }

    /**
     * Search if a user exists in LDAP (adapted from GRR)
     * @param mixed $ds
     * @param string $basedn
     * @param string $login_attr
     * @param string $login
     * @param string $filtre_sup
     * @param string $diagnostic
     * @return string|boolean
     */
    private function grr_ldap_search_user($ds, $basedn, $login_attr, $login, $filtre_sup = "", $diagnostic = "no") {
        

        // Construction du filtre
        $filter = "(" . $login_attr . "=" . $login . ")";
        if (!empty($filtre_sup)) {
            $filter = "(& " . $filter . $filtre_sup . ")";
        }
        Configuration::getLogger()->debug('[ldap][grr_ldap_search_user]', ['filter' => $filter, 'dn' => $basedn]);
        $res = @ldap_search($ds, $basedn, $filter, array(
                    "dn",
                    $login_attr
                        ), 0, 0);
        
        if ($res) {
            Configuration::getLogger()->debug('[ldap][grr_ldap_search_user] geet entries');
            $info = @ldap_get_entries($ds, $res);
            if ((!is_array($info)) || ( $info ['count'] == 0)) {
                return false;
            } else if ($info ['count'] > 1) {
                // Si plusieurs entrées, on accepte uniquement en mode diagnostic
                    return false;
            } else {
                return $info [0] ['dn'];
            }
        } else {
            Configuration::getLogger()->debug('[ldap][grr_ldap_search_user] failed');
            return false;
        }
    }

    /**
     * Get the user informations from the LDAP
     * @param string $_dn
     * @param string $_login
     * @param string $_password
     * @return string|multitype: User informations or error message
     */
    private function grr_getinfo_ldap($_dn, $_login, $_password) {
        Configuration::getLogger()->debug('[ldap][grr_getinfo_ldap]');

        $m_setting_ldap_champ_nom = CoreLdapConfiguration::get('ldap_name_attr', 'sn');
        $m_setting_ldap_champ_prenom = CoreLdapConfiguration::get('ldap_firstname_attr', 'givenname');
        $m_setting_ldap_champ_email = CoreLdapConfiguration::get('ldap_mail_attr', 'email');

        $ldap_adresse = CoreLdapConfiguration::get("ldap_host");
        $ldap_port = CoreLdapConfiguration::get("ldap_port", 389);
        $ldap_login = CoreLdapConfiguration::get("ldap_user");
        $ldap_pwd = CoreLdapConfiguration::get("ldap_password");
        $use_tls = CoreLdapConfiguration::get("ldap_tls", false);

        // Lire les infos sur l'utilisateur depuis LDAP
        // Connexion à l'annuaire
        $ds = $this->grr_connect_ldap($ldap_adresse, $ldap_port, $ldap_login, $ldap_pwd, $use_tls);
        // Test with login and password of the user
        if (!$ds) {
            $ds = $this->grr_connect_ldap($ldap_adresse, $ldap_port, $_login, $_password, $use_tls);
        }
        $result = false;
        if ($ds) {
            Configuration::getLogger()->debug('[ldap][get user info]', ["dn" => $_dn, "fields" => array(
                $m_setting_ldap_champ_nom,
                $m_setting_ldap_champ_prenom,
                $m_setting_ldap_champ_email
            )]);
            $result = @ldap_read($ds, $_dn, "objectClass=*", array(
                        $m_setting_ldap_champ_nom,
                        $m_setting_ldap_champ_prenom,
                        $m_setting_ldap_champ_email
            ));
        }

        if (!$result) {
            Configuration::getLogger()->debug('[ldap][get user info] failed to read ldap fields');
            return "error";
        }
        // Recuperer les donnees de l'utilisateur
        $info = @ldap_get_entries($ds, $result);
        if (!is_array($info)) {
            return "error";
        }
        Configuration::getLogger()->debug('[ldap][user info]', ['fields' => $info]);
        for ($i = 0; $i < $info ["count"]; $i ++) {
            $val = $info [$i];
            if (is_array($val)) {
                $l_nom = ucfirst($val [$m_setting_ldap_champ_nom] [0]);
                $l_prenom = ucfirst($val [$m_setting_ldap_champ_prenom] [0]);
                $l_email = $val [$m_setting_ldap_champ_email] [0];
            }
        }

        return array(
            "name" => $l_nom,
            "firstname" => $l_prenom,
            "mail" => $l_email
        );
    }

}
