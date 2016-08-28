<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class ServicesTranslator {

    public static function servicesstatisticsorder($lang) {
        if ($lang == "fr") {
            return "Services commandes";
        }
        return "Services orders";
    }

    public static function servicesstatisticsproject($lang) {
        if ($lang == "fr") {
            return "Services projets";
        }
        return "Services projects";
    }

    public static function services($lang) {
        if ($lang == "fr") {
            return "services";
        }
        return "services";
    }

    public static function service($lang) {
        if ($lang == "fr") {
            return "service";
        }
        return "service";
    }

    public static function UnitPrice($lang) {
        if ($lang == "fr") {
            return "Prix unitaire";
        }
        return "Unit price";
    }

    public static function servicesConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Services permet de gérer une base de données de prestations et consomables";
        }
        return "The Services module allows to manage a services and supplies database";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Services\"";
        }
        return "Services configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Services'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Services mudule, click \"Install\". This will create the
				Services tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Stock($lang) {
        if ($lang == "fr") {
            return "Stock";
        }
        return "Stock";
    }

    public static function Purchase($lang) {
        if ($lang == "fr") {
            return "Achat";
        }
        return "Purchase";
    }

    public static function Opened_orders($lang) {
        if ($lang == "fr") {
            return "Commandes en cours";
        }
        return "Opened orders";
    }

    public static function Closed_orders($lang) {
        if ($lang == "fr") {
            return "Commandes closes";
        }
        return "Closed orders";
    }

    public static function All_orders($lang) {
        if ($lang == "fr") {
            return "Toutes les commandes";
        }
        return "All orders";
    }

    public static function New_orders($lang) {
        if ($lang == "fr") {
            return "Nouvelle commande";
        }
        return "New order";
    }

    public static function Opened_projects($lang) {
        if ($lang == "fr") {
            return "Projets en cours";
        }
        return "Opened projects";
    }

    public static function Closed_projects($lang) {
        if ($lang == "fr") {
            return "Projets clos";
        }
        return "Closed projects";
    }

    public static function All_projects($lang) {
        if ($lang == "fr") {
            return "Tous les projets";
        }
        return "All projects";
    }

    public static function New_project($lang) {
        if ($lang == "fr") {
            return "Nouveau projet";
        }
        return "New project";
    }

    public static function Edit_service($lang) {
        if ($lang == "fr") {
            return "Modifier un service";
        }
        return "Edit service";
    }

    public static function Quantity($lang) {
        if ($lang == "fr") {
            return "Quantité";
        }
        return "Quantity";
    }

    public static function Services_Orders($lang) {
        if ($lang == "fr") {
            return "Bon de commande de services";
        }
        return "Services orders";
    }

    public static function No_identification($lang) {
        if ($lang == "fr") {
            return "No identification";
        }
        return "No identification";
    }

    public static function Opened_date($lang) {
        if ($lang == "fr") {
            return "Date d'ouverture";
        }
        return "Opened_date";
    }

    public static function Closed_date($lang) {
        if ($lang == "fr") {
            return "Date de fermeture";
        }
        return "Closed date";
    }

    public static function Last_modified_date($lang) {
        if ($lang == "fr") {
            return "Dernière modification";
        }
        return "Last modified date";
    }

    public static function Edit_order($lang) {
        if ($lang == "fr") {
            return "Modifier un bon de commande";
        }
        return "Edit order";
    }

    public static function Services_list($lang) {
        if ($lang == "fr") {
            return "Services";
        }
        return "Services list";
    }

    public static function Opened($lang) {
        if ($lang == "fr") {
            return "Ouvert";
        }
        return "Open";
    }

    public static function Closed($lang) {
        if ($lang == "fr") {
            return "Fermé";
        }
        return "Closed";
    }

    public static function Services_Projects($lang) {
        if ($lang == "fr") {
            return "Projets";
        }
        return "Services projects";
    }

    public static function Edit_projects($lang) {
        if ($lang == "fr") {
            return "Modifier projet";
        }
        return "Edit project";
    }

    public static function New_team($lang) {
        if ($lang == "fr") {
            return "Nouvelle equipe";
        }
        return "New team";
    }

    public static function Academique($lang) {
        if ($lang == "fr") {
            return "Académique";
        }
        return "Academic";
    }

    public static function Industry($lang) {
        if ($lang == "fr") {
            return "Privé";
        }
        return "Industry";
    }

    public static function Time_limite($lang) {
        if ($lang == "fr") {
            return "Délai";
        }
        return "Time limit";
    }

    public static function Comment($lang) {
        if ($lang == "fr") {
            return "Comment";
        }
        return "Comment";
    }

    public static function ServicesBalance($lang = "") {
        if ($lang == "fr") {
            return "Bilan services";
        }
        return "Services balance";
    }

    public static function Beginning_period($lang = "") {
        if ($lang == "fr") {
            return "Début période";
        }
        return "Beginning period";
    }

    public static function End_period($lang = "") {
        if ($lang == "fr") {
            return "Fin période";
        }
        return "End period";
    }

    public static function Invoice_order($lang = "") {
        if ($lang == "fr") {
            return "Facturer les commandes";
        }
        return "Invoice orders";
    }

    public static function Invoice_project($lang = "") {
        if ($lang == "fr") {
            return "Facturer les projets";
        }
        return "Invoice projects";
    }

    public static function Invoice_by_unit($lang = "") {
        if ($lang == "fr") {
            return "Facturation par unité";
        }
        return "Invoice by unit";
    }

    public static function Orders($lang = "") {
        if ($lang == "fr") {
            return "Commandes";
        }
        return "Orders";
    }

    public static function Prices($lang = "") {
        if ($lang == "fr") {
            return "Tarifs";
        }
        return "Prices";
    }

    public static function Project($lang = "") {
        if ($lang == "fr") {
            return "Project";
        }
        return "Project";
    }

    public static function Projects($lang = "") {
        if ($lang == "fr") {
            return "Projects";
        }
        return "Projects";
    }

    public static function By_projects($lang = "") {
        if ($lang == "fr") {
            return "Par projets";
        }
        return "By projects";
    }

    public static function By_period($lang = "") {
        if ($lang == "fr") {
            return "Par période";
        }
        return "By period";
    }

    public static function PricesServices($lang = "") {
        if ($lang == "fr") {
            return "Tarifs services";
        }
        return "Prices services";
    }

    public static function Projects_balance($lang = "") {
        if ($lang == "fr") {
            return "Bilan projets";
        }
        return "Projects balance";
    }

    public static function Project_number($lang = "") {
        if ($lang == "fr") {
            return "Projet number";
        }
        return "Project number";
    }

    public static function BalanceSheetFrom($lang) {
        if ($lang == "fr") {
            return "Bilan sur la période du ";
        }
        return "Balance sheet from ";
    }

    public static function To($lang) {
        if ($lang == "fr") {
            return " au ";
        }
        return " to ";
    }

    public static function Sevices_billed_details($lang) {
        if ($lang == "fr") {
            return "PRESTATIONS FACTUREES";
        }
        return "BILLED SERVICES";
    }

    public static function No_Projet($lang) {
        if ($lang == "fr") {
            return "No projet";
        }
        return "# project";
    }

    public static function invoices($lang) {
        if ($lang == "fr") {
            return "FACTURES";
        }
        return "INVOICES";
    }

    public static function Total_HT($lang) {
        if ($lang == "fr") {
            return "Total HT";
        }
        return "Total HT";
    }

    public static function StatisticsMaj($lang) {
        if ($lang == "fr") {
            return "STATISTIQUES";
        }
        return "STATISTICS";
    }

    public static function numberNewIndustryTeam($lang) {
        if ($lang == "fr") {
            return "Nb de nouveau utilisateurs privé";
        }
        return "Number of new industry team";
    }

    public static function purcentageNewIndustryTeam($lang) {
        if ($lang == "fr") {
            return "Pourcentage de nouveau utilisateurs privé";
        }
        return "Purcentage of new industry team";
    }

    public static function numberIndustryProjects($lang) {
        if ($lang == "fr") {
            return "Nb de projets avec les Privé";
        }
        return "Number of industry projects";
    }

    public static function loyaltyIndustryProjects($lang) {
        if ($lang == "fr") {
            return "Fidélisation des Privé";
        }
        return "Loyalty of industry projects";
    }

    public static function numberNewAccademicTeam($lang) {
        if ($lang == "fr") {
            return "Nb de nouveaux académiques";
        }
        return "Number of new accademic team";
    }

    public static function purcentageNewAccademicTeam($lang) {
        if ($lang == "fr") {
            return "Pourcentage de nouveaux académiques";
        }
        return "Purcentage of new accademic team";
    }

    public static function numberAccademicProjects($lang) {
        if ($lang == "fr") {
            return "Nb de projets avec les académiques";
        }
        return "Number of accademic projects";
    }

    public static function loyaltyAccademicProjects($lang) {
        if ($lang == "fr") {
            return "Fidélisation des académiques";
        }
        return "Loyalty of accademic projects";
    }

    public static function totalNumberOfProjects($lang) {
        if ($lang == "fr") {
            return "Nombre total des projets";
        }
        return "Total number of projects";
    }

    public static function Sevices_details($lang) {
        if ($lang == "fr") {
            return "PRESTATIONS";
        }
        return "Sevices details";
    }




}
