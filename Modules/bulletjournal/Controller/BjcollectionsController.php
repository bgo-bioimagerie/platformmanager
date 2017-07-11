<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjCollection.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BjcollectionsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $model = new BjCollection();
        $collections = $model->getBySpace($id_space);
        
        $table = new TableView();
        $headers = array("name" => CoreTranslator::Name($lang));
        
        $table->addLineEditButton("bjcollectionsedit/".$id_space, "id");
        $table->addLineButton("bjcollectionsview/".$id_space, "id", BulletjournalTranslator::See($lang));
        $tableHtml = $table->view($collections, $headers);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "collections" => $collections,
              "tableHtml" => $tableHtml  ), "indexAction");
    }
    
    public function editAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $model = new BjCollection();
        $collection = $model->get($id);
        
        $form = new Form($this->request, "addCollectionForm");
        $form->setTitle(BulletjournalTranslator::Edit_collection($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $collection["name"]);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bjcollectionsedit/".$id_space."/".$id);
        $form->setButtonsWidth(2, 9);
        if($form->check()){
            $model->set($id, $id_space, $form->getParameter("name"));
            $this->redirect("bjcollections/".$id_space);
        }
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }
    
    public function viewAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // get the collection name
        $modelCollection = new BjCollection();
        $collection = $modelCollection->get($id);
        
        // select all the notes in this collection
        $model = new BjNote();
        $notes = $model->getforCollection($id);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "notes" => $notes, "collection" => $collection));
    }
}
