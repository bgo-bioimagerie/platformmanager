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

}
