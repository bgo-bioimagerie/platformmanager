<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class ComTranslator {

    public static function com($lang){
        if ($lang == "fr") {
            return "com";
        }
        return "com";
    }
    
    public static function comConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Com permet de ...";
        }
        return "The Com module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Com\"";
        }
        return "Com configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Com'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Com mudule, click \"Install\". This will create the
				Com tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Tilemessage($lang = ""){
             if ($lang == "fr") {
            return "Message accueil espace";
        }
        return "Space tile message";   
    }
    
 }
