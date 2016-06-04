<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class EcosystemTranslator {

    public static function ecosystemConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Ecosystem permet de gérer les sites, utilisateurs et affiliations";
        }
        return "The Ecosystem module allows to manage sites, users and belongings";
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

    public static function Ec_configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Ecosystem\"";
        }
        return "Ecosystem configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Ecosystem'. 
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Core mudule, click \"Install\". This will create the
				Ecosystem tables in the database if they don't exists ";
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
            return "Order d'afichage";
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

    public static function LoginAlreadyExists($lang) {
        if ($lang == "fr") {
            return "Le login est déjà pris";
        }
        return "The login already exists";
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

    public static function Sites($lang) {
        if ($lang == "fr") {
            return "Sites";
        }
        return "Sites";
    }

    public static function Site($lang) {
        if ($lang == "fr") {
            return "Site";
        }
        return "Site";
    }

    public static function Edit_Site($lang) {
        if ($lang == "fr") {
            return "Editer un site";
        }
        return "Edit a site";
    }

    public static function enable_for_super_admin($lang) {
        if ($lang == "fr") {
            return "activé pour les super-administrateurs";
        }
        return "enabled for super admin";
    }

    public static function Site_managers($lang) {
        if ($lang == "fr") {
            return "Gestionnaires du site";
        }
        return "Site managers";
    }

    public static function Visitor($lang) {
        if ($lang == "fr") {
            return "Visiteur";
        }
        return "Visitor";
    }

    public static function Manager($lang) {
        if ($lang == "fr") {
            return "Gestionaire";
        }
        return "Manager";
    }

    public static function Remove($lang) {
        if ($lang == "fr") {
            return "Enlever";
        }
        return "Remove";
    }

    public static function Managers_for_site($lang) {
        if ($lang == "fr") {
            return "Gestionnaires du site";
        }
        return "Managers for site";
    }

    public static function Siteadminchangemessage($lang) {
        if ($lang == "fr") {
            return "La liste des gestionnaires du site a bien été modifiée";
        }
        return "The site managers list has been modified";
    }

    public static function Admins($lang) {
        if ($lang == "fr") {
            return "Administrateurs";
        }
        return "Admins";
    }

    public static function Add_site($lang) {
        if ($lang == "fr") {
            return "Ajouter site";
        }
        return "Add site";
    }
}
