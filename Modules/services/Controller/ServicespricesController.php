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
require_once 'Modules/services/Controller/ServicesController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ServicespricesController extends ServicesController
{
    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelBelonging = new ClPricing();
        $modelPrice = new SePrice();
        $services = $this->serviceModel->getAll($idSpace);
        $belongings = $modelBelonging->getAll($idSpace);

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Prices($lang), 3);

        $headers = array(
            "service" => ServicesTranslator::Service($lang)
        );
        for ($i = 0 ; $i < count($belongings) ; $i++) {
            $headers[$belongings[$i]["id"]] = $belongings[$i]["name"];
        }

        $prices = array();
        for ($i = 0 ; $i < count($services) ; $i++) {
            $data = array();
            for ($b = 0 ; $b < count($belongings) ; $b++) {
                $data[$belongings[$b]["id"]] = $modelPrice->getPrice($idSpace, $services[$i]["id"], $belongings[$b]["id"]);
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
        for ($b = 0 ; $b < count($belongings) ; $b++) {
            $form->addText('bel_'.$belongings[$b]['id'], $belongings[$b]['name'], true, 0);
        }

        $form->setValidationButton(CoreTranslator::Save($lang), "servicespriceseditquery/".$idSpace);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml,
                        'formedit' => $form->getHtml($lang), 'services' => $services,
                        'belongings' => $belongings));
    }

    public function editqueryAction($idSpace)
    {
        $modelPricing = new ClPricing();
        $modelPrice = new SePrice();
        $belongings = $modelPricing->getAll($idSpace);

        $id_service = $this->request->getParameter('service_id');

        foreach ($belongings as $belonging) {
            $price = $this->request->getParameter('bel_' . $belonging['id']);
            $modelPrice->setPrice($idSpace, $id_service, $belonging['id'], $price);
        }

        $this->redirect('servicesprices/' . $idSpace);
    }
}
