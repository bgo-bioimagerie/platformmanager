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
 * @deprecated unused
 */
class BjcollectionsController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BjCollection();
        $collections = $model->getBySpace($idSpace);

        $table = new TableView();
        $headers = array("name" => CoreTranslator::Name($lang));

        $table->addLineEditButton("bjcollectionsedit/".$idSpace, "id");
        $table->addLineButton("bjcollectionsview/".$idSpace, "id", BulletjournalTranslator::See($lang));
        $tableHtml = $table->view($collections, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "collections" => $collections,
              "tableHtml" => $tableHtml  ), "indexAction");
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BjCollection();
        $collection = $model->get($idSpace, $id);

        $form = new Form($this->request, "addCollectionForm");
        $form->setTitle(BulletjournalTranslator::Edit_collection($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $collection["name"]);
        $form->setValidationButton(CoreTranslator::Ok($lang), "bjcollectionsedit/".$idSpace."/".$id);

        if ($form->check()) {
            $model->set($id, $idSpace, $form->getParameter("name"));
            $this->redirect("bjcollections/".$idSpace);
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function viewAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get the collection name
        $modelCollection = new BjCollection();
        $collection = $modelCollection->get($idSpace, $id);

        // select all the notes in this collection
        $model = new BjNote();
        $notes = $model->getforCollection($idSpace, $id);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "notes" => $notes, "collection" => $collection));
    }
}
