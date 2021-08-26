<?php

require_once 'Framework/Model.php';
require_once 'Modules/Breeding/Model/BrSaleStatus.php';
require_once 'Modules/Breeding/Model/BrSale.php';

class BrMoves extends Model {

    public function __construct() {
        
    }

    public function getForBatch($id_batch, $lang) {

        $sql1 = "SELECT * FROM br_sale_items WHERE id_batch=? ORDER BY date ASC;";
        $sales = $this->runRequest($sql1, array($id_batch))->fetchAll();

        $sql2 = "SELECT * FROM br_losses WHERE id_batch=? ORDER BY date ASC;";
        $losses = $this->runRequest($sql2, array($id_batch))->fetchAll();

        // date, quantity, type (perte, vente), details (type perte, status), comment
        $data = array();

        $modelStatus = new BrSaleStatus();
        $modelSale = new BrSale();
        foreach ($sales as $sale) {
            
            $saleInfo = $modelSale->get($sale["id_sale"]);
           
            $data[] = array(
                "id" => "sale_" . $sale["id"],
                "date" => CoreTranslator::dateFromEn($sale["date"], $lang),
                "quantity" => $sale["quantity"],
                "type" => BreedingTranslator::Sale($lang),
                "details" => $modelStatus->getName($saleInfo["id_status"], $lang),
                "comment" => $sale["comment"]
            );
        }

        $modelLosseTypes = new BrLosseType();
        foreach ($losses as $losse) {
            $data[] = array(
                "id" => "losse_" . $losse["id"],
                "date" => CoreTranslator::dateFromEn($losse["date"], $lang),
                "quantity" => $losse["quantity"],
                "type" => BreedingTranslator::Losses($lang),
                "details" => $modelLosseTypes->getName($losse["id_type"], $lang),
                "comment" => $losse["comment"]
            );
        }

        /*
        print_r($data);
        if (count($data) > 0) {

            // sort
            foreach ($data as $key => $row) {
                $dates[$key] = $row['date'];
                $quantities[$key] = $row['quantity'];
            }

            return array_multisort($dates, SORT_ASC, $quantities, SORT_ASC, $data);
        } else {
            return array();
        }
         */
        return $data;
    }

}
