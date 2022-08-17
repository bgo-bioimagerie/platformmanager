<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/SeProject.php';

require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/clients/Model/ClPricing.php';



/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicespricesApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getpricesAction($id_space, $id_service) {
        
        $modelPrices = new SePrice();
        $modelPricing = new ClPricing();
        $modelService = new SeService();
        $belongings = $modelPricing->getAll($id_space);
        
        $data = array();
        
        $data['id_service'] = $id_service;
        $data['service'] = $modelService->getItemName($id_space, $id_service) ?? Constants::UNKNOWN;
        for($i = 0 ; $i < count($belongings) ; $i++){
            $price = $modelPrices->getPrice($id_space, $id_service, $belongings[$i]["id"]);
            $data['bel_'.$belongings[$i]['id']] = floatval($price); 
        }
        
        echo json_encode($data);
    }
}
