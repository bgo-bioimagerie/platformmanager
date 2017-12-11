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

/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstorecatalogController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "estore";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space, $id_category = 0) {

        $this->modelProducts = new EsProduct($id_space);
        $this->modelCategories = new EsProductCategory($id_space);


        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // get first category
        if ($id_category == 0) {
            $id_category = $this->modelCategories->getFirstId($id_space);
        }

        // Query to the database
        $categories = $this->modelCategories->getAll($id_space);
        $products = $this->modelProducts->getByCategory($id_category);
        $modelUnitQ = new EsProductUnitQ();
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]["unit_quantity"] = $modelUnitQ->getquantity($products[$i]["id"]);
        }


        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'categories' => $categories,
            'products' => $products,
            'id_category' => $id_category
        ));
    }

}
