<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SePrice.php';

require_once 'Modules/clients/Model/ClPricing.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

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
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
        $_SESSION["openedNav"] = "invoices";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelBelonging = new ClPricing();
        $modelPrice = new SePrice();
        $services = $this->serviceModel->getAll($id_space);
        $belongings = $modelBelonging->getAll($id_space);
        
        $table = new TableView();
        $table->setTitle(ServicesTranslator::Prices($lang), 3);
        
        $headers = array(
            "service" => ServicesTranslator::Service($lang)
        );
        for($i = 0 ; $i < count($belongings) ; $i++){
            $headers[$belongings[$i]["id"]] = $belongings[$i]["name"];
        }
        
        $prices = array();
        for($i = 0 ; $i < count($services) ; $i++){
            $data = array();
            for($b = 0 ; $b < count($belongings) ; $b++){
                $data[$belongings[$b]["id"]] = $modelPrice->getPrice($id_space ,$services[$i]["id"], $belongings[$b]["id"]);
                $data['service'] = $services[$i]['name'];
                $data['id_service'] = $services[$i]['id'];
            }
            $prices[] = $data;
        }
        
        $table->addLineEditButton('editentry', 'id_service', true);
        $tableHtml = $table->view($prices, $headers);
        
        $form = new Form($this->request, "servicesPricesForm");
        $form->setTitle(ServicesTranslator::Prices($lang), 3);
        $form->addHidden("service_id");
        $form->addText("service", ServicesTranslator::service($lang), false, "", false);
        for($b = 0 ; $b < count($belongings) ; $b++){
            $form->addText('bel_'.$belongings[$b]['id'], $belongings[$b]['name'], true, 0);
        }
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicespriceseditquery/".$id_space);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml,
                        'formedit' => $form->getHtml($lang), 'services' => $services,
                        'belongings' => $belongings));
    }
    
    public function editqueryAction($id_space){
        
        $modelPricing = new ClPricing();
        $modelPrice = new SePrice();
        $belongings = $modelPricing->getAll($id_space);
        
        $id_service = $this->request->getParameter('service_id');
        
        foreach($belongings as $belonging){
            $price = $this->request->getParameter('bel_' . $belonging['id']);
            $modelPrice->setPrice($id_space ,$id_service, $belonging['id'], $price);
        }
        
        $this->redirect('servicesprices/' . $id_space);
    }
}
