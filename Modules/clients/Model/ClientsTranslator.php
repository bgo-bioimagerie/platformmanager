<?php

/**
 * Class to translate the syggrif views
 *
 * @author sprigent
 *
 */
class ClientsTranslator {

    public static function clients($lang) {
        if ($lang == "fr") {
            return "Comptes clients";
        }
        return "Clients";
    }

    public static function NewClient($lang) {
        if ($lang == "fr") {
            return "Nouveau compte";
        }
        return "New client";
    }

    public static function clientsConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Clients permet de gérer des comptes clients pour la facturation";
        }
        return "The Clients module allows to manage client accounts for invoicing";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Clients\"";
        }
        return "Clients configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Clients'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Clients mudule, click \"Install\". This will create the
				Clients tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Providers($lang) {
        if ($lang == "fr") {
            return "Fournisseurs";
        }
        return "Providers";
    }

    public static function NewProvider($lang) {
        if ($lang == "fr") {
            return "Nouveau";
        }
        return "New provider";
    }

    public static function Edit_Provider($lang) {
        if ($lang == "fr") {
            return "Edition d'un fournisseur";
        }
        return "Edit provider";
    }

    public static function MenuName($lang) {
        if ($lang == "fr") {
            return "Nom du menu";
        }
        return "Menu name";
    }

    public static function Pricing($lang) {
        if ($lang == "fr") {
            return "Secteur d'activité";
        }
        return "Pricing";
    }

    public static function Pricings($lang) {
        if ($lang == "fr") {
            return "Secteurs d'activité";
        }
        return "Pricings";
    }

    public static function Edit_Client($lang) {
        if ($lang == "fr") {
            return "Edition Client";
        }
        return "Edit Client";
    }

    public static function Client($lang = "") {
        if ($lang == "fr") {
            return "Client";
        }
        return "Client";
    }

    public static function ContactName($lang) {
        if ($lang == "fr") {
            return "Nom du contact";
        }
        return "Contact name";
    }

    public static function Institution($lang) {
        if ($lang == "fr") {
            return "Etablissement";
        }
        return "Institution";
    }

    public static function BuildingFloor($lang) {
        if ($lang == "fr") {
            return "Bâtiment / Etage";
        }
        return "Building / Floor";
    }

    public static function Address($lang) {
        if ($lang == "fr") {
            return "Adresse";
        }
        return "Address";
    }

    public static function Zip_code($lang) {
        if ($lang == "fr") {
            return "Code postal";
        }
        return "ZIP";
    }

    public static function City($lang) {
        if ($lang == "fr") {
            return "Ville";
        }
        return "City";
    }

    public static function Country($lang) {
        if ($lang == "fr") {
            return "Pays";
        }
        return "Country";
    }

    public static function Phone($lang) {
        if ($lang == "fr") {
            return "Téléphone";
        }
        return "Phone";
    }

    public static function Email($lang) {
        if ($lang == "fr") {
            return "Courriel";
        }
        return "Email";
    }

    public static function Service($lang) {
        if ($lang == "fr") {
            return "Service";
        }
        return "Service";
    }

    public static function CompanyInfo($lang) {
        if ($lang == "fr") {
            return "Informations société";
        }
        return "Company infos";
    }

    public static function Edit_Pricing($lang) {
        if ($lang == "fr") {
            return "Secteur d'activité";
        }
        return "Edit pricing";
    }

    public static function NewPricing($lang) {
        if ($lang == "fr") {
            return "Nouveau secteur d'activité";
        }
        return "New pricing";
    }

    public static function Name($lang) {
        if ($lang == "fr") {
            return "Nom";
        }
        return "Name";
    }

    public static function County($lang) {
        if ($lang == "fr") {
            return "Département";
        }
        return "County";
    }

    public static function Tel($lang) {
        if ($lang == "fr") {
            return "Téléphone";
        }
        return "Phone";
    }

    public static function Fax($lang) {
        if ($lang == "fr") {
            return "Télécopie";
        }
        return "Fax";
    }

    public static function Data_has_been_saved($lang) {
        if ($lang == "fr") {
            return "Les données on bien été sauvegardées";
        }
        return "Data has been saved";
    }

    public static function ApprovalNumber($lang) {
        if ($lang == "fr") {
            return "No agrément établissement";
        }
        return "Approval number";
    }

    public static function UsersForAccount($lang) {
        if ($lang == "fr") {
            return "Utilisateurs pour le compte client";
        }
        return "Users for account";
    }

    public static function ClientUsers($lang) {
        if ($lang == "fr") {
            return "Utilisateurs";
        }
        return "Client users";
    }

    public static function UserHasBeenAddedToClient($lang) {
        if ($lang == "fr") {
            return "L'utilisateur a bien été ajouté au compte client";
        }
        return "User has been added to client";
    }

    public static function UserHasBeenDeletedFromClient($lang) {
        if ($lang == "fr") {
            return "L'utilisateur a bien été supprimé du compte client";
        }
        return "User has been removed from client";
    }

    public static function AddressDelivery($lang) {
        if ($lang == "fr") {
            return "Adresse de livraison";
        }
        return "Address delivery";
    }

    public static function AddressInvoice($lang) {
        if ($lang == "fr") {
            return "Adresse de facturation";
        }
        return "Address invoice";
    }

    public static function Letter($lang) {
        if ($lang == "fr") {
            return "Courrier";
        }
        return "Letter";
    }

    public static function invoice_send_preference($lang) {
        if ($lang == "fr") {
            return "Préférence d'envoi facture";
        }
        return "invoice send preference";
    }

    public static function ClientAccount($lang) {
        if ($lang == "fr") {
            return "Compte client";
        }
        return "Client account";
    }

    public static function Identifier($lang) {
        if ($lang == "fr") {
            return "Identifiant";
        }
        return "Identifier";
    }

    public static function clientsuseraccounts($lang) {
        if ($lang == "fr") {
            return "Comptes clients";
        }
        return "Client accounts";
    }

    public static function ClientAccountsFor($lang) {
        if ($lang == "fr") {
            return "Comptes clients pour ";
        }
        return "Client accounts for ";
    }

    public static function addClientAccountFor($lang) {
        if ($lang == "fr") {
            return "Ajouter un compte clients pour ";
        }
        return "Add a client account for";
    }


}
