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
class TestapiController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("testapi");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("testapi", $id_space, $_SESSION["id_user"]);

        $model = new TestapiPeople();
        $people = $model->get($id);
        
        $form = new Form($this->request, "testapiform", true);
        $form->setTitle("This is a test form");
        $form->addHidden("id", $id);
        $form->addText("username", "Name", false, $people["name"]);
        $form->addText("userfirstname", "Firstname", false, $people["firstname"]);
        
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton("Save", "apitestquery");
        
        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }
}
