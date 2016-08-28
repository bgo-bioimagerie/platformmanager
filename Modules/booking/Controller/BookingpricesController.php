<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/ecosystem/Model/EcBelonging.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';

require_once 'Modules/booking/Model/BkPrice.php';
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
    public function __construct() {
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelBelonging = new EcBelonging();
        $modelPrice = new BkPrice();
        $modelResource = new ResourceInfo();
        $modelPNightWe = new BkNightWE();
        $modelPackage = new BkPackage();
        $resources = $modelResource->getForSpace($id_space);
        $belongings = $modelBelonging->getBelongings("name");
        $prices = array();
        $count = -1;
        for($i = 0 ; $i < count($resources) ; $i++){
            $count++;
            // day
            for($b = 0 ; $b < count($belongings) ; $b++){
                $prices[$b][$count] = $modelPrice->getDayPrice($resources[$i]["id"], $belongings[$b]["id"]);
            }
            $resourcesIds[$count] = $resources[$i]["id"] . "_day";
            $resourcesNames[$count] = $resources[$i]["name"];
            // add night we
            $isNight = $modelPNightWe->isNight($resources[$i]["id"]);
            if ($isNight){
                $count++;
                for($b = 0 ; $b < count($belongings) ; $b++){
                    $prices[$b][$count] = $modelPrice->getNightPrice($resources[$i]["id"], $belongings[$b]["id"]);
                }
                $resourcesIds[$count] = $resources[$i]["id"] . "_night";
                $resourcesNames[$count] = $resources[$i]["name"] . " " . BookingTranslator::night($lang);
            }
            $isWe = $modelPNightWe->isWe($resources[$i]["id"]);
            if ($isWe){
                $count++;
                for($b = 0 ; $b < count($belongings) ; $b++){
                    $prices[$b][$count] = $modelPrice->getWePrice($resources[$i]["id"], $belongings[$b]["id"]);
                }
                $resourcesIds[$count] = $resources[$i]["id"] . "_we";
                $resourcesNames[$count] = $resources[$i]["name"] . " " . BookingTranslator::WE($lang);
            }
            
            // add forfaits
            $packages = $modelPackage->getByResource($resources[$i]["id"]);
            foreach($packages as $package){
                $count++;
                for($b = 0 ; $b < count($belongings) ; $b++){
                    $prices[$b][$count] = $modelPrice->getPackagePrice($package["id"], $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $resourcesIds[$count] = $resources[$i]["id"] . "_pk_" . $package["id"];
                $resourcesNames[$count] = $resources[$i]["name"] . " " . $package["name"];
            }
        }
        
        $form = new Form($this->request, "bookingPricesForm");
        $form->setTitle(BookingTranslator::Prices($lang));
        
        $formAdd = new FormAdd($this->request, "bookingPricesFormAdd");
        $formAdd->addHidden("id_resource", $resourcesIds);
        $formAdd->addText("name", ResourcesTranslator::resources($lang), $resourcesNames);
        for($b = 0 ; $b < count($belongings) ; $b++){
            $formAdd->addNumber("bel_".$belongings[$b]["id"], $belongings[$b]["name"], $prices[$b]);
        }
        
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingprices/".$id_space);
        
        $form->setFormAdd($formAdd);
        if ($form->check()){
            $id_resource = $this->request->getParameter("id_resource");
            //print_r($id_resource); echo "<br/>";
            for($b = 0 ; $b < count($belongings) ; $b++){
                $prices = $this->request->getParameter("bel_".$belongings[$b]["id"]);
                //print_r($prices);
                for($i = 0 ; $i < count($id_resource) ; $i++){
                    $residArray = explode("_", $id_resource[$i]);
                    if ($residArray[1] == "day"){
                        $modelPrice->setPriceDay($id_resource[$i], $belongings[$b]["id"], $prices[$i]);
                    }
                    else if($residArray[1] == "night"){
                        $modelPrice->setPriceNight($id_resource[$i], $belongings[$b]["id"], $prices[$i]);
                    }
                    else if($residArray[1] == "we"){
                        $modelPrice->setPriceWe($id_resource[$i], $belongings[$b]["id"], $prices[$i]);
                    }
                    else if($residArray[1] == "pk"){
                        $modelPrice->setPricePackage($id_resource[$i], $belongings[$b]["id"], $residArray[2], $prices[$i]);
                    }
                }
            }
            $this->redirect("bookingprices/".$id_space);
            return;
        }
       
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }
}
