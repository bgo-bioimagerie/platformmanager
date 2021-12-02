<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class BulletjournalTranslator {

    public static function bulletjournal($lang) {
        if ($lang == "fr") {
            return "bulletjournal";
        }
        return "bulletjournal";
    }

    public static function bulletjournalConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Bulletjournal est une implémentation du système bullet journal de gestion de tâches";
        }
        return "The Bulletjournal module is an implementation of the billet journal time management method";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Bulletjournal\"";
        }
        return "Bulletjournal configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Bulletjournal'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Bulletjournal mudule, click \"Install\". This will create the
				Bulletjournal tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_modules($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Calendar($lang) {
        if ($lang == "fr") {
            return "Calendrier";
        }
        return "Calendar";
    }
    
    public static function Notes($lang) {
        if ($lang == "fr") {
            return "Notes";
        }
        return "Notes";
    }

    public static function Collections($lang) {
        if ($lang == "fr") {
            return "Collections";
        }
        return "Collections";
    }

    public static function Migrations($lang) {
        if ($lang == "fr") {
            return "Migrations";
        }
        return "Migrations";
    }
    
    public static function Event($lang) {
        if ($lang == "fr") {
            return "Evenement";
        }
        return "Event";
    }
    
    public static function Task($lang) {
        if ($lang == "fr") {
            return "Tâche";
        }
        return "Task";
    }
    
    public static function ThisMonth($lang) {
        if ($lang == "fr") {
            return "Ce mois";
        }
        return "This Month";
    }

    public static function Title($lang) {
        if ($lang == "fr") {
            return "Titre";
        }
        return "Title";
    }
    
    public static function Content($lang) {
        if ($lang == "fr") {
            return "Contenu";
        }
        return "Content";
    }
    
    public static function Deadline($lang) {
        if ($lang == "fr") {
            return "Deadline";
        }
        return "Deadline";
    }
    
    public static function Priority($lang) {
        if ($lang == "fr") {
            return "Priorité";
        }
        return "Priority";
    }
    
    public static function MarkAsDone($lang) {
        if ($lang == "fr") {
            return "Fait";
        }
        return "Mark as done";
    }
    
    public static function Cancel($lang) {
        if ($lang == "fr") {
            return "Annuler";
        }
        return "Cancel";
    }
    
    public static function ReOpen($lang) {
        if ($lang == "fr") {
            return "Réouvrir";
        }
        return "Re Open";
    }
    
    public static function DateStart($lang) {
        if ($lang == "fr") {
            return "Date début";
        }
        return "Date start";
    }
    
    public static function HourStart($lang) {
        if ($lang == "fr") {
            return "Heure début";
        }
        return "Hour start";
    }
    
    public static function DateEnd($lang) {
        if ($lang == "fr") {
            return "Date fin";
        }
        return "Date end";
    }
    
    public static function HourEnd($lang) {
        if ($lang == "fr") {
            return "Heure fin";
        }
        return "Hour end";
    }
    
    
    public static function Migrate($lang) {
        if ($lang == "fr") {
            return "Migrer";
        }
        return "Migrate";
    }
    
    public static function migrate_task($lang) {
        if ($lang == "fr") {
            return "Migrer la tache";
        }
        return "migrate task";
    }
    
    public static function Add_Collection($lang) {
        if ($lang == "fr") {
            return "Ajouter collection";
        }
        return "Add collection";
    }
    
    public static function Edit_collection($lang) {
        if ($lang == "fr") {
            return "Modifier collection";
        }
        return "Edit collection";
    }
    
    public static function See($lang) {
        if ($lang == "fr") {
            return "Voir";
        }
        return "See";
    }
    
    
}
