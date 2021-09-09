<?php

/**
 * Class to translate the core views
 * 
 * @author sprigent
 *
 */
class CoreTranslator {

    public static function dateToEn($date, $lang) {
        if($date == null) {
            return "";
        }
        //echo "to translate = " . $date . "<br/>";
        if ($lang == "fr" || str_contains($date, "/")) {
            $dateArray = explode("/", $date);
            if (count($dateArray) == 3) {
                //print_r($dateArray);
                $day = $dateArray[0];
                $month = $dateArray[1];
                $year = $dateArray[2];
                //echo "translated = " . $year . "-" . $month . "-" . $day . "<br/>";
                return $year . "-" . $month . "-" . $day;
            }
            return "";
        }
        // En
        return $date;
    }

    public static function dateFromEn($date, $lang) {
        if (!$date) {
            return "";
        }

        if ($lang == "fr") {
            $dateArray = explode("-", $date);
            if (count($dateArray) == 3) {
                $day = $dateArray[2];
                $month = $dateArray[1];
                $year = $dateArray[0];
                return $day . "/" . $month . "/" . $year;
            }
            return $date;
        }
        // En
        return $date;
    }

    public static function SQL_configuration($lang) {
        if ($lang == "fr") {
            return "Configuration SQL";
        }
        return "SQL configuration";
    }

    public static function this_will_edit_the_configuration_file($lang) {
        if ($lang == "fr") {
            return "Cela va modifier le fichier de configuration Config/conf.ini (vérifier "
                    . "que www-data peut écrire dans ce fichier avant de valider)";
        }
        return "This will edit the configuration file Config/conf.ini (make sure the file is writable by www-data)";
    }

    public static function sql_host($lang) {
        if ($lang == "fr") {
            return "adresse base sql";
        }
        return "sql host";
    }

    public static function db_name($lang) {
        if ($lang == "fr") {
            return "nom base de données";
        }
        return "database name";
    }

    public static function Next($lang) {
        if ($lang == "fr") {
            return "Suivant";
        }
        return "Next";
    }

    public static function Home($lang = "") {
        if ($lang == "fr") {
            return "Accueil";
        }
        return "Home";
    }

    public static function Tools($lang = "") {
        if ($lang == "fr") {
            return "Outils";
        }
        return "Tools";
    }

    public static function Admin($lang = "") {
        if ($lang == "fr") {
            return "Administration";
        }
        return "Admin";
    }

    public static function My_Account($lang = "") {
        if ($lang == "fr") {
            return "Mon compte";
        }
        return "My Account";
    }
    
    public static function AccountCreatedSubject($spaceName = "", $lang = "") {
        $str = ($spaceName !== "") ? "[pfm: " . $spaceName . "] " : "";
        if ($lang == "fr") {
            return $str . "Compte utilisateur créé";
        }
        return $str . "User account created";
    }

    public static function AccountPendingCreationSubject($lang) {
        $str = "[pfm] ";
        if ($lang == "fr") {
            return $str . "Compte utilisateur en attente de confirmation";
        }
        return $str . "User account pending confirmation";
    }

    public static function AccountPendingCreationEmail($lang, $jwt, $url) {
        $confirmUrl = $url."/corecreateaccountconfirm?token=".$jwt;
        if($lang == "fr") {
            return "Merci de confirmer votre inscription en allant sur le lien suivant.\n".$confirmUrl;
        }
        return "Please confirm your registration at the following link.\n".$confirmUrl;
    }

    public static function WaitingAccountMessage($lang) {
        if($lang == "fr") {
            return "Un mail a été envoyé avec un lien pour confirmer votre inscription";
        }
        return "An email has been sent with a link to confirm your registration";
    }

    public static function Settings($lang = "") {
        if ($lang == "fr") {
            return "Préférences";
        }
        return "Settings";
    }

    public static function logout($lang = "") {
        if ($lang == "fr") {
            return "Déconnexion";
        }
        return "logout";
    }

    public static function MenuItem($item, $lang = "") {
        if ($lang == "fr") {
            if ($item == "booking") {
                return "calendrier";
            }
            if ($item == "users/institutions") {
                return "utilisateurs";
            }
            if ($item == "supplies") {
                return "consommables";
            }
            if ($item == "sprojects") {
                return "projets";
            }
            if ($item == "quotes") {
                return "devis";
            }
            if ($item == "petshop") {
                return "animalerie";
            }
        }
        return $item;
    }

    public static function Change_password($lang = "") {
        if ($lang == "fr") {
            return "Modifier le mot de passe";
        }
        return "Change password";
    }

    public static function Unable_to_change_the_password($lang) {
        if ($lang == "fr") {
            return "Impossible de modifier le mot de passe";
        }
        return "Unable to change the password";
    }

    public static function The_password_has_been_successfully_updated($lang) {
        if ($lang == "fr") {
            return "Le mot de passe a été mis à jour";
        }
        return "The password has been successfully updated!";
    }

    public static function Ok($lang) {
        if ($lang == "fr") {
            return "Ok";
        }
        return "Ok";
    }

    public static function Add_User($lang) {
        if ($lang == "fr") {
            return "Ajouter utilisateur";
        }
        return "Add User";
    }

    public static function Name($lang) {
        if ($lang == "fr") {
            return "Nom";
        }
        return "Name";
    }

