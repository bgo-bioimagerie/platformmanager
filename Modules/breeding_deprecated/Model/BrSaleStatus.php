<?php

require_once 'Framework/Model.php';

class BrSaleStatus extends Model {

    public function __construct() {
        
    }

    public function getAll($lang) {

        $status = array();
        $status[] = array("id" => 1, "name" => BreedingTranslator::Entered($lang));
        $status[] = array("id" => 2, "name" => BreedingTranslator::InProgress($lang));
        $status[] = array("id" => 3, "name" => BreedingTranslator::Sold($lang));
        $status[] = array("id" => 4, "name" => BreedingTranslator::Canceled($lang));
        $status[] = array("id" => 5, "name" => BreedingTranslator::Lost($lang));
    }

    public function getName($id, $lang){
        if ($id == 1){
            return BreedingTranslator::Entered($lang);
        }
        if ($id == 2){
            return BreedingTranslator::InProgress($lang);
        }
        if ($id == 3){
            return BreedingTranslator::Sold($lang);
        }
        if ($id == 4){
            return BreedingTranslator::Canceled($lang);
        }
        if ($id == 5){
            return BreedingTranslator::Lost($lang);
        }
    }
    
    public function getForList($lang) {
        $names = array(
            BreedingTranslator::Entered($lang),
            BreedingTranslator::InProgress($lang),
            BreedingTranslator::Sold($lang),
            BreedingTranslator::Canceled($lang),
            BreedingTranslator::Lost($lang)
        );

        $ids = array(1, 2, 3, 4, 5);

        return array("names" => $names, "ids" => $ids);
    }

}
