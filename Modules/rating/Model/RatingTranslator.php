<?php

/**
 * Class to translate the syggrif views
 * 
 * @author sprigent
 *
 */
class RatingTranslator {

    public static function configuration($lang = "") {
        if ($lang == "fr") {
            return "Configuration de \"tatisfaction client\"";
        }
        return "Customer satisfaction configuration";
    }
}

?>
