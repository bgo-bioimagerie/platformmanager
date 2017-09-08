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
class CorenavbarApi extends Controller {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("testapi");
    }

    public function navbarAction() {

        $modulesModel = new CoreMenu();
        $toolMenu = $modulesModel->getMenus("name");
        
        for($i = 0 ; $i < count($toolMenu) ; $i++){
            $toolMenu[$i]["items"] = $modulesModel->getItemsFormMenu($toolMenu[$i]["id"]);
        }
        
        echo json_encode(['items' => $toolMenu]);
    }

}
