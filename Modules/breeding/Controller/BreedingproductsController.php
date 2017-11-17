<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrProduct.php';
require_once 'Modules/breeding/Model/BrCategory.php';
require_once 'Modules/breeding/Model/BrProductStage.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingproductsController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrProduct ();
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
        $providersArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->addLineEditButton("brproductedit/" . $id_space);
        $table->addDeleteButton("brproductdelete/" . $id_space);
        $tableHtml = $table->view($providersArray, array(
            "name" => CoreTranslator::Name($lang),
            "code" => BreedingTranslator::Code($lang),
        ));

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
    public function editAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        $data = $this->model->get($id);

        $modelCategory = new BrCategory();
        $categories = $modelCategory->getForList($id_space);

        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::Edit_product($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", BreedingTranslator::Name($lang), true, $data["name"]);
        $form->addText("code", BreedingTranslator::Code($lang), false, $data["code"]);
        $form->addSelect("id_category", BreedingTranslator::Category($lang), $categories["names"], $categories["ids"], $data["id_category"]);
        $form->addTextArea("description", BreedingTranslator::Description($lang), true, $data["description"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "brproductedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brproducts/" . $id_space);
        $form->setButtonsWidth(4, 8);

        // table of stages
        $modelStages = new BrProductStage();
        $stages = $modelStages->getAll($id);
        $headers = array(
            "name" => BreedingTranslator::Name($lang),
            "starts" => BreedingTranslator::Start($lang),
            "ends" => BreedingTranslator::End($lang),
        );
        for ($i = 0; $i < count($stages); $i++) {
            $stages[$i]["starts"] = $stages[$i]["start_num"] . " " . $modelStages->getUnitName($stages[$i]["start_unit"], $lang);
            $stages[$i]["ends"] = $stages[$i]["end_num"] . " " . $modelStages->getUnitName($stages[$i]["end_unit"], $lang);
        }

        $table = new TableView();
        $table->addLineEditButton("brproductstageedit/" . $id_space . "/" . $id);
        $table->addDeleteButton("brproductstagedelete/" . $id_space . "/" . $id);
        $tableHtml = $table->view($stages, $headers);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->model->set(
                    $id, $id_space, $form->getParameter("name"), $form->getParameter("code"), $form->getParameter("description"), $form->getParameter("id_category")
            );

            // after the provider is saved we redirect to the providers list page
            $this->redirect("brproductedit/" . $id_space . "/" . $id);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $id_space,
                'id_product' => $id,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'tableHtml' => $tableHtml
            ));
        }
    }

    public function stageeditAction($id_space, $id_product, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // data
        $modelStage = new BrProductStage();
        $data = $modelStage->get($id);

        $units = $modelStage->getUnitsForList($lang);

        // form
        $form = new Form($this->request, "editstageActionForm");
        $form->setTitle(BreedingTranslator::EditStage($lang));
        $form->addText("name", BreedingTranslator::Name($lang), true, $data["name"]);
        $form->addNumber("display_order", BreedingTranslator::Order($lang), true, $data["display_order"]);
        $form->addText("start_num", BreedingTranslator::Start($lang), true, $data["start_num"]);
        $form->addSelect("start_unit", BreedingTranslator::Unit($lang), $units["names"], $units["ids"], $data["start_num"]);
        $form->addText("end_num", BreedingTranslator::End($lang), true, $data["end_num"]);
        $form->addSelect("end_unit", BreedingTranslator::Unit($lang), $units["names"], $units["ids"], $data["end_num"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "brproductstageedit/" . $id_space . "/" . $id_product . "/" . $id);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            
            $modelStage->set(
                    $id, $id_product, $form->getParameter("name"), $form->getParameter("display_order"), $form->getParameter("start_num"), $form->getParameter("start_unit"), $form->getParameter("end_num"), $form->getParameter("end_unit")
            );
            $this->redirect("brproductedit/" . $id_space . "/" . $id_product);
            return;
        }

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "id_product" => $id_product,
            "formHtml" => $form->getHtml($lang)
        ));
    }
    
    public function stagedeleteAction($id_space, $id_product, $id){
        
        $modelStage = new BrProductStage();
        $modelStage->delete($id);
        
        $this->redirect("brproductedit/".$id_space."/".$id_product);
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);

        // query to delete the provider
        $this->model->delete($id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brproducts/" . $id_space);
    }

}
