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
            return "Bilan projets";
        }
        return "Services projects";
    }

    public static function services($lang) {
        if ($lang == "fr") {
            return "Prestations";
        }
        return "Services";
    }

    public static function service($lang) {
        if ($lang == "fr") {
            return "Prestation";
        }
        return "Service";
    }

    public static function servicesprices($lang) {
        if ($lang == "fr") {
            return "Tarifs";
        }
        return "Prices";
    }

    public static function servicesinvoiceorder($lang) {
        if ($lang == "fr") {
            return "Relever commandes";
        }
        return "Invoice orders";
    }

    public static function servicesinvoiceproject($lang) {
        if ($lang == "fr") {
            return "Relevé projets";
        }
        return "Invoice projects";
    }

    public static function Listing($lang) {
        if ($lang == "fr") {
            return "Liste des prestations";
        }
        return "Listing";
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

    public static function New_Purchase($lang) {
        if ($lang == "fr") {
            return "Nouvel achat";
        }
        return "New purchase";
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
            return "Quantité/Temps/Prix";
        }
        return "Quantity/Time/Price";
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
            return "Date de libération produit";
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

    public static function OpenedUpper($lang) {
        if ($lang == "fr") {
            return "OUVERT";
        }
        return "OPEN";
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

    public static function Add_projects($lang) {
        if ($lang == "fr") {
            return "Ajouter projet";
        }
        return "Add project";
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

    public static function OrderBalance($lang = "") {
        if ($lang == "fr") {
            return "Bilan commandes";
        }
        return "Orders balance";
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
            return "Relevé projets";
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
            return "Tarifs prestations";
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
            return "Projets";
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

    public static function Sheet($lang) {
        if ($lang == "fr") {
            return "Fiche";
        }
        return "Sheet";
    }

    public static function FollowUp($lang) {
        if ($lang == "fr") {
            return "Suivi";
        }
        return "Follow-up";
    }

    public static function projectEdited($lang) {
        if ($lang == "fr") {
            return "La fiche projet à bien été enregistrée !";
        }
        return "Project sheet has been saved !";
    }

    public static function Description($lang) {
        if ($lang == "fr") {
            return "Description";
        }
        return "Description";
    }

    public static function NewEntry($lang) {
        if ($lang == "fr") {
            return "Nouveau produit";
        }
        return "New service";
    }

    public static function projectperiodbegin($lang) {
        if ($lang == "fr") {
            return "Début periode projet";
        }
        return "Project period begin";
    }

    public static function projectperiodend($lang) {
        if ($lang == "fr") {
            return "Fin periode projet";
        }
        return "Project period end";
    }

    public static function projectperiod($lang) {
        if ($lang == "fr") {
            return "Periode projet";
        }
        return "Period end";
    }

    public static function UseProject($lang) {
        if ($lang == "fr") {
            return "Utiliser les projects";
        }
        return "Use projects";
    }

    public static function UseCommand($lang) {
        if ($lang == "fr") {
            return "Utiliser les commandes";
        }
        return "Use commands";
    }

    public static function InvoiceIt($lang) {
        if ($lang == "fr") {
            return "Facturer";
        }
        return "Invoice it";
    }

    public static function Created_by($lang) {
        if ($lang == "fr") {
            return "Créé par";
        }
        return "Created by";
    }

    public static function Modified_by($lang) {
        if ($lang == "fr") {
            return "Modifié par";
        }
        return "Modified by";
    }

    public static function Date_begin($lang) {
        if ($lang == "fr") {
            return "Début période";
        }
        return "Date begin";
    }

    public static function Date_end($lang) {
        if ($lang == "fr") {
            return "Fin période";
        }
        return "Date end";
    }

    public static function UseStock($lang) {
        if ($lang == "fr") {
            return "Utiliser la gestion de stock";
        }
        return "Use stock";
    }

    public static function ExportCsv($lang) {
        if ($lang == "fr") {
            return "Exporter CSV";
        }
        return "Export CSV";
    }

    public static function Title($lang) {
        if ($lang == "fr") {
            return "Intitulé";
        }
        return "Title";
    }

    public static function servicesOrigin($lang) {
        if ($lang == "fr") {
            return "Origine";
        }
        return "Origin";
    }

    public static function Edit_Origin($lang) {
        if ($lang == "fr") {
            return "Editer origin";
        }
        return "Edit origin";
    }

    public static function OriginsFrom($lang) {
        if ($lang == "fr") {
            return "Décompte des origines sur le période du ";
        }
        return "Origins counting from ";
    }

    public static function Dates_are_not_correct($lang) {
        if ($lang == "fr") {
            return "Les dates ne sont pas correctes ";
        }
        return "Dates are not correct ";
    }

    public static function OriginsMaj($lang) {
        if ($lang == "fr") {
            return "ORIGINES";
        }
        return "ORIGINS";
    }
    
    public static function Discount($lang) {
        if ($lang == "fr") {
            return "Pourcentage de remise";
        }
        return "Discount";
    }
    
    public static function Visa($lang) {
        if ($lang == "fr") {
            return "Visa";
        }
        return "Visa";
    }
    
    public static function servicesVisas($lang) {
        if ($lang == "fr") {
            return "Visas";
        }
        return "Visas";
    }
    
    public static function Closed_by($lang) {
        if ($lang == "fr") {
            return "Visas";
        }
        return "Closed by";
    }
    
    
}