    public static function Firstname($lang) {
        if ($lang == "fr") {
            return "Prénom";
        }
        return "Firstname";
    }

    public static function Login($lang) {
        if ($lang == "fr") {
            return "Identifiant";
        }
        return "Login";
    }

    public static function Password($lang) {
        if ($lang == "fr") {
            return "Mot de passe";
        }
        return "Password";
    }

    public static function Confirm($lang) {
        if ($lang == "fr") {
            return "Confirmer";
        }
        return "Confirm";
    }

    public static function Email($lang) {
        if ($lang == "fr") {
            return "Courriel";
        }
        return "Email";
    }

    public static function Phone($lang) {
        if ($lang == "fr") {
            return "Téléphone";
        }
        return "Phone";
    }

    public static function Responsible($lang) {
        if ($lang == "fr") {
            return "Responsable";
        }
        return "Person in charge";
    }

    public static function is_responsible($lang) {
        if ($lang == "fr") {
            return "est responsable";
        }
        return "is in charge";
    }

    public static function Status($lang) {
        if ($lang == "fr") {
            return "Statut";
        }
        return "Status";
    }

    public static function Convention($lang) {
        if ($lang == "fr") {
            return "Charte";
        }
        return "Convention";
    }

    public static function ConventionDownload($lang) {
        if ($lang == "fr") {
            return "Télécharger charte";
        }
        return "Convention download";
    }
    
    
    public static function Date_convention($lang) {
        if ($lang == "fr") {
            return "Charte signée le";
        }
        return "Convention signed on";
    }

    public static function Date_end_contract($lang) {
        if ($lang == "fr") {
            return "Date de fin de contrat";
        }
        return "Date end contract";
    }

    public static function Save($lang) {
        if ($lang == "fr") {
            return "Valider";
        }
        return "Save";
    }

    public static function Reject($lang) {
        if ($lang == "fr") {
            return "Rejeter";
        }
        return "Reject";
    }

    public static function Cancel($lang) {
        if ($lang == "fr") {
            return "Annuler";
        }
        return "Cancel";
    }

    public static function Unable_to_add_the_user($lang) {
        if ($lang == "fr") {
            return "Impossible d'ajouter l'utilisateur";
        }
        return "Unable to add the user";
    }

    public static function The_user_had_been_successfully_added($lang) {
        if ($lang == "fr") {
            return "L'utilisateur a été ajouté !";
        }
        return "The user had been successfully added!";
    }

    public static function for_user($lang) {
        if ($lang == "fr") {
            return "pour l'utilisateur";
        }
        return "for user";
    }

    public static function Is_user_active($lang) {
        if ($lang == "fr") {
            return "est actif";
        }
        return "Is user active";
    }

    public static function yes($lang) {
        if ($lang == "fr") {
            return "oui";
        }
        return "yes";
    }

    public static function no($lang) {
        if ($lang == "fr") {
            return "non";
        }
        return "no";
    }

    public static function Edit_User($lang) {
        if ($lang == "fr") {
            return "Editer utilisateur";
        }
        return "Edit User";
    }

    public static function Unable_to_update_the_user($lang) {
        if ($lang == "fr") {
            return "Impossible de mettre à jour l'utilisateur ";
        }
        return "Unable to update the user";
    }

    public static function The_user_had_been_successfully_updated($lang) {
        if ($lang == "fr") {
            return "L'utilisateur a été mis à jour";
        }
        return "The user has been successfully updated";
    }

    public static function User_from($lang) {
        if ($lang == "fr") {
            return "date inscription";
        }
        return "User from";
    }

    public static function Last_connection($lang) {
        if ($lang == "fr") {
            return "dernière connexion";
        }
        return "Last connection";
    }

    public static function Edit($lang) {
        if ($lang == "fr") {
            return "Editer";
        }
        return "Edit";
    }

    public static function Manage_account($lang) {
        if ($lang == "fr") {
            return "Editer compte";
        }
        return "Manage account";
    }

    public static function Curent_password($lang) {
        if ($lang == "fr") {
            return "Mot de passe actuel";
        }
        return "Current password";
    }

    public static function New_password($lang) {
        if ($lang == "fr") {
            return "Nouveau mot de passe";
        }
        return "New password";
    }

    public static function Unable_to_update_the_account($lang) {
        if ($lang == "fr") {
            return "Impossible de mettre à jour le compte !";
        }
        return "Unable to update the account!";
    }

    public static function The_account_has_been_successfully_updated($lang) {
        if ($lang == "fr") {
            return "Le compte a été mis à jour !";
        }
        return "The account has been successfully updated!";
    }

    public static function Users_Institutions($lang) {
        if ($lang == "fr") {
            return "Utilisateurs/Unités";
        }
        return "Users/Institutions";
    }

    public static function Units($lang) {
        if ($lang == "fr") {
            return "Unités";
        }
        return "Units";
    }

    public static function Add_unit($lang) {
        if ($lang == "fr") {
            return "Ajouter Unité";
        }
        return "Add unit";
    }

    public static function Active_Users($lang) {
        if ($lang == "fr") {
            return "Utilisateurs actifs";
        }
        return "Active Users";
    }

