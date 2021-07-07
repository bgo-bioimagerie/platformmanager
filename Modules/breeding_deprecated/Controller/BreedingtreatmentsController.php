<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrTreatment.php';
require_once 'Modules/breeding/Model/BrBatch.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingtreatmentsController extends CoresecureController {

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
        $this->model = new BrTreatment ();
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
        $table->addLineEditButton("brtreatmentedit/" . $id_space . "/" . $id_batch);
        $table->addDeleteButton("brtreatmentdelete/" . $id_space . "/" . $id_batch, "id", "date");
        $tableHtml = $table->view($providersArray, array(
            "date" => CoreTranslator::Date($lang),
            "antibiotic" => BreedingTranslator::Antibiotic($lang),
            "suppressor" => BreedingTranslator::Suppressor($lang),
            "water" => BreedingTranslator::Water($lang),
            "food" => BreedingTranslator::Food($lang),
            "comment" => BreedingTranslator::Comment($lang)
        ));

        // render the View
        $batch = $this->modelBatch->get($id_batch);
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml,
            'batch' => $batch,
            'activTab' => "treatments"
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
        $form->addText("antibiotic", BreedingTranslator::Antibiotic($lang), false, $data["antibiotic"]);
        $form->addText("suppressor", BreedingTranslator::Suppressor($lang), false, $data["suppressor"]);
        $form->addText("water", BreedingTranslator::Water($lang), false, $data["water"]);
        $form->addText("food", BreedingTranslator::Food($lang), false, $data["food"]);
        $form->addTextArea("comment", BreedingTranslator::Comment($lang), false, $data["comment"]);


        $form->setValidationButton(CoreTranslator::Ok($lang), "brtreatmentedit/" . $id_space . "/" . $id_batch . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "brtreatments/" . $id_space . "/" . $id_batch);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $this->model->set(
                    $id, $id_batch, CoreTranslator::dateToEn($form->getParameter("date"), $lang), $form->getParameter("antibiotic"), $form->getParameter("suppressor"), $form->getParameter("water"), $form->getParameter("food"), $form->getParameter("comment")
            );

            // after is saved we redirect to the list page
            $this->redirect("brtreatments/" . $id_space . "/" . $id_batch);
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
                'activTab' => "treatments"
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
        $this->redirect("brtreatments/" . $id_space . "/" . $id_batch);
    }

}
