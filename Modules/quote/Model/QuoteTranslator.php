<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class QuoteTranslator {

    public static function quote($lang) {
        if ($lang == "fr") {
            return "Devis";
        }
        return "Quote";
    }

    public static function quoteConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Quote permet de générer des devis";
        }
        return "The Quote module allows to make quotes";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Quote\"";
        }
        return "Quote configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Quote'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Quote mudule, click \"Install\". This will create the
				Quote tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Quotes($lang) {
        if ($lang == "fr") {
            return "Devis";
        }
        return "Quotes";
    }

    public static function Recipient($lang) {
        if ($lang == "fr") {
            return "Destinataire";
        }
        return "Recipient";
    }

    public static function DateCreated($lang) {
        if ($lang == "fr") {
            return "Date création";
        }
        return "Date Created";
    }

    public static function DateLastModified($lang) {
        if ($lang == "fr") {
            return "Dernière modification";
        }
        return "Last Modified";
    }

    public static function Ok($lang) {
        if ($lang == "fr") {
            return "Ok";
        }
        return "Ok";
    }

    public static function NewQuote($lang) {
        if ($lang == "fr") {
            return "Nouveau devis";
        }
        return "New quote";
    }

    public static function Description($lang) {
        if ($lang == "fr") {
            return "Description";
        }
        return "Description";
    }

    public static function Address($lang) {
        if ($lang == "fr") {
            return "Adresse";
        }
        return "Address";
    }

    public static function Add($lang) {
        if ($lang == "fr") {
            return "Ajouter";
        }
        return "Ajouter";
    }

    public static function Remove($lang) {
        if ($lang == "fr") {
            return "Enlever";
        }
        return "Remove";
    }

    public static function Presta($lang) {
        if ($lang == "fr") {
            return "Prestation";
        }
        return "Service";
    }

    public static function EditQuote($lang) {
        if ($lang == "fr") {
            return "Devis";
        }
        return "Edit quote";
    }

    public static function CreateNewUserQuote($lang) {
        if ($lang == "fr") {
            return "Devis nouvel utilisateur";
        }
        return "Create new user quote";
    }

    public static function CreateExistingUserQuote($lang) {
        if ($lang == "fr") {
            return "Devis utilisateur";
        }
        return "Create new quote";
    }

    public static function QuoteHasBeenSaved($lang) {
        if ($lang == "fr") {
            return "Le devis a été enregistré";
        }
        return "Quote has been saved";
    }

    public static function Quantity($lang) {
        if ($lang == "fr") {
            return "Quantité";
        }
        return "Quantity";
    }

    public static function NewItem($lang) {
        if ($lang == "fr") {
            return "Nouvel item";
        }
        return "New item";
    }

    public static function FormItem($lang) {
        if ($lang == "fr") {
            return "contenu";
        }
        return "Form item";
    }
    
    public static function Comment($lang) {
        if ($lang == "fr") {
            return "Comment";
        }
        return "Comment";
    }
    
    public static function PDF($lang) {
        if ($lang == "fr") {
            return "PDF";
        }
        return "PDF";
    }

    public static function pricingNeeded($lang) {
        if ($lang == "fr") {
            return "Vous devez d'abord attribuer un secteur d'activité à votre client dans le module Clients";
        }
        return "First, you must affect a pricing to your client in Clients module";
    }
    
}
