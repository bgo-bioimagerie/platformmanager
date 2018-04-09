<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsSale.php';
require_once 'Modules/estore/Model/EsSaleStatus.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class EstoreController extends CoresecureController {

    
    protected $modelSales;
     /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->modelSales = new EsSale();
    }

    public function navbar($id_space){
                
       
        $lang = $this->getLanguage();

        $html = file_get_contents('Modules/estore/View/Estore/navbar.php');


        $html = str_replace('{{Sales}}', EstoreTranslator::Sales($lang), $html);
        $html = str_replace('{{Entered}}', EstoreTranslator::Entered($lang), $html);
        $html = str_replace('{{Feasibility}}', EstoreTranslator::Feasibility($lang), $html);
        $html = str_replace('{{TodoQuote}}', EstoreTranslator::TodoQuote($lang), $html);
        $html = str_replace('{{QuoteSent}}', EstoreTranslator::QuoteSent($lang), $html);
        $html = str_replace('{{ToSendSale}}', EstoreTranslator::ToSendSale($lang), $html);
        $html = str_replace('{{Invoicing}}', EstoreTranslator::Invoicing($lang), $html);
        $html = str_replace('{{PaymentPending}}', EstoreTranslator::PaymentPending($lang), $html);
        $html = str_replace('{{Ended}}', EstoreTranslator::Ended($lang), $html);
        $html = str_replace('{{Canceled}}', EstoreTranslator::Canceled($lang), $html);
        
        $html = str_replace('{{NotFeasibleReasons}}', EstoreTranslator::NotFeasibleReasons($lang), $html);
        $html = str_replace('{{CancelReasons}}', EstoreTranslator::CancelReasons($lang), $html);
        
        
        
        $html = str_replace('{{Products}}', EstoreTranslator::Products($lang), $html);
        $html = str_replace('{{CategoriesProduct}}', EstoreTranslator::CategoriesProduct($lang), $html);
        $html = str_replace('{{Prices}}', EstoreTranslator::Prices($lang), $html);
        
        $html = str_replace('{{OtherInfo}}', EstoreTranslator::OtherInfo($lang), $html);
        $html = str_replace('{{ContactTypes}}', EstoreTranslator::ContactTypes($lang), $html);
        $html = str_replace('{{Delivery}}', EstoreTranslator::Delivery($lang), $html);
        
        

        $html = str_replace('{{id_space}}', $id_space, $html);
        
        
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("estore", $id_space);
        
        $modelConfig = new CoreConfig();
        $title = $modelConfig->getParamSpace("estoreMenuName", $id_space);
        if($title == ""){
            $title = EstoreTranslator::estore($lang);
        }
        
        
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', $title, $html);
        
        // get the number of entered sales
        $html = $this->replaceNotification($id_space, '{{countFaisabilityList}}', EsSaleStatus::$Feasibility, $html);
        $html = $this->replaceNotification($id_space, '{{countTodoQuoteList}}', EsSaleStatus::$TodoQuote, $html);
        $html = $this->replaceNotification($id_space, '{{countQuoteSentList}}', EsSaleStatus::$QuoteSent, $html);
        $html = $this->replaceNotification($id_space, '{{countSendSaleList}}', EsSaleStatus::$ToSendSale, $html);
        $html = $this->replaceNotification($id_space, '{{countInvoicingList}}', EsSaleStatus::$Invoicing, $html);
        $html = $this->replaceNotification($id_space, '{{countPaymentPendingList}}', EsSaleStatus::$PaymentPending, $html);
        
        return $html;
    }
    
    protected function replaceNotification($id_space, $name, $value, $html){
        
        $countEntered = $this->modelSales->count($id_space, $value);
        $startDiv = "<div class=\"pm-space-menu-notification\">";
        $startDivNull = "<div class=\"pm-space-menu-notification-null\">";
        $endDiv = "</div>";
        
        if ($countEntered > 0){
            $html = str_replace($name, $startDiv.$countEntered.$endDiv, $html);
        }
        else{
            $html = str_replace($name, $startDivNull.$countEntered.$endDiv, $html);
        }
        return $html;
    }
}
