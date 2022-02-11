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

    public static function Invoice($lang) {
        if ($lang == "fr") {
            return "Facture";
        }
        return "Invoice";
    }

    public static function All_invoices($lang) {
        if ($lang == "fr") {
            return "Tous les relevés";
        }
        return "All invoices";
    }

    public static function To_Send_invoices($lang) {
        if ($lang == "fr") {
            return "Relevés à envoyer";
        }
        return "To send invoices";
    }

    public static function Sent_invoices($lang) {
        if ($lang == "fr") {
            return "Relevés envoyés";
        }
        return "Sent invoices";
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
            return "Date de libération produit";
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

    public static function GeneratePdfDetails($lang = "") {
        if ($lang == "fr") {
            return "PDF avec détails";
        }
        return "PDF with details";
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

    public static function SendStatus($lang) {
        if ($lang == "fr") {
            return "Envoi";
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

    public static function Edited_by($lang) {
        if ($lang == "fr") {
            return "Edité par";
        }
        return "Edited by";
    }

    public static function Title($lang) {
        if ($lang == "fr") {
            return "Info";
        }
        return "Title";
    }

    public static function Invoice_All($lang = "") {
        if ($lang == "fr") {
            return "Facturer tout";
        }
        return "Invoice all";
    }

    public static function Invoice_Responsible($lang = "") {
        if ($lang == "fr") {
            return "Facturer un responsable";
        }
        return "Invoice a person in charge";
    }

    public static function PDFTemplate($lang = "") {
        if ($lang == "fr") {
            return "Modèle de facture HTML - PDF";
        }
        return "HTML - PDF Template";
    }

    public static function uploadTemplate($lang) {
        if ($lang == "fr") {
            return "Téléverser modèle (format Twig)";
        }
        return "Upload template (Twig format)";
    }

    public static function Upload($lang) {
        if ($lang == "fr") {
            return "Téléverser";
        }
        return "Upload";
    }

    public static function UploadImages($lang) {
        if ($lang == "fr") {
            return "Téléverser images";
        }
        return "Upload images";
    }

    public static function Images($lang) {
        if ($lang == "fr") {
            return "Images";
        }
        return "Images";
    }

    public static function Name($lang) {
        if ($lang == "fr") {
            return "Name";
        }
        return "Name";
    }

    public static function Discount($lang) {
        if ($lang == "fr") {
            return "Remise";
        }
        return "Discount";
    }

    public static function Visas($lang) {
        if ($lang == "fr") {
            return "Visas";
        }
        return "Visas";
    }

    public static function Visa($lang) {
        if ($lang == "fr") {
            return "Visa";
        }
        return "Visa";
    }

    public static function Date_send($lang) {
        if ($lang == "fr") {
            return "Date d’envoi facture";
        }
        return "Date send";
    }

    public static function Visa_send($lang) {
        if ($lang == "fr") {
            return "Visa d’envoi facture";
        }
        return "Visa to send";
    }

    public static function useInvoiceDatePaid($lang) {
        if ($lang == "fr") {
            return "Utiliser l'information date de paiement";
        }
        return "Use invoice date paid";
    }

    public static function TheFieldVisaIsMandatoryWithSend($lang) {
        if ($lang == "fr") {
            return "Erreur: le champ visa est obligatoire pour envoyer un relevé";
        }
        return "Error: the field visa is mandatory for a send an invoice";
    }

    public static function InvoiceHasBeenSaved($lang) {
        if ($lang == "fr") {
            return "Les informations du relevé ont bien été enregistrées";
        }
        return "Invoice informations have been saved";
    }

    public static function currentTemplate($lang) {
        if ($lang == "fr") {
            return "Modèle actuel";
        }
        return "Current template";
    }

    public static function Download($lang) {
        if ($lang == "fr") {
            return "Télécharger";
        }
        return "Download";
    }

    public static function DownloadTemplate($lang) {
        if ($lang == "fr") {
            return "Télécharger template";
        }
        return "Download template";
    }

    public static function TheTemplateHasBeenUploaded($lang) {
        if ($lang == "fr") {
            return "Le modèle à bien été téléversé";
        }
        return "The template has been uploaded";
    }

    public static function NewInvoice($lang) {
        if ($lang == "fr") {
            return "Nouveau relevé";
        }
        return "New invoice";
    }

    public static function Product($lang) {
        if ($lang == "fr") {
            return "Produit/service";
        }
        return "Product";
    }

    public static function PDF($lang) {
        if ($lang == "fr") {
            return "PDF";
        }
        return "PDF";
    }
    
    public static function PDFDetails($lang) {
        if ($lang == "fr") {
            return "PDF avec détails";
        }
        return "PDF with details";
    }
    
    public static function User($lang) {
        if ($lang == "fr") {
            return "Utilisateur";
        }
        return "User";
    }
    
    public static function NonNumericValue($lang) {
        if ($lang == "fr") {
            return "Certaines de vos valeurs ne sont pas numériques. Merci de les éditer avant validation.";
        }
        return "Some of your values are non numeric and won't display. PLease edit them before saving.";
    }

    public static function NoTemplate($lang) {
        if($lang == 'fr') {
            return 'Attention, il faut définir un template dans la configuration';
        }
        return 'Warning: no template defined in configuration';
    }

}