    public static function Unactive_Users($lang) {
        if ($lang == "fr") {
            return "Utilisateurs inactifs";
        }
        return "Inactive Users";
    }

    public static function Address($lang = "") {
        if ($lang == "fr") {
            return "Adresse";
        }
        return "Address";
    }

    public static function User($lang = "") {
        if ($lang == "fr") {
            return "Utilisateur";
        }
        return "User";
    }

    public static function Users($lang = "") {
        if ($lang == "fr") {
            return "Utilisateurs";
        }
        return "Users";
    }

    public static function Unit($lang = "") {
        if ($lang == "fr") {
            return "Unité";
        }
        return "Unit";
    }

    public static function Add($lang = "") {
        if ($lang == "fr") {
            return "Ajouter";
        }
        return "Add";
    }

    public static function Edit_Unit($lang = "") {
        if ($lang == "fr") {
            return "Modifier unité";
        }
        return "Edit Unit";
    }

    public static function User_Settings($lang = "") {
        if ($lang == "fr") {
            return "Préférences utilisateur";
        }
        return "User Settings";
    }

    public static function Projects($lang = "") {
        if ($lang == "fr") {
            return "Projets";
        }
        return "Projects";
    }

    public static function Add_project($lang = "") {
        if ($lang == "fr") {
            return "Ajouter projet";
        }
        return "Add project";
    }

    public static function Description($lang = "") {
        if ($lang == "fr") {
            return "Description";
        }
        return "Description";
    }

    public static function Edit_Project($lang = "") {
        if ($lang == "fr") {
            return "Editer Projet";
        }
        return "Edit Project";
    }

    public static function Open($lang = "") {
        if ($lang == "fr") {
            return "Ouvert";
        }
        return "Open";
    }

    public static function Close($lang = "") {
        if ($lang == "fr") {
            return "Fermé";
        }
        return "Close";
    }

