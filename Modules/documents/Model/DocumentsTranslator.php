<?php

/**
 * Class to translate the syggrif views
 *
 * @author sprigent
 *
 */
class DocumentsTranslator
{
    public static function documents($lang)
    {
        if ($lang == "fr") {
            return "documents";
        }
        return "documents";
    }

    public static function documentsConfigAbstract($lang)
    {
        if ($lang == "fr") {
            return "Le module Documents permet de partager des documents";
        }
        return "The Documents module allows to share documents";
    }

    public static function configuration($lang = "")
    {
        if ($lang == "fr") {
            return "Configuration de \"Documents\"";
        }
        return "Documents configuration";
    }

    public static function Install_Repair_database($lang = "")
    {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "")
    {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Documents'.
                    Cela créera les tables qui n'existent pas";
        }
        return "To repair the Documents mudule, click \"Install\". This will create the
                Documents tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "")
    {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Title($lang)
    {
        if ($lang == "fr") {
            return "Title";
        }
        return "Title";
    }

    public static function Owner($lang)
    {
        if ($lang == "fr") {
            return "Propriétaire";
        }
        return "Owner";
    }

    public static function LastModified($lang)
    {
        if ($lang == "fr") {
            return "Dernière modification";
        }
        return "Last modified";
    }

    public static function Open($lang)
    {
        if ($lang == "fr") {
            return "Ouvrir";
        }
        return "Open";
    }

    public static function Add_Doc($lang)
    {
        if ($lang == "fr") {
            return "Ajouter document";
        }
        return "Add document";
    }

    public static function File($lang)
    {
        if ($lang == "fr") {
            return "Fichier";
        }
        return "File";
    }

    public static function Edit_Document($lang)
    {
        if ($lang == "fr") {
            return "Modifier document";
        }
        return "Edit document";
    }

    public static function Missing_Document($lang)
    {
        if ($lang == "fr") {
            return "Fichier introuvable";
        }
        return "Missing file";
    }
}
