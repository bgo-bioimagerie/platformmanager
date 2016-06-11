<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class TemplateTranslator {

    public static function template($lang){
        if ($lang == "fr") {
            return "template";
        }
        return "template";
    }
    
    public static function templateConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Template permet de ...";
        }
        return "The Template module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Template\"";
        }
        return "Template configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Template'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Template mudule, click \"Install\". This will create the
				Template tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

 }
