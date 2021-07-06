<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/clients/Model/ClPricing.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';

require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingpricesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space){
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelPricing = new ClPricing();
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getBySpace($id_space);
        
        $belongings = $modelPricing->getAll($id_space);
        
        $table = new TableView();
        
        $modelConfig = new CoreConfig();
        $bookingmenuname = $modelConfig->getParamSpace("bookingmenuname", $id_space);
        
        $table->setTitle(BookingTranslator::Prices($lang) . " " . $bookingmenuname, 3);
        
        $headers = array(
            "resource" => ResourcesTranslator::Resource($lang)
        );
        for($i = 0 ; $i < count($belongings) ; $i++){
            $headers[$belongings[$i]["id"]] = $belongings[$i]["name"];
        }
        
        $prices = array();
        $modelPrice = new BkPrice();
        $modelPNightWe = new BkNightWE();
        $modelPackage = new BkPackage();
        $count = 0;
        $data = array();
        for ($i = 0; $i < count($resources); $i++) {
            
            $count++;
            // day
            for ($b = 0; $b < count($belongings); $b++) {
                $data[$belongings[$b]["id"]] = $modelPrice->getDayPrice($id_space, $resources[$i]["id"], $belongings[$b]["id"]);
            }
            $data['id_resource'] = $resources[$i]["id"] . "-day";
            $data['resource'] = $resources[$i]["name"];
            $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
            $prices[] = $data;
            // add night we
            $isNight = $modelPNightWe->isNight($id_space, $belongings[0]["id"]);
            if ($isNight) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getNightPrice($id_space, $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-night";
                $data['resource'] = $resources[$i]["name"] . " " . BookingTranslator::night($lang);
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }
            $isWe = $modelPNightWe->isWe($id_space, $belongings[0]["id"]);
            if ($isWe) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getWePrice($id_space, $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-we";
                $data['resource'] = $resources[$i]["name"] . " " . BookingTranslator::WE($lang);
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }

            // add forfaits
            $packages = $modelPackage->getByResource($id_space, $resources[$i]["id"]);
            foreach ($packages as $package) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getPackagePrice($id_space, $package["id"], $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-pk-" . $package["id"];
                $data['resource'] = $resources[$i]["name"] . " " . $package["name"];
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }
        }
        
        $table->addLineEditButton('editentry', 'id_resource', true);
        $tableHtml = $table->view($prices, $headers);
        
        $form = new Form($this->request, "resourcesPricesForm");
        $form->setTitle(BookingTranslator::Prices($lang), 3);
        $form->addHidden("resource_id");
        $form->addText("resource", ResourcesTranslator::resource($lang), false, "", false);
        for($b = 0 ; $b < count($belongings) ; $b++){
            $form->addText('bel_'.$belongings[$b]['id'], $belongings[$b]['name'], true, 0);
        }
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingpriceseditquery/".$id_space);
       
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml,
                        'formedit' => $form->getHtml($lang), 'resources' => $ress,
                        'belongings' => $belongings));
    }
    
    public function editqueryAction($id_space){
        
        $modelBelonging = new ClPricing();
        $modelPrice = new BkPrice();
        $belongings = $modelBelonging->getAll($id_space);
        
        $id_resource = $this->request->getParameter('resource_id');
        
        $residArray = explode("-", $id_resource);


        $resourceModel = new ResourceInfo();
        $res = $resourceModel->get($id_space, $residArray[0]);
        if(!$res) {
            Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id_resource]);
            throw new PfmAuthException('access denied for this resource', 403);
        }

        if ($residArray[1] == "day") {
            foreach($belongings as $bel){
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceDay($id_space, $residArray[0], $bel["id"], $price);
            }
        } else if ($residArray[1] == "night") {
            foreach($belongings as $bel){
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceNight($id_space, $residArray[0], $bel["id"], $price);
            }
        } else if ($residArray[1] == "we") {
            foreach($belongings as $bel){
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceWe($id_space, $residArray[0], $bel["id"], $price);
            }
        } else if ($residArray[1] == "pk") {
            foreach($belongings as $bel){
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPricePackage($id_space, $residArray[0], $bel['id'], $residArray[2], $price);
            }
        }
        
        $this->redirect('bookingprices/' . $id_space);
    }

    /**
     * @deprecated
     */

    public function ownerAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $resources = $this->getResourcesListing($id_space);
        $unitModel = new EcUnit();
        $units = $unitModel->getUnitsForList("name");
        
        $modelOwnerPrices = new BkOwnerPrice();
        $data = $modelOwnerPrices->getAll();
        $dataResources = array();
        $dataUnits = array();
        $dataPrice = array();
        foreach($data as $d){
            if ($d["id_package"] > 0){
               $dataResources[] =  $d["id_resource"] . "_pk_" . $d["id_package"];
            }
            else{
                $dataResources[] =  $d["id_resource"] . "_" . $d["day_night_we"];
            }
            $dataUnits[] = $d["id_unit"];
            $dataPrice[] = $d["price"];
        }

        $form = new Form($this->request, "bookingPricesForm");
        $form->setTitle(BookingTranslator::Prices($lang));

        $formAdd = new FormAdd($this->request, "bookingPricesFormAdd");
        $formAdd->addSelect("resource", ResourcesTranslator::resources($lang), $resources["names"], $resources["ids"], $dataResources);
        $formAdd->addSelect("unit", CoreTranslator::Units($lang), $units["names"], $units["ids"], $dataUnits);
        $formAdd->addNumber("price", InvoicesTranslator::Price_HT($lang), $dataPrice);

        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingpricesowner/" . $id_space);

        $form->setFormAdd($formAdd);
        if ($form->check()) {
            $modelPrice = new BkOwnerPrice();
            $id_resource = $this->request->getParameter("resource");
            $units = $this->request->getParameter("unit");
            $prices = $this->request->getParameter("price");
            
            $modelPrice->removeNotListed($id_resource, $units);
            
            for ($i = 0; $i < count($id_resource); $i++) {
                $residArray = explode("_", $id_resource[$i]);
                if ($residArray[1] == "day") {
                    $modelPrice->setPriceDay($id_resource[$i], $units[$i], $prices[$i]);
                } else if ($residArray[1] == "night") {
                    $modelPrice->setPriceNight($id_resource[$i], $units[$i], $prices[$i]);
                } else if ($residArray[1] == "we") {
                    $modelPrice->setPriceWe($id_resource[$i], $units[$i], $prices[$i]);
                } else if ($residArray[1] == "pk") {
                    $modelPrice->setPricePackage($id_resource[$i], $units[$i], $residArray[2], $prices[$i]);
                }
            }
            
            $this->redirect("bookingpricesowner/" . $id_space);
        }

        $this->render(array('lang' => $lang, "id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

    /**
     * @deprecated
     */
    protected function getResourcesListing($id_space) {

        $lang = $this->getLanguage();
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);
        $modelPNightWe = new BkNightWE();
        $modelPackage = new BkPackage();
        $modelBelonging = new EcBelonging();
        $belongings = $modelBelonging->getBelongings($id_space, "name");
        $count = -1;
        for ($i = 0; $i < count($resources); $i++) {
            $count++;
            // day
            $resourcesIds[$count] = $resources[$i]["id"] . "_day";
            $resourcesNames[$count] = $resources[$i]["name"];
            // add night we
            $isNight = $modelPNightWe->isNight($belongings[0]["id"]);
            if ($isNight) {
                $count++;
                $resourcesIds[$count] = $resources[$i]["id"] . "_night";
                $resourcesNames[$count] = $resources[$i]["name"] . " " . BookingTranslator::night($lang);
            }
            $isWe = $modelPNightWe->isWe($belongings[0]["id"]);
            if ($isWe) {
                $count++;
                $resourcesIds[$count] = $resources[$i]["id"] . "_we";
                $resourcesNames[$count] = $resources[$i]["name"] . " " . BookingTranslator::WE($lang);
            }

            // add forfaits
            $packages = $modelPackage->getByResource($resources[$i]["id"]);
            foreach ($packages as $package) {
                $count++;
                $resourcesIds[$count] = $resources[$i]["id"] . "_pk_" . $package["id"];
                $resourcesNames[$count] = $resources[$i]["name"] . " " . $package["name"];
            }
        }

        return array("ids" => $resourcesIds, "names" => $resourcesNames);
    }

}
