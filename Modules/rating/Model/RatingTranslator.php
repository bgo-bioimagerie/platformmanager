<?php

/**
 * Class to translate the rating views
 * 
 * @author sprigent
 *
 */
class RatingTranslator {

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de satisfaction client";
        }
        return "Customer satisfaction configuration";
    }

    public static function ratingConfigAbstract($lang) {
        if ($lang == "fr") {
            return "Le module Rating permet de noter la satisfaction client";
        }
        return "The Rating module allows to record customer satisfaction";
    }

    public static function rating($lang){
        if ($lang == "fr") {
            return "Satisfaction";
        }
        return "Satisfaction";
    }

    public static function Campaigns($lang){
        if ($lang == "fr") {
            return "Campagnes";
        }
        return "Campaigns";
    }

    public static function Campaign($lang){
        if ($lang == "fr") {
            return "Campagne";
        }
        return "Campaign";
    }

    public static function From($lang){
        if ($lang == "fr") {
            return "De";
        }
        return "From";
    }

    public static function To($lang){
        if ($lang == "fr") {
            return "à";
        }
        return "To";
    }

    public static function Deadline($lang){
        if ($lang == "fr") {
            return "Date butoir";
        }
        return "Deadline";
    }

    public static function CampaignSaved($lang){
        if ($lang == "fr") {
            return "Campagne mise à jour";
        }
        return "Campaign updated";
    }

    public static function CampaignStarted($lang){
        if ($lang == "fr") {
            return "Campagne démarrée";
        }
        return "Campaign started";
    }

    public static function Subject($lang){
        if ($lang == "fr") {
            return "Titre de la campagne";
        }
        return "Campaign subject";
    }

    public static function Survey($lang){
        if ($lang == "fr") {
            return "Questionnaire de satisfaction";
        }
        return "Satisfation survey";
    }
    
}

?>
