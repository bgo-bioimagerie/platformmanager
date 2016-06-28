<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class BookingTranslator {

    public static function booking($lang){
        if ($lang == "fr") {
            return "Calendrier";
        }
        return "booking";
    }
    
    public static function bookingConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Booking permet de ...";
        }
        return "The Booking module allows to ...";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Booking\"";
        }
        return "Booking configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Booking'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Booking mudule, click \"Install\". This will create the
				Booking tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }
    
    public static function Booking_settings($lang){
        if ($lang == "fr") {
            return "Réglages calendrier";
        }
        return "Booking settings";
    }

    public static function Scheduling($lang){
        if ($lang == "fr") {
            return "Horaires";
        }
        return "Scheduling";
    }
    
    public static function Packages($lang){
        if ($lang == "fr") {
            return "Forfaits";
        }
        return "Packages";
    }
    
    public static function Supplementaries($lang){
        if ($lang == "fr") {
            return "Suppléments";
        }
        return "Supplementaries";
    }
    
    public static function Color_codes($lang){
        if ($lang == "fr") {
            return "Codes couleur";
        }
        return "Color codes";
    }
    
    public static function Block_Resouces($lang){
        if ($lang == "fr") {
            return "Bloquer ressources";
        }
        return "Block resouces";
    }
    
    
    
 }
