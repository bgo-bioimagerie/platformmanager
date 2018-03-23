<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class TranferTranslator {

    public static function tranfer($lang) {
        if ($lang == "fr") {
            return "tranfer";
        }
        return "tranfer";
    }

    public static function tranferConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Tranfer permet de ...";
        }
        return "The Tranfer module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Tranfer\"";
        }
        return "Tranfer configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Tranfer'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Tranfer mudule, click \"Install\". This will create the
				Tranfer tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Providers($lang) {
        if ($lang == "fr") {
            return "Fournisseurs";
        }
        return "Providers";
    }
    
    public static function NewProvider($lang) {
        if ($lang == "fr") {
            return "Nouveau";
        }
        return "New provider";
    }

    public static function Edit_Provider($lang) {
        if ($lang == "fr") {
            return "Edition d'un fournisseur";
        }
        return "Edit provider";
    }
    
}
