<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrProduct.php';
require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/breeding/Form/BatchInfoForm.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingbatchsController extends CoresecureController {
    
    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrBatch ();
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
        $table->setTitle(BreedingTranslator::Batchs($lang), 3);
        $table->addLineEditButton("brbatchedit/" . $id_space);
        $table->addDeleteButton("brbatchdelete/" . $id_space, "id", "reference");
        $tableHtml = $table->view($providersArray, array(
            "reference" => BreedingTranslator::Reference($lang)
            ));

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    public function inprogressAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->model->getInProgress($id_space);

        $table = new TableView();
        $table->setTitle(BreedingTranslator::BatchsInProgress($lang), 3);
        $table->addLineEditButton("brbatchedit/" . $id_space);
        $table->addDeleteButton("brbatchdelete/" . $id_space, "id", "reference");
        $tableHtml = $table->view($providersArray, array(
            "reference" => BreedingTranslator::Reference($lang)
            ));

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }
    
        public function archivesAction($id_space) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $providersArray = $this->model->getArchives($id_space);

        $table = new TableView();
        $table->setTitle(BreedingTranslator::BatchsArchives($lang), 3);
        $table->addLineEditButton("brbatchedit/" . $id_space);
        $table->addDeleteButton("brbatchdelete/" . $id_space, "id", "reference");
        $tableHtml = $table->view($providersArray, array(
            "reference" => BreedingTranslator::Reference($lang)
            ));

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    public function newAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        $formGenerator = new BatchInfoForm($this->request, "BreedingBatchInfoForm", "brbatchnew/" . $id_space);
        $formGenerator->setLang($lang);
        $formGenerator->setTitle(BreedingTranslator::NewBatch($lang));
        $formGenerator->setSpace($id_space);
        $formGenerator->render();
        $form = $formGenerator->getForm();
        if ($form->check()) {
            $id = $formGenerator->save();
            
            $_SESSION["message"] = BreedingTranslator::Batch_has_been_saved($lang);
            $this->redirect("brbatch/" . $id_space . "/" . $id);
            return;
        }

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang)
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

        $data = $this->model->get($id);
        
        $formGenerator = new BatchInfoForm($this->request, "BreedingBatchInfoForm", "brbatch/" . $id_space);
        $formGenerator->setLang($lang);
        $formGenerator->setTitle(BreedingTranslator::Infos($lang));
        $formGenerator->setSpace($id_space);
        $formGenerator->setData($data);
        $formGenerator->render();
        $form = $formGenerator->getForm();
        if ($form->check()) {
            $id = $formGenerator->save();
            
            $_SESSION["message"] = BreedingTranslator::Batch_has_been_saved($lang);
            $this->redirect("brbatch/" . $id_space . "/" . $id);
            return;
        }

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'batch' => $data,
            'activTab' => "infos"
        ));
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
        $this->redirect("brbatchs/" . $id_space);
    }
}
