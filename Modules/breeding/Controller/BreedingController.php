<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BreedingController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();

        $html = file_get_contents('Modules/breeding/View/Breeding/navbar.php');


        $html = str_replace('{{Sales}}', BreedingTranslator::Sales($lang), $html);
        $html = str_replace('{{NewSale}}', BreedingTranslator::NewSale($lang), $html);
        $html = str_replace('{{SalesSent}}', BreedingTranslator::SalesSent($lang), $html);
        $html = str_replace('{{SalesInProgress}}', BreedingTranslator::SalesInProgress($lang), $html);
        $html = str_replace('{{SalesCanceled}}', BreedingTranslator::SalesCanceled($lang), $html);

        $html = str_replace('{{Batchs}}', BreedingTranslator::Batchs($lang), $html);
        $html = str_replace('{{NewBatch}}', BreedingTranslator::NewBatch($lang), $html);
        $html = str_replace('{{BatchsInProgress}}', BreedingTranslator::BatchsInProgress($lang), $html);
        $html = str_replace('{{BatchsArchives}}', BreedingTranslator::BatchsArchives($lang), $html);

        $html = str_replace('{{Products}}', BreedingTranslator::Products($lang), $html);
        $html = str_replace('{{CategoriesProduct}}', BreedingTranslator::CategoriesProduct($lang), $html);
        $html = str_replace('{{Prices}}', BreedingTranslator::Prices($lang), $html);
        

        $html = str_replace('{{id_space}}', $id_space, $html);

        $html = str_replace('{{Glossary}}', BreedingTranslator::Glossary($lang), $html);
        $html = str_replace('{{Pricings}}', BreedingTranslator::Pricings($lang), $html);
        $html = str_replace('{{Clients}}', BreedingTranslator::Clients($lang), $html);
        $html = str_replace('{{Delivery}}', BreedingTranslator::Delivery($lang), $html);
        $html = str_replace('{{CompanyInfo}}', BreedingTranslator::CompanyInfo($lang), $html);
        
        $html = str_replace('{{UsersInstitutions}}', BreedingTranslator::UsersInstitutions($lang), $html);
        $html = str_replace('{{ContactTypes}}', BreedingTranslator::ContactTypes($lang), $html);
        $html = str_replace('{{LosseTypes}}', BreedingTranslator::LosseTypes($lang), $html);

        
        
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("breeding", $id_space);
        
        $modelConfig = new CoreConfig();
        $title = $modelConfig->getParamSpace("breedingMenuName", $id_space);
        if($title == ""){
            $title = BreedingTranslator::breeding($lang);
        }
        
        
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', $title, $html);

        return $html;
    }

}
