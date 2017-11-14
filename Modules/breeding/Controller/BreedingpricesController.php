<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrPrice.php';
require_once 'Modules/breeding/Model/BrPricing.php';
require_once 'Modules/breeding/Model/BrProductUnitQ.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingpricesController extends CoresecureController {
    
    /**
     * User model object
     */
    private $pricingModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->priceModel = new BrPrice();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $data = $this->priceModel->getAll($id_space);
        
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "unit_quantity" => BreedingTranslator::UnitQuantity($lang)
        );
        $modelPricing = new BrPricing();
        $pricings = $modelPricing->getAll($id_space);
        foreach($pricings as $p){
            $headers["pricing_" . $p["id"]] = $p["name"];
        }

        
        $table = new TableView();
        $table->setTitle(BreedingTranslator::Prices($lang));
        $table->addLineEditButton("brpriceedit/" . $id_space, "id_product_stage");
        $tableHtml = $table->view($data, $headers);

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($id_space, $id_product_stage) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        $modelPricing = new BrPricing();
        $pricings = $modelPricing->getAll($id_space);
        
        $modelUnitQuantity = new BrProductUnitQ();
        $unitQuantity = $modelUnitQuantity->getquantity($id_product_stage);
        

        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::Edit_Price($lang) . ": " . $this->priceModel->getProductFullName($id_product_stage), 3);
        
        $form->addNumber("unit_quantity", BreedingTranslator::UnitQuantity($lang), true, $unitQuantity);
        foreach($pricings as $p){
            $price = $this->priceModel->getPrice($id_product_stage, $p["id"]);
            $form->addText("pricing_" . $p["id"], $p["name"], true, $price);
        }
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "brpriceedit/" . $id_space . "/" . $id_product_stage);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $modelUnitQuantity->set($id_space, $id_product_stage, $form->getParameter("unit_quantity"));
            foreach($pricings as $p){
                $this->priceModel->set($id_space, $id_product_stage, $p["id"], $form->getParameter("pricing_" . $p["id"]));
            }
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("brprices/" . $id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        
        // query to delete the provider
        $this->pricingModel->delete($id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brpricings/" . $id_space);
    }
}