    public static function Modules_configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration des modules";
        }
        return "Modules configuration";
    }

    public static function Config($lang = "") {
        if ($lang == "fr") {
            return "Config";
        }
        return "Config";
    }

    public static function Language($lang = "") {
        if ($lang == "fr") {
            return "Langue";
        }
        return "Language";
    }

    public static function Home_page($lang = "") {
        if ($lang == "fr") {
            return "Page d'accueil";
        }
        return "Home page";
    }

    public static function Database($lang = "") {
        if ($lang == "fr") {
            return "Base de données";
        }
        return "Database";
    }

    public static function Contact_the_administrator($lang = "") {
        if ($lang == "fr") {
            return "Contacter l'administrateur";
        }
        return "Contact the administrator";
    }

    public static function CoreConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Core permet de gérer les paramètres de 
					l'application et la base de données utilisateurs";
        }
        return "The Core module allows to manage the application
				settings and a user database";
    }

    public static function Core_configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration du \"Core\"";
        }
        return "Core configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de Core. 
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Core mudule, click \"Install\". This will create the
				Core tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function disable($lang = "") {
        if ($lang == "fr") {
            return "désactivé";
        }
        return "disabled";
    }

    public static function enable_for_visitors($lang = "") {
        if ($lang == "fr") {
            return "activé pour les visiteurs";
        }
        return "enabled for visitors";
    }

    public static function enable_for_users($lang = "") {
        if ($lang == "fr") {
            return "activé pour les utlisateurs";
        }
        return "enabled for users";
    }

    public static function enable_for_manager($lang = "") {
        if ($lang == "fr") {
            return "activé pour les gestionaires";
        }
        return "enabled for manager";
    }

    public static function enable_for_admin($lang = "") {
        if ($lang == "fr") {
            return "activé pour les administrateurs";
        }
        return "enabled for admin";
    }

    public static function non_active_users($lang = "") {
        if ($lang == "fr") {
            return "Désactivation des utilisateurs";
        }
        return "Deactivate users";
    }

    public static function Disable_user_account_when($lang = "") {
        if ($lang == "fr") {
            return "désactiver un compte utilisateur lorsque";
        }
        return "Deactivate user account when";
    }

    public static function never($lang = "") {
        if ($lang == "fr") {
            return "jamais";
        }
        return "never";
    }

    public static function contract_ends($lang = "") {
        if ($lang == "fr") {
            return "fin de contrat";
        }
        return "contract ends";
    }

    public static function contract_ends_or_does_not_login_for_1_year($lang = "") {
        if ($lang == "fr") {
            return "fin de contrat ou ne s'est pas connecté depuis 1 années";
        }
        return "contract ends or did not connect since 1 years ";
    }

    public static function does_not_login_for_n_year($n, $lang = "") {
        if ($lang == "fr") {
            if ($n > 1) {
                return "ne s'est pas connecté depuis " . $n . " années";
            }
            return "ne s'est pas connecté depuis " . $n . " année";
        }
        if ($n > 1) {
            return "did not connect since " . $n . " years ";
        }
        return "did not connect since " . $n . " year ";
    }

    public static function Backup($lang = "") {
        if ($lang == "fr") {
            return "Sauvegarde";
        }
        return "Backup";
    }

    public static function Run_backup($lang = "") {
        if ($lang == "fr") {
            return "Lancer sauvegarde";
        }
        return "Run backup";
    }

    public static function Install($lang) {
        if ($lang == "fr") {
            return "Installer";
        }
        return "Install";
    }

    public static function Not_signed($lang) {
        if ($lang == "fr") {
            return "Non signée";
        }
        return "Not signed";
    }

    public static function Signed_the($lang) {
        if ($lang == "fr") {
            return "Signée le";
        }
        return "Signed on";
    }

    public static function Export($lang) {
        if ($lang == "fr") {
            return "Exporter";
        }
        return "Export";
    }

    public static function ExportResponsibles($lang) {
        if ($lang == "fr") {
            return "Exporter Responsables";
        }
        return "Export Persons in charge";
    }

    public static function All($lang) {
        if ($lang == "fr") {
            return "Tous";
        }
        return "All";
    }

    public static function Active($lang) {
        if ($lang == "fr") {
            return "Actifs";
        }
        return "Active";
    }

    public static function Unactive($lang) {
        if ($lang == "fr") {
            return "Inactifs";
        }
        return "Inactive";
    }

    public static function Search($lang) {
        if ($lang == "fr") {
            return "Rechercher";
        }
        return "Search";
    }

    public static function Delete_User($lang) {
        if ($lang == "fr") {
            return "Supprimer utilisateur";
        }
        return "Delete user";
    }

    public static function Delete_Unit($lang) {
        if ($lang == "fr") {
            return "Supprimer unité";
        }
        return "Delete unit";
    }

    public static function Delete($lang) {
        if ($lang == "fr") {
            return "Supprimer";
        }
        return "Delete";
    }

    public static function Delete_User_Warning($lang, $userName) {
        if ($lang == "fr") {
            return "Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur: " . $userName . " ?" .
                    "<br> Attention: Cela supprimera uniquement l'utilisateur de la base de données. Toute référence faite
				    à cet utilisateur dans un autre module sera corrompue.";
        }
        return "Delete user: " . $userName . " ?" .
                "<br> Warning: This will remove the user from the database. Any reference to this user in another module will be corrupted";
    }

    public static function Delete_Unit_Warning($lang, $unitName) {
        if ($lang == "fr") {
            return "Êtes-vous sûr de vouloir supprimer définitivement l'unité: " . $unitName . " ?" .
                    "<br> Attention: Cela supprimera uniquement l'unité de la base de données. Toute référence faite
				    à cet unité dans un autre module sera corrompue.";
        }
        return "Delete unit: " . $unitName . " ?" .
                "<br> Warning: This will remove the unit from the database. Any reference to this unit in another module will be corrupted";
    }

    public static function The_user_has_been_deleted($lang) {
        if ($lang == "fr") {
            return "L'utilisateur a été supprimé";
        }
        return "The user has been deleted";
    }

    public static function Translate_status($lang, $status) {
        if ($lang == "fr") {
            if ($status == "visitor") {
                return "visiteur";
            } else if ($status == "user") {
                return "utilisateur";
            } else if ($status == "manager") {
                return "gestionnaire";
            } else if ($status == "admin") {
                return "administrateur";
            }
            return $status;
        }
        return $status;
    }

    public static function Translate_status_from_id($lang, $id_status) {
        if ($lang == "fr") {
            if ($id_status == 1) {
                return "visiteur";
            } else if ($id_status == 2) {
                return "utilisateur";
            } else if ($id_status == 3) {
                return "gestionnaire";
            } else if ($id_status == 4) {
                return "administrateur";
            }
            return "rôle introuvable";
        } else if ($lang == "fr") {
            if ($id_status == 1) {
                return "visitor";
            } else if ($id_status == 2) {
                return "user";
            } else if ($id_status == 3) {
                return "manager";
            } else if ($id_status == 4) {
                return "administrator";
            }
            return "cannot find the role";
        }
        return $id_status;
    }

    public static function LdapConfig($lang) {
        if ($lang == "fr") {
            return "Configuration de LDAP";
        }
        return "LDAP confguration";
    }

    public static function UseLdap($lang) {
        if ($lang == "fr") {
            return "Utiliser LDAP";
        }
        return "Use LDAP";
    }

    public static function userDefaultStatus($lang) {
        if ($lang == "fr") {
            return "Statut par défaut d'un nouvel utilisateur";
        }
        return "User default status";
    }

    public static function ldapName($lang) {
        if ($lang == "fr") {
            return "Attribut LDAP nom";
        }
        return "LDAP attribute name";
    }

    public static function ldapFirstname($lang) {
        if ($lang == "fr") {
            return "Attribut LDAP prénom";
        }
        return "LDAP attribute firstname";
    }

    public static function ldapMail($lang) {
        if ($lang == "fr") {
            return "Attribut LDAP email";
        }
        return "LDAP attribute email";
    }

    public static function ldapSearch($lang) {
        if ($lang == "fr") {
            return "Attribut LDAP attribut de recherche";
        }
        return "LDAP attribute for user search";
    }

    public static function LdapAccess($lang) {
        if ($lang == "fr") {
            return "Accès au LDAP";
        }
        return "LDAP access";
    }

    public static function ldapAdress($lang) {
        if ($lang == "fr") {
            return "Adresse du LDAP";
        }
        return "LDAP address";
    }

    public static function ldapPort($lang) {
        if ($lang == "fr") {
            return "Numéro de port";
        }
        return "Port number";
    }

    public static function ldapId($lang) {
        if ($lang == "fr") {
            return "Identifiant de connexion (si anonyme non autorisé)";
        }
        return "Connection ID (if anonymous impossible)";
    }

    public static function ldapPwd($lang) {
        if ($lang == "fr") {
            return "Mot de passe connexion (si anonyme non autorisé)";
        }
        return "Connection password (if anonymous impossible)";
    }

    public static function ldapBaseDN($lang) {
        if ($lang == "fr") {
            return "Base DN";
        }
        return "Base DN";
    }

    public static function HomeConfig($lang) {
        if ($lang == "fr") {
            return "Informations page de connexion";
        }
        return "Connection page informations";
    }

    public static function title($lang) {
        if ($lang == "fr") {
            return "Titre";
        }
        return "Title";
    }

    public static function logo($lang) {
        if ($lang == "fr") {
            return "Logo";
        }
        return "Logo";
    }

    public static function menu_color($lang) {
        if ($lang == "fr") {
            return "Couleur du menu";
        }
        return "Menu color";
    }

    public static function color($lang) {
        if ($lang == "fr") {
            return "Couleur";
        }
        return "Color";
    }

    public static function text_color($lang) {
        if ($lang == "fr") {
            return "Couleur du text";
        }
        return "Text color";
    }

    public static function Belonging($lang) {
        if ($lang == "fr") {
            return "Appartenance";
        }
        return "Belonging";
    }

    public static function Belongings($lang) {
        if ($lang == "fr") {
            return "Appartenances";
        }
        return "Belongings";
    }

    public static function add_belonging($lang) {
        if ($lang == "fr") {
            return "Ajouter";
        }
        return "Add belonging";
    }

    public static function Edit_belonging($lang) {
        if ($lang == "fr") {
            return "Modifier appartenance";
        }
        return "Edit belonging";
    }

    public static function Source($lang) {
        if ($lang == "fr") {
            return "Source";
        }
        return "Source";
    }

    public static function User_list_options($lang) {
        if ($lang == "fr") {
            return "Champs optionnels table utilisateurs";
        }
        return "User list options";
    }

    public static function Display_order($lang) {
        if ($lang == "fr") {
            return "Ordre d'affichage";
        }
        return "Display order";
    }

    public static function Authorizations($lang) {
        if ($lang == "fr") {
            return "Autorisations";
        }
        return "Authorizations";
    }

    public static function type($lang) {
        if ($lang == "fr") {
            return "type";
        }
        return "type";
    }

    public static function Academic($lang) {
        if ($lang == "fr") {
            return "Académique";
        }
        return "Academic";
    }

    public static function Company($lang) {
        if ($lang == "fr") {
            return "Privé";
        }
        return "Private";
    }

    public static function AlreadyExists($elem , $lang) {
        if ($lang == "fr") {
            return "Un compte avec " . $elem . " existe déjà";
        }
        return "An account with " . $elem . " already exists";
    }

    public static function LoginAlreadyExists($lang) {
        if ($lang == "fr") {
            return "Le login est déjà pris";
        }
        return "Login already exists";
    }

    public static function EmailAlreadyExists($lang) {
        if ($lang == "fr") {
            return "Un compte existe déjà avec cette adresse mail";
        }
        return "An account with this email address already exists";
    }

    public static function Maintenance_Mode($lang) {
        if ($lang == "fr") {
            return "Mode maintenance";
        }
        return "Maintenance mode";
    }

    public static function InMaintenance($lang) {
        if ($lang == "fr") {
            return "En maintenance";
        }
        return "Maintenance on";
    }

    public static function MaintenanceMessage($lang) {
        if ($lang == "fr") {
            return "Message";
        }
        return "Message";
    }

    public static function Management($lang) {
        if ($lang == "fr") {
            return "Gestion";
        }
        return "Management";
    }

    public static function Managers($lang) {
        if ($lang == "fr") {
            return "Gestionnaires";
        }
        return "Managers";
    }

    public static function UnixDate($unitTime, $lang) {
        if ($lang == "fr") {
            return date("d/m/Y \à H:i", $unitTime);
        }
        return date("Y-m-d  H:i", $unitTime);
    }

    public static function ConnectionPageData($lang) {
        if ($lang == "fr") {
            return "Informations page de connexion";
        }
        return "Connection page data";
    }

    public static function Carousel($lang) {
        if ($lang == "fr") {
            return "Carousel";
        }
        return "Carousel";
    }

    public static function Image_Url($lang) {
        if ($lang == "fr") {
            return "Image url";
        }
        return "Image url";
    }

    public static function ViewCarousel($lang) {
        if ($lang == "fr") {
            return "Afficher le carousel";
        }
        return "View carousel";
    }

    public static function TheTwoPasswordAreDifferent($lang) {
        if ($lang == "fr") {
            return "Les deux mots de passe sont différents";
        }
        return "The two password are different";
    }

    public static function The_curent_password_is_not_correct($lang) {
        if ($lang == "fr") {
            return "Le mot de passe actuel n'est pas correct";
        }
        return "The curent password is not correct";
    }

    public static function Date($lang) {
        if ($lang == "fr") {
            return "Date";
        }
        return "Date";
    }

    public static function Background_color($lang) {
        if ($lang == "fr") {
            return "Couleur fond";
        }
        return "Background color";
    }

    public static function Background_highlight($lang) {
        if ($lang == "fr") {
            return "Couleur actif";
        }
        return "Background highlight";
    }

    public static function Text_highlight($lang) {
        if ($lang == "fr") {
            return "Text actif";
        }
        return "Text highlight";
    }

    public static function Configuration($lang) {
        if ($lang == "fr") {
            return "Configuration";
        }
        return "Configuration";
    }

    public static function Menus($lang) {
        if ($lang == "fr") {
            return "Menus";
        }
        return "Menus";
    }

    public static function Menus_saved($lang) {
        if ($lang == "fr") {
            return "Les menus ont été sauvegardés";
        }
        return "Menus have been saved";
    }

    public static function Items($lang) {
        if ($lang == "fr") {
            return "Items";
        }
        return "Items";
    }

    public static function MenuItems($lang) {
        if ($lang == "fr") {
            return "Items menu";
        }
        return "Menu items";
    }

    public static function Url($lang) {
        if ($lang == "fr") {
            return "Url";
        }
        return "Url";
    }

    public static function Icon($lang) {
        if ($lang == "fr") {
            return "Icone";
        }
        return "Icon";
    }

    public static function Menu($lang) {
        if ($lang == "fr") {
            return "Menu";
        }
        return "Menu";
    }

    public static function Spaces($lang) {
        if ($lang == "fr") {
            return "Espaces";
        }
        return "Spaces";
    }

    public static function Add_Space($lang) {
        if ($lang == "fr") {
            return "Ajouter espace";
        }
        return "Add space";
    }

    public static function Edit_space($lang) {
        if ($lang == "fr") {
            return "Modifier espace";
        }
        return "Edit space";
    }

    public static function PrivateA($lang) {
        if ($lang == "fr") {
            return "Privé";
        }
        return "Private";
    }

    public static function PublicA($lang) {
        if ($lang == "fr") {
            return "Public";
        }
        return "Public";
    }

    public static function Visitor($lang) {
        if ($lang == "fr") {
            return "Visiteur";
        }
        return "Visitor";
    }

    public static function Manager($lang) {
        if ($lang == "fr") {
            return "Gestionnaire";
        }
        return "Manager";
    }

    public static function Role($lang) {
        if ($lang == "fr") {
            return "Rôle";
        }
        return "Role";
    }

    public static function Access($lang) {
        if ($lang == "fr") {
            return "Accès";
        }
        return "Access";
    }

    public static function View_Menu($lang) {
        if ($lang == "fr") {
            return "Menu espace";
        }
        return "View menu";
    }

    public static function Neww($lang) {
        if ($lang == "fr") {
            return "Nouveau";
        }
        return "New";
    }

    public static function Inactive($lang) {
        if ($lang == "fr") {
            return "Inactif";
        }
        return "Inactive";
    }

    public static function Update($lang) {
        if ($lang == "fr") {
            return "Mise à jour";
        }
        return "Update";
    }

    public static function UpdateComment($lang) {
        if ($lang == "fr") {
            return "Mise à jour du cache du routeur et les base de données des modules";
        }
        return "This update the rooting cache and the modules database";
    }

    public static function MenuName($lang) {
        if ($lang == "fr") {
            return "Nom du menu";
        }
        return "Menu name";
    }

    public static function ExportAll($lang) {
        if ($lang == "fr") {
            return "Exporter tous";
        }
        return "Export all users";
    }

    public static function UseTLS($lang) {
        if ($lang == "fr") {
            return "Chiffrage TLS";
        }
        return "Use TLS";
    }

    public static function RememberMe($lang) {
        if ($lang == "fr") {
            return "Se souvenir de moi";
        }
        return "Remember me";
    }

    public static function Who_can_delete_users($lang) {
        if ($lang == "fr") {
            return "Qui peut supprimer des utilisateurs";
        }
        return "Who can delete users";
    }

    public static function Select($lang) {
        if ($lang == "fr") {
            return "Choix";
        }
        return "Select";
    }

    public static function AccountPasswordReset($lang) {
        if ($lang == "fr") {
            return "Réinitialisation mot de passe";
        }
        return "Account password reset";
    }

    public static function AccountPasswordResetMessage($lang) {
        if ($lang == "fr") {
            return "Vous avez demandé une réinitialisation de votre mot de passe. Votre nouveau mot de passe est ";
        }
        return "Your password has been reset as per requested. Your new password is ";
    }
    
    public static function AccountCreatedEmail($lang, $login, $pwd){
        if ($lang == "fr") {
            return "Vous avez demandé un compte sur Platform-Manager. Votre identifiant est ".$login." et votre mot de passe est " . $pwd;
        }
        return "Your asked for a new account on Platform-Manager. Your login is " . $login . " and your password is " . $pwd;
    }
    
    public static function CreatedAccountMessage($lang){
        if ($lang == "fr") {
            return "Votre compte a bien été créé et un email avec vos identifiants vous a été envoyé. Votre compte sera actif lorsqu'un-e responsable"
            . " de l'accès que vous avez demandé l'activera";
        }
        return "Your account has been created and you will receive an email with your credentials. You will be able to connect to your account when a manager of"
        . " the acces you asked for will validate your account";
    }

    public static function ExtAccountMessage($lang) {
        if ($lang == "fr") {
            return "Votre connexion à Platform-Manager est gérée par un annuaire externe. Merci de contacter"
                    . "les administrateurs pour plus d'informations";
        }
        return "Your account password is managed using an external directory. Please contact administrators"
                . " to get the procedure to change your password";
    }

    public static function ResetPasswordMessageSend($lang) {
        if ($lang == "fr") {
            return "Un couriel à été envoyé avec un nouveau mot de passe. Pensez à le modifier dans le menu"
                    . " 'mon compte'";
        }
        return "En email has been send with the new password. Please change it in 'My account' section";
    }

    public static function PasswordForgotten($lang) {
        if ($lang == "fr") {
            return "Mot de passe oublié";
        }
        return "Password forgotten";
    }

    public static function UserNotFoundWithEmail($lang) {
        if ($lang == "fr") {
            return "Aucun compte utilisateur ne correspond au couriel donné";
        }
        return "Cannot find user with the given email";
    }

    public static function Dashboard($lang) {
        if ($lang == "fr") {
            return "Tableau de bord";
        }
        return "Dashboard";
    }

    public static function ActivateCustomDashboard($lang) {
        if ($lang == "fr") {
            return "activer le tableau de bord personalisé";
        }
        return "Activate custom dashboard";
    }

    public static function Choice($lang) {
        if ($lang == "fr") {
            return "Choix";
        }
        return "Choice";
    }

    public static function Activation($lang) {
        if ($lang == "fr") {
            return "Activation";
        }
        return "Activation";
    }

    public static function Sections($lang) {
        if ($lang == "fr") {
            return "Sections";
        }
        return "Sections";
    }

    public static function NewSection($lang) {
        if ($lang == "fr") {
            return "Nouvelle section";
        }
        return "New section";
    }

    public static function EditSection($lang) {
        if ($lang == "fr") {
            return "Editer section";
        }
        return "Edit section";
    }

    public static function NewItem($lang) {
        if ($lang == "fr") {
            return "Nouvel item";
        }
        return "New item";
    }

    public static function Section($lang) {
        if ($lang == "fr") {
            return "Section";
        }
        return "Section";
    }

    public static function Width($lang) {
        if ($lang == "fr") {
            return "Largeur";
        }
        return "Width";
    }

    public static function SpaceIcons($lang) {
        if ($lang == "fr") {
            return "Icones de la page d'accueil des espaces";
        }
        return "Icons of the home page";
    }

    public static function smallIcons($lang) {
        if ($lang == "fr") {
            return "Petites icones";
        }
        return "Small icons";
    }

    public static function IconsWithDescription($lang) {
        if ($lang == "fr") {
            return "Icones avec description";
        }
        return "Icons with description";
    }

    public static function Vat($lang) {
        if ($lang == "fr") {
            return "TVA (%)";
        }
        return "Vat";
    }

    public static function MainSubMenus($lang) {
        if ($lang == "fr") {
            return "Sous menus";
        }
        return "Sub menus";
    }

    public static function MainMenus($lang) {
        if ($lang == "fr") {
            return "Menus";
        }
        return "Menus";
    }

    public static function MainMenu($lang) {
        if ($lang == "fr") {
            return "Menu";
        }
        return "Menu";
    }

    public static function NewMainMenu($lang) {
        if ($lang == "fr") {
            return "Ajouter menu";
        }
        return "New menu";
    }

    public static function NewMainSubMenu($lang) {
        if ($lang == "fr") {
            return "Ajouter sous menu";
        }
        return "New sub menu";
    }

    public static function EditMainMenu($lang) {
        if ($lang == "fr") {
            return "Edition menu";
        }
        return "Edit menu";
    }

    public static function EditMainSubMenu($lang) {
        if ($lang == "fr") {
            return "Edition sous menu";
        }
        return "Edit sub menu";
    }

    public static function MenuSaved($lang) {
        if ($lang == "fr") {
            return "Le menu a bien été sauvegardé";
        }
        return "The menu has been saved";
    }

    public static function SubMenus($lang) {
        if ($lang == "fr") {
            return "Sous menus";
        }
        return "Sub menus";
    }

    public static function SubMenu($lang) {
        if ($lang == "fr") {
            return "Sous menu";
        }
        return "Sub menu";
    }

    public static function ItemsFor($lang) {
        if ($lang == "fr") {
            return "Items pour: ";
        }
        return "Items for: ";
    }

    public static function EditItemFor($lang) {
        if ($lang == "fr") {
            return "Edition item pour: ";
        }
        return "Edit items for: ";
    }

    public static function EditItem($lang) {
        if ($lang == "fr") {
            return "Edition item";
        }
        return "Edit item";
    }

    public static function Space($lang) {
        if ($lang == "fr") {
            return "Espace";
        }
        return "Space";
    }

    public static function Item($lang) {
        if ($lang == "fr") {
            return "Item";
        }
        return "Item";
    }

    public static function Image($lang) {
        if ($lang == "fr") {
            return "Image";
        }
        return "Image";
    }

    public static function or_($lang) {
        if ($lang == "fr") {
            return "Ou";
        }
        return "Or";
    }

    public static function CreateAccount($lang) {
        if ($lang == "fr") {
            return "Créer un compte";
        }
        return "Create an account";
    }

    public static function AccessTo($lang){
        if ($lang == "fr") {
            return "Demande accès à";
        }
        return "Access to";
    }
    
    public static function Error($lang){
        if ($lang == "fr") {
            return "Erreur";
        }
        return "Error";
    }
    
    public static function PendingUsers($lang){
        if ($lang == "fr") {
            return "Attente d'activation";
        }
        return "Pending accounts";
    }
    
    public static function PendingUserAccounts($lang){
        if ($lang == "fr") {
            return "Comptes en attente d'activation";
        }
        return "Pending users accounts";
    }
    
    public static function DateCreated($lang){
        if ($lang == "fr") {
            return "Date de création";
        }
        return "Date created";
    }
    
    public static function Activate($lang){
        if ($lang == "fr") {
            return "Activer";
        }
        return "Activate";
    }
    
    public static function UserAccountHasBeenActivated($lang){
        if ($lang == "fr") {
            return "Le compte utilisateur a bien été activé";
        }
        return "User account has been activated";        
    }

    public static function UserAccountHasBeenDeleted($lang){
        if ($lang == "fr") {
            return "L'utilisateur n'a plus acces à votre espace";
        }
        return "This user has no longer access to your space";
    }

    public static function UserIsMemberOfSpace($lang){
        if ($lang == "fr") {
            return "Impossible de supprimer cet utilisateur, car il est lié ,à un espace";
        }
        return "You are not allowed to delete this user as he is member of a space";
    }
    
    public static function AccessFor($lang){
        if ($lang == "fr") {
            return "Accès pour";
        }
        return "Access for";        
    }
    
    public static function UserAccessHasBeenSaved($lang){
        if ($lang == "fr") {
            return "Les accès ont bien été enregistrés";
        }
        return "User access has been saved";        
    }
    
    public static function Download($lang){
        if ($lang == "fr") {
            return "Télécharger";
        }
        return "Download";        
    }
    
    public static function Informations($lang){
        if ($lang == "fr") {
            return "Informations";
        }
        return "Informations";        
    }   
    
    public static function AccountHasBeenCreated($lang){
        if ($lang == "fr") {
            return "Le compte a bien été créé";
        }
        return "Account has been created";        
    }

    public static function AccountHasBeenModified($lang){
        if ($lang == "fr") {
            return "Le compte a bien été modifié";
        }
        return "Account has been modified";        
    }  
    
    public static function RequestJoin($isMemberOfSpace, $lang){
        if ($lang == "fr") {
            return $isMemberOfSpace ? "Quitter" : "Rejoindre";
        }
        return $isMemberOfSpace ? "Leave" : "Join"; 
    }

    public static function JoinRequested($lang){
        if ($lang == "fr") {
            return "Demande envoyée...";
        }
        return "Join requested..."; 
    }

    public static function JoinRequestEmail($login, $spaceName, $lang){
        if ($lang == "fr") {
            return "Bonjour, <br><br>" . $login . " demande à rejoindre votre espace " . $spaceName. " sur Platform-Manager";
        }
        return "Hi, <br><br>" . $login . " requests to join your space " . $spaceName. " on Platform-Manager";
    }

    public static function JoinRequestSubject($spaceName, $lang){
        $str = ($spaceName !== "") ? "[pfm: " . $spaceName . "] " : "";
        if ($lang == "fr") {
            return $str . "Nouvelle demande d'accès à votre espace";
        }
        return $str . "New join request for your space";
    }

    public static function JoinResponseEmail($userName, $spaceName, $accepted, $lang){
        if ($lang == "fr") {
            $message = "Bonjour " . $userName . ", <br><br> votre demande à rejoindre l'espace " . $spaceName . " sur Platform-Manager ";
            if ($accepted) {
                $message = $message . " a bien été acceptée.";
            } else {
                $message = $message . " a été refusée.";
            }
            return $message;
        }
        $message = "Hi " . $userName . ", <br><br> your request to join space " . $spaceName . " on Platform-Manager ";
        if ($accepted) {
            $message = $message . " has been accepted.";
        } else {
            $message = $message . " has been rejected.";
        }
        return $message;
    }

    public static function JoinResponseSubject($spaceName, $lang){
        if ($lang == "fr") {
            return "[pfm : " . $spaceName . "] Votre demande d'accès à l'espace " . $spaceName;
        }
        return "[pfm: " . $spaceName . "] Your join request for space " . $spaceName;
    }
    public static function Contact($lang = "") {
        return "Contact";
    }

    public static function Support($lang = "") {
        if ($lang == "fr") {
            return "Email de support";
        }
        return "Support email";
    }

    public static function MailSubjectPrefix($spaceName = "") {
        return ($spaceName !== "") ? "[pfm: " . $spaceName . "] " : "";
    }

    public static function History($lang = "") {
        if ($lang == "fr") {
            return "Historique";
        }
        return "History";
    }

    public static function Default_language($lang = "") {
        if ($lang == "fr") {
            return "Langue par défaut";
        }
        return "Default language";
    }

    public static function English($lang = "") {
        if ($lang == "fr") {
            return "Anglais";
        }
        return "English";
    }

    public static function French($lang = "") {
        if ($lang == "fr") {
            return "Français";
        }
        return "French";
    }
    
}
