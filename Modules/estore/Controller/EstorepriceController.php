<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsProduct.php';
require_once 'Modules/estore/Model/EsProductCategory.php';
require_once 'Modules/estore/Model/EsProductUnitQ.php';
require_once 'Modules/estore/Model/EsPrice.php';


require_once 'Modules/clients/Model/ClPricing.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstorepriceController extends CoresecureController {
    
        /**
     * User model object
     */
    private $priceModel;
    
    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->priceModel = new EsPrice();
        $_SESSION["openedNav"] = "estore";
    }
    
     /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $data = $this->priceModel->getAll($id_space);
       
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "unit_quantity" => EstoreTranslator::UnitQuantity($lang)
        );
        $modelPricing = new ClPricing();
        $pricings = $modelPricing->getAll($id_space);
        foreach($pricings as $p){
            $headers["pricing_" . $p["id"]] = $p["name"];
        }

        $table = new TableView();
        $table->setTitle(EstoreTranslator::Prices($lang));
        $table->addLineEditButton("espriceedit/" . $id_space, "id");
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
    public function editAction($id_space, $id_product) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();
        // default empy provider
        $modelPricing = new ClPricing();
        $pricings = $modelPricing->getAll($id_space);
        
        $modelUnitQuantity = new EsProductUnitQ();
        $unitQuantity = $modelUnitQuantity->getquantity($id_space ,$id_product);
        
        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(EstoreTranslator::Edit_Price($lang) . ": " . $this->priceModel->getProductFullName($id_space ,$id_product), 3);
        
        $form->addNumber("unit_quantity", EstoreTranslator::UnitQuantity($lang), true, $unitQuantity);
        foreach($pricings as $p){
            $price = $this->priceModel->getPrice($id_space ,$id_product, $p["id"]);
            $form->addText("pricing_" . $p["id"], $p["name"], true, $price);
        }
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "espriceedit/" . $id_space . "/" . $id_product);
        $form->setButtonsWidth(4, 8);
        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $modelUnitQuantity->set($id_space, $id_product, $form->getParameter("unit_quantity"));
            foreach($pricings as $p){
                $this->priceModel->set($id_space, $id_product, $p["id"], $form->getParameter("pricing_" . $p["id"]));
            }
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("esprices/" . $id_space);
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

}
