<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class DatabaseTranslator {

    public static function database($lang){
        if ($lang == "fr") {
            return "base de données";
        }
        return "database";
    }
    
    public static function databaseConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Database permet de créer une base de donnée dynamiquement";
        }
        return "The Database module allows to create a database";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Database\"";
        }
        return "Database configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Database'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Database mudule, click \"Install\". This will create the
				Database tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function NewDatabase($lang) {
        if ($lang == "fr") {
            return "Nouvelle base";
        }
        return "New database";
    }
    
    public static function Database_informations($lang) {
        if ($lang == "fr") {
            return "Informations";
        }
        return "Database informations";
    }
    
    public static function Info($lang) {
        if ($lang == "fr") {
            return "Informations";
        }
        return "informations";
    }
    
    public static function Classes($lang) {
        if ($lang == "fr") {
            return "Classes";
        }
        return "Classes";
    }
    
    public static function Classe($lang) {
        if ($lang == "fr") {
            return "Classe";
        }
        return "Class";
    }
    
    public static function Views($lang) {
        if ($lang == "fr") {
            return "Vues";
        }
        return "Views";
    }
    
    public static function Menu($lang) {
        if ($lang == "fr") {
            return "Menu";
        }
        return "Menu";
    }
    
    public static function lang($lang) {
        if ($lang == "fr") {
            return "Langue";
        }
        return "Language";
    }
    
    public static function View_name($lang) {
        if ($lang == "fr") {
            return "Nom affiché";
        }
        return "View name";
    }
    
    public static function Attributs($lang) {
        if ($lang == "fr") {
            return "Attributs";
        }
        return "Attributs";
    }
    
    public static function NewClass($lang) {
        if ($lang == "fr") {
            return "Nouvelle classe";
        }
        return "New class";
    }
    
    public static function Type($lang) {
        if ($lang == "fr") {
            return "Type";
        }
        return "Type";
    }
    
    public static function Foreign_class($lang) {
        if ($lang == "fr") {
            return "Lien classe";
        }
        return "Foreign class";
    }
    
    public static function Foreign_key($lang) {
        if ($lang == "fr") {
            return "Attribut vue classe";
        }
        return "Foreign view key";
    }
    
    public static function Mandatory($lang) {
        if ($lang == "fr") {
            return "Obligatoire";
        }
        return "Mandatory";
    }
    
    public static function Dictionnary($lang){
        if ($lang == "fr") {
            return "Dictionnaire";
        }
        return "Dictionnary";
    }
    
    public static function Preview($lang){
        if ($lang == "fr") {
            return "Aperçu";
        }
        return "Preview";
    }
    
    public static function NewView($lang){
        if ($lang == "fr") {
            return "Nouvelle vue";
        }
        return "New view";
    }
    
    public static function Display_order($lang){
        if ($lang == "fr") {
            return "Ordre d'affichage";
        }
        return "Display order";
    }
    
    public static function Page($lang){
        if ($lang == "fr") {
            return "Page";
        }
        return "Page";
    }
    
    public static function Text($lang){
        if ($lang == "fr") {
            return "Text";
        }
        return "Text";
    }
    
    public static function Install($lang){
        if ($lang == "fr") {
            return "Installer";
        }
        return "Install";       
    }
 }
