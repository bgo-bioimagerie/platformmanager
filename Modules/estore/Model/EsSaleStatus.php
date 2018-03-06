<?php

require_once 'Framework/Model.php';

class EsSaleStatus extends Model {

    public static $Entered = 1;
    public static $Feasibility = 2;
    public static $TodoQuote = 3;
    public static $QuoteSent = 4;
    public static $ToSendSale = 5;
    public static $Invoicing = 6;
    public static $PaymentPending = 7;
    public static $Ended = 8;
    public static $Canceled = 9;

    public function __construct() {
        
    }

    public function getAll($lang) {

        $status = array();
        $status[] = array("id" => 1, "name" => EstoreTranslator::Entered($lang));
        $status[] = array("id" => 2, "name" => EstoreTranslator::Feasibility($lang));
        $status[] = array("id" => 3, "name" => EstoreTranslator::TodoQuote($lang));
        $status[] = array("id" => 4, "name" => EstoreTranslator::QuoteSent($lang));
        $status[] = array("id" => 5, "name" => EstoreTranslator::ToSendSale($lang));
        $status[] = array("id" => 6, "name" => EstoreTranslator::Invoicing($lang));
        $status[] = array("id" => 7, "name" => EstoreTranslator::PaymentPending($lang));
        $status[] = array("id" => 8, "name" => EstoreTranslator::Ended($lang));
        $status[] = array("id" => 9, "name" => EstoreTranslator::Canceled($lang));
    }

    public function getName($id, $lang) {
        if ($id == 1) {
            return EstoreTranslator::Entered($lang);
        }
        if ($id == 2) {
            return EstoreTranslator::Feasibility($lang);
        }
        if ($id == 3) {
            return EstoreTranslator::TodoQuote($lang);
        }
        if ($id == 4) {
            return EstoreTranslator::QuoteSent($lang);
        }
        if ($id == 5) {
            return EstoreTranslator::ToSendSale($lang);
        }
        if ($id == 6) {
            return EstoreTranslator::Invoicing($lang);
        }
        if ($id == 7) {
            return EstoreTranslator::PaymentPending($lang);
        }
        if ($id == 8) {
            return EstoreTranslator::Ended($lang);
        }
        if ($id == 9) {
            return EstoreTranslator::Canceled($lang);
        }
    }

    public function getForList($lang) {
        $names = array(
            EstoreTranslator::Entered($lang),
            EstoreTranslator::Feasibility($lang),
            EstoreTranslator::TodoQuote($lang),
            EstoreTranslator::QuoteSent($lang),
            EstoreTranslator::ToSendSale($lang),
            EstoreTranslator::Invoicing($lang),
            EstoreTranslator::PaymentPending($lang),
            EstoreTranslator::Ended($lang),
            EstoreTranslator::Canceled($lang)
        );

        $ids = array(1, 2, 3, 4, 5, 6, 7, 8, 9);

        return array("names" => $names, "ids" => $ids);
    }

}
