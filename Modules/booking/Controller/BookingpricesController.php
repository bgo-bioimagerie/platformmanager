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
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingpricesController extends BookingsettingsController {

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space){
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelPricing = new ClPricing();
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getBySpace($id_space);
        
        // TODO: handle case where $belongings = null; Causes warnings in dev mode
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
            $isNight = $belongings ? $modelPNightWe->isNight($id_space, $belongings[0]["id"]) : null;
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
            $isWe = $belongings ? $modelPNightWe->isWe($id_space, $belongings[0]["id"]) : null;
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

}
