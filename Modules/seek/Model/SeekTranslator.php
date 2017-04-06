<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class SeekTranslator {

    public static function seek($lang) {
        if ($lang == "fr") {
            return "seek";
        }
        return "seek";
    }

    public static function seekConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Seek permet de ...";
        }
        return "The Seek module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Seek\"";
        }
        return "Seek configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Seek'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Seek mudule, click \"Install\". This will create the
				Seek tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function SeekUrl($lang = "") {
        if ($lang == "fr") {
            return "Seek URL";
        }
        return "Seek Url";
    }

    public static function Url($lang = "") {
        if ($lang == "fr") {
            return "URL";
        }
        return "Url";
    }

}
