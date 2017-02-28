<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/Antibodies/Model/Tissus.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesApi extends CoresecureController {

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
    public function tissusAction($id_space, $id_tissus) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        
        $modelTissus = new Tissus();
        $data = $modelTissus->getTissusById($id_tissus);
        
        echo json_encode($data);
    }

}
