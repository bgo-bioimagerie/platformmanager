<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CatalogController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("catalog");
    }
    
        public function navbar($id_space){
        $html = file_get_contents('Modules/catalog/View/Catalog/navbar.php');
        
        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Categories}}', CatalogTranslator::Categories($lang), $html);
        $html = str_replace('{{Prestations}}', CatalogTranslator::Prestations($lang), $html);
        return $html;

    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("catalog", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }
}
