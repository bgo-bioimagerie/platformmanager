<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class CatalogTranslator {

    public static function catalog($lang) {
        if ($lang == "fr") {
            return "Catalogue";
        }
        return "Catalog";
    }

    public static function catalogConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Catalog permet d'afficher les 'prestation' disponibles";
        }
        return "The Catalog module allows to show a catalog";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Catalog\"";
        }
        return "Catalog configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Catalog'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Catalog mudule, click \"Install\". This will create the
				Catalog tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Catalog_settings($lang = "") {
        if ($lang == "fr") {
            return "Gestion du catalog";
        }
        return "Catalog manager";
    }

    public static function catalogsettings($lang = "") {
        if ($lang == "fr") {
            return "Gestion du catalog";
        }
        return "Catalog manager";
    }

    public static function Prestations($lang = "") {
        if ($lang == "fr") {
            return "Prestations";
        }
        return "Prestations";
    }

    public static function Catalog_management($lang) {
        if ($lang == "fr") {
            return "Gestion du catalogue";
        }
        return "Catalog management";
    }

    public static function Categories($lang) {
        if ($lang == "fr") {
            return "Catégories";
        }
        return "Categories";
    }

    public static function Category($lang) {
        if ($lang == "fr") {
            return "Catégorie";
        }
        return "Category";
    }

    public static function Entries($lang) {
        if ($lang == "fr") {
            return "Prestations";
        }
        return "Entries";
    }

    public static function Entry($lang) {
        if ($lang == "fr") {
            return "Prestation";
        }
        return "Entry";
    }

    public static function Short_desc($lang) {
        if ($lang == "fr") {
            return "Description courte";
        }
        return "Short description";
    }

    public static function Full_desc($lang) {
        if ($lang == "fr") {
            return "Description complète";
        }
        return "Full description";
    }

    public static function Title($lang) {
        if ($lang == "fr") {
            return "Titre";
        }
        return "Title";
    }

    public static function Illustration($lang) {
        if ($lang == "fr") {
            return "Illustration";
        }
        return "Illustration";
    }

    public static function Plugins($lang) {
        if ($lang == "fr") {
            return "Plugins";
        }
        return "Plugins";
    }

    public static function Antibody_plugin($lang) {
        if ($lang == "fr") {
            return "Plugin anticorps";
        }
        return "antibody plugin";
    }

    public static function Enabled($lang) {
        if ($lang == "fr") {
            return "Activé";
        }
        return "Enabled";
    }

    public static function Unabled($lang) {
        if ($lang == "fr") {
            return "Désactivé";
        }
        return "Unabled";
    }

    public static function Antibodies($lang) {
        if ($lang == "fr") {
            return "Anticorps";
        }
        return "Antibodies";
    }

    public static function Provider($lang) {
        if ($lang == "fr") {
            return "Fournisseur";
        }
        return "Provider";
    }

    public static function Name($lang) {
        if ($lang == "fr") {
            return "Nom";
        }
        return "Name";
    }

    public static function Reference($lang) {
        if ($lang == "fr") {
            return "Référence";
        }
        return "Reference";
    }

    public static function Spices($lang) {
        if ($lang == "fr") {
            return "Espèce";
        }
        return "Spices";
    }

    public static function Comment($lang) {
        if ($lang == "fr") {
            return "Commentaire";
        }
        return "Comment";
    }

    public static function Antibody($lang) {
        if ($lang == "fr") {
            return "Anticorps";
        }
        return "Antibody";
    }

    public static function Ranking($lang) {
        if ($lang == "fr") {
            return "Classement";
        }
        return "Ranking";
    }

    public static function Application($lang) {
        if ($lang == "fr") {
            return "Voie de signalisation";
        }
        return "Signaling pathway";
    }

    public static function Staining($lang) {
        if ($lang == "fr") {
            return "Marquage";
        }
        return "Marker";
    }

    public static function Status($lang) {
        if ($lang == "fr") {
            return "Statut";
        }
        return "Status";
    }

    public static function Sample($lang) {
        if ($lang == "fr") {
            return "prélevement";
        }
        return "Sample";
    }

    public static function importMessage($lang) {
        if ($lang == "fr") {
            return "Importer tout les anticorps de la base de donnée vers le catalogue ?";
        }
        return "Are you sure you want to import all the antibodies from antibodies database to the catalog ?";
    }

    public static function Source($lang) {
        if ($lang == "fr") {
            return "Source";
        }
        return "Source";
    }
    
    public static function Logo($lang) {
        if ($lang == "fr") {
            return "Logo";
        }
        return "Logo";
    }
    
    public static function PublicPageHeader($lang) {
        if ($lang == "fr") {
            return "Entête de la page publique";
        }
        return "Public page header";
    }
    
    public static function Image($lang) {
        if ($lang == "fr") {
            return "Image";
        }
        return "Image";
    }

}
