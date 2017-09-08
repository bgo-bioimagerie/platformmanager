<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/testapi/Model/TestapiTranslator.php';

require_once 'Modules/testapi/Model/TestapiPeople.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class TestapiApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("testapi");
    }

    public function testAction() {

        $name = $this->request->getParameter("username");
        $firstname = $this->request->getParameter("userfirstname");
        
        $message = "The database has successfully updated the values: " . $name . ", " . $firstname;
        echo json_encode(['message' => $message]);
    }

}
