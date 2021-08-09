<?php


class HelpdeskTranslator {

    public static function helpdesk($lang) {
        if ($lang == "fr") {
            return "Helpdesk";
        }
        return "helpdesk";
    }

    public static function helpdeskConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Helpdesk permet de gérer les tickets utilisateurs";
        }
        return "The Helpdesk module allows to manage client tickets";
    }

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"Helpdesk\"";
        }
        return "Helpdesk configuration";
    }

    public static function MenuName($lang = "") {
        if ($lang == "fr") {
            return "Nom du menu";
        }
        return "Menu name";
    }

    public static function newTicket($lang = "") {
        if($lang == "fr") {
            return "Un niveau ticket est arrivé";
        }
        return "A new ticket has been received";
    }

    public static function updatedTicket($lang = "") {
        if($lang == "fr") {
            return "Le ticket a été modifié";
        }
        return "Ticket has been updated";
    }

    public static function reminderReachedTicket($lang="") {
        if($lang == "fr") {
            return "Le date d'expiration du ticket a été atteinte";
        }
        return "Reminder date has been reached";
    }

}
