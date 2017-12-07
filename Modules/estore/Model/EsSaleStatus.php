<?php

require_once 'Framework/Model.php';

class EsSaleStatus extends Model {

    public static $Entered = 1;
    public static $InProgress = 2;
    public static $Quoted = 3;
    public static $Sent = 4;
    public static $Sold = 5;
    public static $Canceled = 6;
    public static $Lost = 7;
    
    public function __construct() {
        
    }

    public function getAll($lang) {

        $status = array();
        $status[] = array("id" => 1, "name" => EstoreTranslator::Entered($lang));
        $status[] = array("id" => 2, "name" => EstoreTranslator::InProgress($lang));
        $status[] = array("id" => 3, "name" => EstoreTranslator::Quoted($lang));
        $status[] = array("id" => 4, "name" => EstoreTranslator::Sent($lang));
        $status[] = array("id" => 5, "name" => EstoreTranslator::Sold($lang));
        $status[] = array("id" => 6, "name" => EstoreTranslator::Canceled($lang));
        $status[] = array("id" => 7, "name" => EstoreTranslator::Lost($lang));
    }

    public function getName($id, $lang){
        if ($id == 1){
            return EstoreTranslator::Entered($lang);
        }
        if ($id == 2){
            return EstoreTranslator::InProgress($lang);
        }
        if ($id == 3){
            return EstoreTranslator::Quoted($lang);
        }
        if ($id == 4){
            return EstoreTranslator::Sent($lang);
        }
        if ($id == 5){
            return EstoreTranslator::Sold($lang);
        }
        if ($id == 6){
            return EstoreTranslator::Canceled($lang);
        }
        if ($id == 7){
            return EstoreTranslator::Lost($lang);
        }
    }
    
    public function getForList($lang) {
        $names = array(
            EstoreTranslator::Entered($lang),
            EstoreTranslator::InProgress($lang),
            EstoreTranslator::Quoted($lang),
            EstoreTranslator::Sent($lang),
            EstoreTranslator::Sold($lang),
            EstoreTranslator::Canceled($lang),
            EstoreTranslator::Lost($lang)
        );

        $ids = array(1, 2, 3, 4, 5, 6, 7);

        return array("names" => $names, "ids" => $ids);
    }

}
