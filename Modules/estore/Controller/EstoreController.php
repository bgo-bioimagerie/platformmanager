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
        $html = str_replace('{{SalesEntered}}', EstoreTranslator::EnteredSale($lang), $html);
        $html = str_replace('{{SalesQuoted}}', EstoreTranslator::SalesQuoted($lang), $html);
        $html = str_replace('{{SalesInProgress}}', EstoreTranslator::SalesInProgress($lang), $html);
        $html = str_replace('{{SalesSent}}', EstoreTranslator::SalesSent($lang), $html);
        $html = str_replace('{{SalesCanceled}}', EstoreTranslator::SalesCanceled($lang), $html);
        $html = str_replace('{{SalesArchives}}', EstoreTranslator::SalesArchives($lang), $html);

        
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
        $html = $this->replaceNotification($id_space, '{{countEntered}}', EsSaleStatus::$Entered, $html);
        $html = $this->replaceNotification($id_space, '{{countInProgress}}', EsSaleStatus::$InProgress, $html);
        $html = $this->replaceNotification($id_space, '{{countQuoted}}', EsSaleStatus::$Quoted, $html);
        $html = $this->replaceNotification($id_space, '{{countSent}}', EsSaleStatus::$Sent, $html);
        $html = $this->replaceNotification($id_space, '{{countCanceled}}', EsSaleStatus::$Canceled, $html);
        $html = $this->replaceNotification($id_space, '{{countSold}}', EsSaleStatus::$Sold, $html);

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
