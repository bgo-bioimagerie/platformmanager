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
}

?>
