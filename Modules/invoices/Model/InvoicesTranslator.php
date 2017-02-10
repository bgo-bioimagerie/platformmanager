<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class InvoicesTranslator {

    public static function invoices($lang) {
        if ($lang == "fr") {
            return "Facturation";
        }
        return "Invoices";
    }

    public static function All_invoices($lang) {
        if ($lang == "fr") {
            return "Tous les relevés";
        }
        return "All invoices";
    }

    public static function invoicesConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Invoices permet de générer des factures";
        }
        return "The Invoices module allows to generate invoices from other modules database";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Invoices\"";
        }
        return "Invoices configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Invoices'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Invoices mudule, click \"Install\". This will create the
				Invoices tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Number($lang) {
        if ($lang == "fr") {
            return "Numéro";
        }
        return "#";
    }

    public static function Date_generated($lang) {
        if ($lang == "fr") {
            return "Date d'émission";
        }
        return "Date generated";
    }

    public static function Date_paid($lang) {
        if ($lang == "fr") {
            return "Date règlement";
        }
        return "Date paid";
    }

    public static function Total_HT($lang) {
        if ($lang == "fr") {
            return "Total HT";
        }
        return "Total HT";
    }

    public static function Edit_invoice($lang) {
        if ($lang == "fr") {
            return "Modifier facture";
        }
        return "Edit invoice";
    }

    public static function Content($lang = "") {
        if ($lang == "fr") {
            return "Contenu";
        }
        return "Content";
    }

    public static function GeneratePdf($lang = "") {
        if ($lang == "fr") {
            return "PDF";
        }
        return "PDF";
    }

    public static function Designation($lang) {
        if ($lang == "fr") {
            return "Désignation";
        }
        return "Designation";
    }

    public static function UnitPrice($lang) {
        if ($lang == "fr") {
            return "Prix unitaire";
        }
        return "Unit price";
    }

    public static function Quantity($lang) {
        if ($lang == "fr") {
            return "Quantité";
        }
        return "Quantity";
    }

    public static function Price_HT($lang) {
        if ($lang == "fr") {
            return "Prix HT";
        }
        return "Net price";
    }

    public static function Info($lang) {
        if ($lang == "fr") {
            return "Info";
        }
        return "Info";
    }

    public static function InvoiceInfo($lang) {
        if ($lang == "fr") {
            return "Informations facture";
        }
        return "invoice informations";
    }

    public static function Period_begin($lang) {
        if ($lang == "fr") {
            return "Début période";
        }
        return "Period begin";
    }

    public static function Period_end($lang) {
        if ($lang == "fr") {
            return "Fin période";
        }
        return "Period end";
    }

    public static function OwnerPrice($lang) {
        if ($lang == "fr") {
            return "Tarifs préférentiel";
        }
        return "Owner price";
    }

    public static function invoiceperiodbegin($lang) {
        if ($lang == "fr") {
            return "Début periode facturation";
        }
        return "Invoices period begin";
    }

    public static function invoiceperiodend($lang) {
        if ($lang == "fr") {
            return "Fin periode facturation";
        }
        return "Invoices period end";
    }

    public static function invoiceperiod($lang) {
        if ($lang == "fr") {
            return "Periode facturation";
        }
        return "Invoices end";
    }

}
