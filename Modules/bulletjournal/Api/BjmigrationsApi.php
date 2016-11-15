<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/Bjnote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BjmigrationsApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function migratetaskAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        //$lang = $this->getLanguage();

        $model = new BjTask();
        $model->migrate($id);
        
        $data = array("id" => $id);
        echo json_encode($data);
    }
}
