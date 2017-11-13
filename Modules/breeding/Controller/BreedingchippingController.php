<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrChipping.php';
require_once 'Modules/breeding/Model/BrBatch.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingchippingController extends CoresecureController {

    /**
     * User model object
     */
    private $model;
    private $modelBatch;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrChipping();
        $this->modelBatch = new BrBatch();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space, $id_batch) {

        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->model->getAll($id_batch);
        for($i = 0 ; $i < count($providersArray) ; $i++){
            $providersArray[$i]["date"] = CoreTranslator::dateFromEn($providersArray[$i]["date"], $lang);
        }
        

        $table = new TableView();
        $table->addLineEditButton("brchippingedit/" . $id_space . "/" . $id_batch);
        $table->addDeleteButton("brchippingdelete/" . $id_space . "/" . $id_batch, "id", "date");
        $tableHtml = $table->view($providersArray, array(
            "date" => CoreTranslator::Date($lang),
            "chip_number" => BreedingTranslator::ChipNumber($lang),
            "comment" => BreedingTranslator::Comment($lang)
        ));

        // render the View
        $batch = $this->modelBatch->get($id_batch);
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml,
            'batch' => $batch,
            'activTab' => "chipping"
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($id_space, $id_batch, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        $data = $this->model->get($id);


        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::Edit_Category($lang), 3);
        $form->addDate("date", CoreTranslator::Date($lang), false, CoreTranslator::dateFromEn($data["date"], $lang));
        $form->addText("chip_number", BreedingTranslator::ChipNumber($lang), false, $data["chip_number"]);
        $form->addTextArea("comment", BreedingTranslator::Comment($lang), false, $data["comment"]);


        $form->setValidationButton(CoreTranslator::Ok($lang), "brchippingedit/" . $id_space . "/" . $id_batch . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brchipping/" . $id_space . "/" . $id_batch);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->model->set(
                    $id, $id_batch, CoreTranslator::dateToEn($form->getParameter("date"), $lang), 
                    $form->getParameter("chip_number"),
                    $form->getParameter("comment")
            );

            // after is saved we redirect to the list page
            $this->redirect("brchipping/" . $id_space . "/" . $id_batch);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $batch = $this->modelBatch->get($id_batch);
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'batch' => $batch,
                'activTab' => "chipping"
            ));
        }
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id_batch, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);

        // query to delete the provider
        $this->model->delete($id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brchipping/" . $id_space . "/" . $id_batch);
    }

}
