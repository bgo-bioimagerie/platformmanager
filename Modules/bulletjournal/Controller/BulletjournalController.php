<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BulletjournalController extends CoresecureController {
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->redirect("bjnotes/".$id_space."/0/0");
    }
}
