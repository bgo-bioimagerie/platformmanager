<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SePrice.php';

require_once 'Modules/ecosystem/Model/EcBelonging.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';



/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicespricesController extends CoresecureController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelBelonging = new EcBelonging();
        $modelPrice = new SePrice();
        $services = $this->serviceModel->getAll($id_space);
        $belongings = $modelBelonging->getBelongings("name");
        $prices = array();
        for($i = 0 ; $i < count($services) ; $i++){
            //$tmp = array();
            for($b = 0 ; $b < count($belongings) ; $b++){
                $prices[$b][$i] = $modelPrice->getPrice($services[$i]["id"], $belongings[$b]["id"]);
            }
            //$prices[$b][$i] = $tmp;
            $servicesIds[] = $services[$i]["id"];
            $servicesNames[] = $services[$i]["name"];
        }
        
        $form = new Form($this->request, "servicesPricesForm");
        $form->setTitle(ServicesTranslator::Prices($lang));
        
        $formAdd = new FormAdd($this->request, "servicesPricesFormAdd");
        $formAdd->addHidden("id_service", $servicesIds);
        $formAdd->addText("name_service", ServicesTranslator::services($lang), $servicesNames);
        for($b = 0 ; $b < count($belongings) ; $b++){
            $formAdd->addNumber("bel_".$belongings[$b]["id"], $belongings[$b]["name"], $prices[$b]);
        }
        
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesprices/".$id_space);
        
        $form->setFormAdd($formAdd);
        if ($form->check()){
            $id_services = $this->request->getParameter("id_service");
            for($b = 0 ; $b < count($belongings) ; $b++){
                $prices = $this->request->getParameter("bel_".$belongings[$b]["id"]);
                for($i = 0 ; $i < count($id_services) ; $i++){
                    $modelPrice->setPrice($id_services[$i], $belongings[$b]["id"], $prices[$i]);
                }
            }
            $this->redirect("servicesprices/".$id_space);
            
            return;
        }
       
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }
}
