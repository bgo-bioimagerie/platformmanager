<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/clients/Model/ClPricing.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingpricesApi extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getpricesAction($idSpace, $id_resource)
    {
        $lang = $this->getLanguage();
        $modelPrices = new BkPrice();
        $modelBelongings = new ClPricing();
        $modelResource = new ResourceInfo();
        $belongings = $modelBelongings->getAll($idSpace);
        $modelPackage = new BkPackage();

        $data = array();

        $residArray = explode("-", $id_resource);
        $resourceName = $modelResource->getName($idSpace, $residArray[0]);
        if ($residArray[1] == "day") {
            $data['resource'] = $resourceName;
            foreach ($belongings as $bel) {
                $data['bel_' . $bel['id']] = $modelPrices->getDayPrice($idSpace, $residArray[0], $bel["id"]);
            }
        } elseif ($residArray[1] == "night") {
            $data['resource'] = $resourceName . " " . BookingTranslator::night($lang);
            foreach ($belongings as $bel) {
                $data['bel_' . $bel['id']] = $modelPrices->getNightPrice($idSpace, $residArray[0], $bel["id"]);
            }
        } elseif ($residArray[1] == "we") {
            $data['resource'] = $resourceName . " " . BookingTranslator::WE($lang);
            foreach ($belongings as $bel) {
                $data['bel_' . $bel['id']] = $modelPrices->getWePrice($idSpace, $residArray[0], $bel["id"]);
            }
        } elseif ($residArray[1] == "pk") {
            $p = $modelPackage->getBySupID($idSpace, $residArray[2], $residArray[0]);
            $data['resource'] = $resourceName . " " . $p['name'];
            foreach ($belongings as $bel) {
                $data['bel_' . $bel['id']] = $modelPrices->getPackagePrice($idSpace, $residArray[2], $residArray[0], $bel["id"]);
            }
        }
        $data['id_resource'] = $id_resource;

        echo json_encode($data);
    }
}
