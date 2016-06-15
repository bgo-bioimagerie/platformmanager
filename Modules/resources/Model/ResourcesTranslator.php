<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class ResourcesTranslator {

    public static function resources($lang){
        if ($lang == "fr") {
            return "Resources";
        }
        return "Resources";
    }
    
    public static function resourcesConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Resources permet de gérer des resources (descirption, fiches de vie)";
        }
        return "The Resources module allows to manage a resources database (description, maintenance sheets)";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Resources\"";
        }
        return "Resources configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Resources'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Resources mudule, click \"Install\". This will create the
				Resources tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Category($lang = "") {
        if ($lang == "fr") {
            return "Catégorie";
        }
        return "Category";
    }
    
    public static function Categories($lang = "") {
        if ($lang == "fr") {
            return "Catégories";
        }
        return "Categories";
    }
    
    public static function Area($lang = "") {
        if ($lang == "fr") {
            return "Domaine";
        }
        return "Area";
    }
    
    public static function Areas($lang = "") {
        if ($lang == "fr") {
            return "Domaines";
        }
        return "Areas";
    }
    
    public static function Display_order($lang = "") {
        if ($lang == "fr") {
            return "Ordre d'affichage";
        }
        return "Display order";
    }
    
    public static function Edit_Area($lang) {
        if ($lang == "fr") {
            return "Modifier domaine";
        }
        return "Edit area";
    }
    
    public static function Edit_Category($lang) {
        if ($lang == "fr") {
            return "Editer catégorie";
        }
        return "Edit category";
    }
    
    public static function Brand($lang){
                if ($lang == "fr") {
            return "Marque";
        }
        return "Brand";
    }
    
    public static function Type($lang){
                if ($lang == "fr") {
            return "Type";
        }
        return "Type";
    }
    
    public static function Description($lang){
                if ($lang == "fr") {
            return "Description";
        }
        return "Description";
    }
    
    public static function Infos($lang){
                if ($lang == "fr") {
            return "Informations";
        }
        return "Informations";
    }
    
    public static function Events($lang){
                if ($lang == "fr") {
            return "Suivi";
        }
        return "Events";
    }
    
    public static function Event_Types($lang){
                if ($lang == "fr") {
            return "Types d'évenement";
        }
        return "Event types";
    }
    
        public static function Event_Type($lang){
                if ($lang == "fr") {
            return "Type d'évenement";
        }
        return "Event type";
    }
    
    public static function States($lang){
                if ($lang == "fr") {
            return "Etats";
        }
        return "States";
    }
    
    public static function State($lang){
                if ($lang == "fr") {
            return "Etat";
        }
        return "State";
    }
    
    public static function Edit_Event_Type($lang){
                if ($lang == "fr") {
            return "Modifier type d'évenement";
        }
        return "Edit event type";
    }
    
    public static function Comment($lang){
                if ($lang == "fr") {
            return "Commentaire";
        }
        return "Comment";
    }
    
        public static function Add_event($lang){
                if ($lang == "fr") {
            return "Ajouter un évenement";
        }
        return "Add an event";
    }
    
    public static function Edit_event_for($lang){
                if ($lang == "fr") {
            return "Modifier évenement pour la resource: ";
        }
        return "Edit event for:";
    }
    
    public static function Add_File($lang){
        if ($lang == "fr") {
            return "Ajouter un fichier";
        }
        return "Add file";
    }
    
    public static function Files($lang){
        if ($lang == "fr") {
            return "Fichiers";
        }
        return "Files";
    }

    public static function Edit_State($lang){
        if ($lang == "fr") {
            return "Modifier un état";
        }
        return "Edit state";
    }
    
    public static function Resps_Status($lang){
        if ($lang == "fr") {
            return "Staus des responsables";
        }
        return "Instructors status";
    }
    
    public static function Edit_Resps_status($lang){
        if ($lang == "fr") {
            return "Modifier un staus de responsable";
        }
        return "Edit instructor status";
    }
    
    public static function Status($lang){
        if ($lang == "fr") {
            return "Status";
        }
        return "Status";
    }
    
    
    
 }
