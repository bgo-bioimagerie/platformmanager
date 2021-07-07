<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsProduct.php';
require_once 'Modules/estore/Model/EsProductCategory.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstoreproductController extends CoresecureController {
    
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
    public function indexAction($id_space) {
        
        $this->model = new EsProduct($id_space);
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $dataArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("esproductedit/" . $id_space);
        $table->addDeleteButton("esproductdelete/" . $id_space);
        $tableHtml = $table->view($dataArray, array(
            "name" => CoreTranslator::Name($lang),
            "category" => EstoreTranslator::Category($lang),
            "url_image" => array("title" => EstoreTranslator::Image($lang), "type" => "image", "base_url" => "")
        ));

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit a product form
     */
    public function editAction($id_space, $id) {
        
        $this->model = new EsProduct($id_space);
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        $data = $this->model->get($id_space ,$id);
        
        $modelCategory = new EsProductCategory();
        $categories = $modelCategory->getForList($id_space);
        
        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(EstoreTranslator::Product($lang), 3);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addSelect("id_category", EstoreTranslator::Category($lang), $categories["names"], $categories["ids"], $data["id_category"]);
        $form->addUpload("image", EstoreTranslator::Image($lang));
        $form->addTextArea("description", EstoreTranslator::Description($lang), false, $data["description"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "esproductedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "esproduct/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            
            $id = $this->model->set($id, $id_space, 
                    $form->getParameter("id_category"), 
                    $form->getParameter("name"), 
                    $form->getParameter("description")
                    );
            
            $this->uploadImage($id_space, $id);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("esproductedit/" . $id_space . "/" . $id);
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
    
    public function uploadImage($id_space, $id) {
        $target_dir = "data/estore/images/";
        if ($_FILES["image"]["name"] != "") {
            $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            
            $url = $id_space . "_" . $id . "." . $ext;
            FileUpload::uploadFile($target_dir, "image", $url);
            
            $this->model->setImage($id_space ,$id, $target_dir . $url);
        }
    }
    
    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        
        $this->model = new EsProduct($id_space);
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        
        // query to delete the provider
        $this->model->delete($id_space ,$id);
        
        // after the provider is deleted we redirect to the providers list page
        $this->redirect("esproducts/" . $id_space);
    }
}
