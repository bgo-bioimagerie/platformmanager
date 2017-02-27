<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class AntibodiesTranslator {

    public static function antibodies($lang){
        if ($lang == "fr") {
            return "Anticorps";
        }
        return "Antibodies";
    }
    
    public static function antibodiesConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Antibodies permet de gérer une base de donnée d'anticorps";
        }
        return "The Antibodies module allows to manage an antibodies data base";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Antibodies\"";
        }
        return "Antibodies configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Antibodies'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Antibodies mudule, click \"Install\". This will create the
				Antibodies tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Export_as_csv($lang) {
        if ($lang == "fr") {
            return "Exporter en csv";
        }
        return "Export as csv";
    }
    
    public static function AntibodyInfo($lang) {
        if ($lang == "fr") {
            return "Informations anticorps";
        }
        return "Antibody informations";
    }
    
 }
