<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class DevTranslator {

    public static function dev($lang){
        if ($lang == "fr") {
            return "dev";
        }
        return "dev";
    }
    
    public static function devConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Dev permet de générer la trame du code d'un nouveau module";
        }
        return "The Dev module allows to automaticaly generate the base code for a new module";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Dev\"";
        }
        return "Dev configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Dev'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Dev mudule, click \"Install\". This will create the
				Dev tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function GenerateModule($lang){
        if ($lang == "fr") {
            return "Générer un nouveau module";
        }
        return "Generate new module";
    }
    
    public static function Generate($lang){
        if ($lang == "fr") {
            return "Générer";
        }
        return "Generate";
    }
    
    public static function TheModuleHasBeenGenerated($lang){
        if ($lang == "fr") {
            return "Le module à été généré";
        }
        return "The module has been generated";
    }
    
    
 }
