<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ClientsController extends CoresecureController {

     /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function navbar($id_space){
        
        $lang = $this->getLanguage();

        $html = file_get_contents('Modules/clients/View/Clients/navbar.php');

        $html = str_replace('{{Clients}}', ClientsTranslator::Clients($lang), $html);
        $html = str_replace('{{Pricings}}', ClientsTranslator::Pricings($lang), $html);
        $html = str_replace('{{CompanyInfo}}', ClientsTranslator::CompanyInfo($lang), $html);

        $html = str_replace('{{id_space}}', $id_space, $html);
        
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("clients", $id_space);
        
        $modelConfig = new CoreConfig();
        $title = $modelConfig->getParamSpace("clientsMenuName", $id_space);
        if($title == ""){
            $title = ClientsTranslator::clients($lang);
        }
        
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', $title, $html);

        return $html;
    }
}
