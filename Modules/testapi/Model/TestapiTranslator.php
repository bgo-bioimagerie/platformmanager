<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class TestapiTranslator {

    public static function testapi($lang){
        if ($lang == "fr") {
            return "testapi";
        }
        return "testapi";
    }
    
    public static function testapiConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Testapi permet de ...";
        }
        return "The Testapi module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Testapi\"";
        }
        return "Testapi configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Testapi'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Testapi mudule, click \"Install\". This will create the
				Testapi tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

 }
