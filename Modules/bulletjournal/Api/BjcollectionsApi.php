
<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';
require_once 'Modules/bulletjournal/Model/BjCollectionNote.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BjcollectionsApi extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function notecollectionsAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        //$lang = $this->getLanguage();

        $modelCollection = new BjCollectionNote();
        $collections = $modelCollection->getNoteCollections($idSpace, $id);

        $model = new BjNote();
        $noteName = $model->getName($idSpace, $id);

        $data = array("collections" => $collections, "id_note" => $id, "noteName" => $noteName);
        echo json_encode($data);
    }
}
