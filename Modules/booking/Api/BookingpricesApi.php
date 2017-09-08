<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/ecosystem/Model/EcBelonging.php';

require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPackage.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingpricesApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getpricesAction($id_space, $id_resource) {

        $lang = $this->getLanguage();
        $modelPrices = new BkPrice();
        $modelBelongings = new EcBelonging();
        $modelResource = new ResourceInfo();
        $belongings = $modelBelongings->getAll($id_space, "display_order");
        $modelPackage = new BkPackage();

        $data = array();

        $residArray = explode("-", $id_resource);
        $resourceName = $modelResource->getName($residArray[0]);
        if ($residArray[1] == "day") {
            $data['resource'] = $resourceName;
            foreach($belongings as $bel){
                $data['bel_' . $bel['id']] = $modelPrices->getDayPrice($residArray[0], $bel["id"]);
            }
        } else if ($residArray[1] == "night") {
            $data['resource'] = $resourceName . " " . BookingTranslator::night($lang);
            foreach($belongings as $bel){
                $data['bel_' . $bel['id']] = $modelPrices->getNightPrice($residArray[0], $bel["id"]);
            }
        } else if ($residArray[1] == "we") {
            $data['resource'] = $resourceName . " " . BookingTranslator::WE($lang);
            foreach($belongings as $bel){
                $data['bel_' . $bel['id']] = $modelPrices->getWePrice($residArray[0], $bel["id"]);
            }
        } else if ($residArray[1] == "pk") {
            $data['resource'] = $resourceName . " " . $modelPackage->getName($residArray[2]);
            foreach($belongings as $bel){
                $data['bel_' . $bel['id']] = $modelPrices->getPackagePrice($residArray[2], $residArray[0], $bel["id"]);
            }
        }
        $data['id_resource'] = $id_resource;

        echo json_encode($data);
    }

}
