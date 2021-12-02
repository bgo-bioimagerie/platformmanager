<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class StatisticsTranslator {

    public static function statistics($lang) {
        if ($lang == "fr") {
            return "Statistiques";
        }
        return "Statistics";
    }

    public static function statisticsConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Statistics permet de calculer des statistiques utilisant les données de plusieurs modules (resources, booking, services...)";
        }
        return "The Statistics module allows to calculate statistics using multiple modules data (resources, booking, services...)";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Statistics\"";
        }
        return "Statistics configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Statistics'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Statistics mudule, click \"Install\". This will create the
				Statistics tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function BookingBalance($lang = "") {
        if ($lang == "fr") {
            return "Bilan réservations";
        }
        return "Booking balance";
    }

    public static function StatisticsGlobal($lang = "") {
        if ($lang == "fr") {
            return "Bilan global";
        }
        return "Global balance";
    }

    public static function Period_begining($lang = "") {
        if ($lang == "fr") {
            return "Début période";
        }
        return "Period begining";
    }

    public static function Period_end($lang = "") {
        if ($lang == "fr") {
            return "Fin période";
        }
        return "Period end";
    }

    public static function Exclude_colorcodes($lang = "") {
        if ($lang == "fr") {
            return "Exclure les codes couleur";
        }
        return "Exclude color codes";
    }

    public static function Statisticsperiod($lang) {
        if ($lang == "fr") {
            return "Période par défaut";
        }
        return "Default period";
    }

    public static function statisticsperiodbegin($lang) {
        if ($lang == "fr") {
            return "Début de période";
        }
        return "Period begin";
    }
    
    public static function statisticsperiodend($lang) {
        if ($lang == "fr") {
            return "Fin de période";
        }
        return "Period end";
    }

}
