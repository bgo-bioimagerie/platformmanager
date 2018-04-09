<?php

require_once 'Framework/Model.php';

class EsSaleStatus extends Model {

    public static $Feasibility = 1;
    public static $TodoQuote = 2;
    public static $QuoteSent = 3;
    public static $ToSendSale = 4;
    public static $Invoicing = 5;
    public static $PaymentPending = 6;
    public static $Ended = 7;
    public static $Canceled = 8;

    public function __construct() {
        
    }

    public function getAll($lang) {

        $status = array();
        $status[] = array("id" => 1, "name" => EstoreTranslator::Feasibility($lang));
        $status[] = array("id" => 2, "name" => EstoreTranslator::TodoQuote($lang));
        $status[] = array("id" => 3, "name" => EstoreTranslator::QuoteSent($lang));
        $status[] = array("id" => 4, "name" => EstoreTranslator::ToSendSale($lang));
        $status[] = array("id" => 5, "name" => EstoreTranslator::Invoicing($lang));
        $status[] = array("id" => 6, "name" => EstoreTranslator::PaymentPending($lang));
        $status[] = array("id" => 7, "name" => EstoreTranslator::Ended($lang));
        $status[] = array("id" => 8, "name" => EstoreTranslator::Canceled($lang));
    }

    public static function getName($id, $lang) {

        if ($id == 1) {
            return EstoreTranslator::Feasibility($lang);
        }
        if ($id == 2) {
            return EstoreTranslator::TodoQuote($lang);
        }
        if ($id == 3) {
            return EstoreTranslator::QuoteSent($lang);
        }
        if ($id == 4) {
            return EstoreTranslator::ToSendSale($lang);
        }
        if ($id == 5) {
            return EstoreTranslator::Invoicing($lang);
        }
        if ($id == 6) {
            return EstoreTranslator::PaymentPending($lang);
        }
        if ($id == 7) {
            return EstoreTranslator::Ended($lang);
        }
        if ($id == 8) {
            return EstoreTranslator::Canceled($lang);
        }
    }

    public function getForList($lang) {
        $names = array(
            EstoreTranslator::Feasibility($lang),
            EstoreTranslator::TodoQuote($lang),
            EstoreTranslator::QuoteSent($lang),
            EstoreTranslator::ToSendSale($lang),
            EstoreTranslator::Invoicing($lang),
            EstoreTranslator::PaymentPending($lang),
            EstoreTranslator::Ended($lang),
            EstoreTranslator::Canceled($lang)
        );

        $ids = array(1, 2, 3, 4, 5, 6, 7, 8);

        return array("names" => $names, "ids" => $ids);
    }

}
