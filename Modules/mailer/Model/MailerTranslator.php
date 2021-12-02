<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class MailerTranslator {

    public static function mailer($lang) {
        if ($lang == "fr") {
            return "Courriels";
        }
        return "Mailer";
    }

    public static function mailerConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Mailer permet d'envoyer des emails";
        }
        return "The Mailer module allows to send emails";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Mailer\"";
        }
        return "Mailer configuration";
    }

    public static function Install_Repair_database($lang = "") {
        if ($lang == "fr") {
            return "Installer/Réparer la base de données";
        }
        return "Install/Repair database";
    }

    public static function Install_Txt($lang = "") {
        if ($lang == "fr") {
            return "Cliquer sur \"Installer\" pour installer ou réparer la base de données de 'Mailer'.
					Cela créera les tables qui n'existent pas";
        }
        return "To repair the Mailer mudule, click \"Install\". This will create the
				Mailer tables in the database if they don't exists ";
    }

    public static function Activate_desactivate_menus($lang = "") {
        if ($lang == "fr") {
            return "Activer/désactiver les menus";
        }
        return "Activate/deactivate menus";
    }

    public static function From($lang = "") {
        if ($lang == "fr") {
            return "Expediteur";
        }
        return "From";
    }

    public static function To($lang = "") {
        if ($lang == "fr") {
            return "Destinataires";
        }
        return "To";
    }

    public static function Subject($lang = "") {
        if ($lang == "fr") {
            return "Objet";
        }
        return "Subject";
    }

    public static function Send($lang = "") {
        if ($lang == "fr") {
            return "Envoyer";
        }
        return "Send";
    }

    public static function Mailer_configuration($lang) {
        if ($lang == "fr") {
            return "Email configuration";
        }
        return "Mailer configuration";
    }

    public static function Content($lang) {
        if ($lang == "fr") {
            return "Contenu";
        }
        return "Content";
    }

    public static function Send_email($lang) {
        if ($lang == "fr") {
            return "Envoi d'email";
        }
        return "Send email";
    }

    public static function Message_Send($lang) {
        if ($lang == "fr") {
            return "Le message a bien été envoyé. Vous devriez en recevoir une copie par email.";
        }
        return "Message has been sent. You should receive a copy of this email in your email box";
    }

    public static function Message_Not_Send($lang) {
        if ($lang == "fr") {
            return "Le message n'a pa pu être envoyé. <br/> Erreur : ";
        }
        return "Message was not sent <br/>' . 'Mailer error: ";
    }

    public static function SendCopyToSender($lang) {
        if ($lang == "fr") {
            return "Envoyer une copie à la personne à l'origine de l'envoie de mail";
        }
        return "Send copy to sender";
    }
    
}
