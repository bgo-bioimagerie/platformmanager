<?php

/**
 * Class to translate the syggrif views
 *
 * @author sprigent
 *
 */
class ComTranslator
{
    public static function com($lang)
    {
        if ($lang == "fr") {
            return "com";
        }
        return "com";
    }

    public static function comConfigAbstract($lang)
    {
        if ($lang == "fr") {
            return "Le module Com permet d'afficher des messages sur la page d'accueil";
        }
        return "The Com module allows to show messages in home page";
    }

    public static function configuration($lang = "")
    {
        if ($lang == "fr") {
            return "Configuration de \"Com\"";
        }
        return "Com configuration";
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
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Com'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Com mudule, click \"Install\". This will create the
				Com tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "")
    {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function Tilemessage($lang = "")
    {
        if ($lang == "fr") {
            return "Message accueil espace";
        }
        return "Space tile message";
    }

    public static function PrivateTilemessage($lang = "")
    {
        if ($lang == "fr") {
            return "Message d'accueil privé (visible uniquement par les membres de l'espace)";
        }
        return "Private tile message (only visible by space members)";
    }

    public static function PublicTilemessage($lang = "")
    {
        if ($lang == "fr") {
            return "Message d'accueil public";
        }
        return "Public tile message";
    }

    public static function News($lang)
    {
        if ($lang == "fr") {
            return "Actualités";
        }
        return "News";
    }

    public static function Title($lang)
    {
        if ($lang == "fr") {
            return "Titre";
        }
        return "Title";
    }

    public static function Date($lang)
    {
        if ($lang == "fr") {
            return "Date";
        }
        return "Date";
    }

    public static function Expire($lang)
    {
        if ($lang == "fr") {
            return "Expire";
        }
        return "Expire";
    }

    public static function NewsEdit($lang)
    {
        if ($lang == "fr") {
            return "Edition d'actualité";
        }
        return "News edit";
    }

    public static function Media($lang)
    {
        if ($lang == "fr") {
            return "Média";
        }
        return "Media";
    }

    public static function NewsHasBeenSaved($lang)
    {
        if ($lang == "fr") {
            return "L'actualité à été sauvegardée";
        }
        return "News has been saved";
    }

    public static function Content($lang)
    {
        if ($lang == "fr") {
            return "Contenu";
        }
        return "Content";
    }

    public static function useComAsSpaceHomePage($lang)
    {
        if ($lang == "fr") {
            return "Utiliser la page d'actualité comme accueil de l'espace";
        }
        return "use com as space home page";
    }

    public static function Twitter($lang)
    {
        if ($lang == "fr") {
            return "Twitter";
        }
        return "Twitter";
    }

    public static function UserTwitter($lang)
    {
        if ($lang == "fr") {
            return "Utiliser Twitter";
        }
        return "Use Twitter";
    }

    public static function AuthAccessToken($lang)
    {
        if ($lang == "fr") {
            return "Auth Access Token";
        }
        return "Auth Access Token";
    }

    public static function AuthAccessTokenSecret($lang)
    {
        if ($lang == "fr") {
            return "Auth Access Token Secret";
        }
        return "Auth Access Token Secret";
    }

    public static function ConsumerKey($lang)
    {
        if ($lang == "fr") {
            return "Consumer Key";
        }
        return "Consumer Key";
    }

    public static function ConsumerKeySecret($lang)
    {
        if ($lang == "fr") {
            return "Consumer Key Secret";
        }
        return "Consumer Key Secret";
    }
}
