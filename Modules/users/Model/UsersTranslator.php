<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class UsersTranslator {

    public static function users($lang) {
        if ($lang == "fr") {
            return "users";
        }
        return "users";
    }

    public static function usersConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Users permet de gérer les comptes utilisateurs";
        }
        return "The Users module allows to manage users accounts";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Users\"";
        }
        return "Users configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Users'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Users mudule, click \"Install\". This will create the
				Users tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }
    
    public static function Informations($lang) {
        if ($lang == "fr") {
            return "Informations";
        }
        return "Informations";
    }
    
    public static function Unit($lang) {
        if ($lang == "fr") {
            return "Unité/équipe";
        }
        return "Unit";
    }
    
    public static function Phone($lang) {
        if ($lang == "fr") {
            return "Téléphone professionnel";
        }
        return "Phone";
    }
    
    public static function Avatar($lang) {
        if ($lang == "fr") {
            return "Avatar";
        }
        return "Avatar";
    }
    
    public static function Bio($lang) {
        if ($lang == "fr") {
            return "Bio";
        }
        return "Bio";
    }
    
    public static function UserInformationsHaveBeenSaved($lang) {
        if ($lang == "fr") {
            return "Les informations du compte ont bien été enregistrées";
        }
        return "Account informations have been saved";
    }
    
    public static function Create_item($item, $lang = "") {
        $result = ($lang === "fr") ? "Créer " : "Create " ;
        switch ($item) {
            case "user":
                $result .= ($lang === "fr") ? "ou ajouter un⋅e utilisateur⋅rice" : "or add a user";
                break;
            case "pending":
                $result = ($lang === "fr") ? "et/ou valider un compte en attente" : "and/or accept one pending request";
                break;
            default:
                break;
        }
        return $result;
    }

    public static function User_account($lang) {
        if ($lang == "fr") {
            return "compte utilisateur";
        }
        return "user account";
    }
    
}
