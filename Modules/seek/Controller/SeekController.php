<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/seek/Model/SeekTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class SeekController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("seek");
    }
    
    public function navbar($id_space){
        return "";
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("seek", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        
        $modelConfig = new CoreConfig();
        $seekUrl = $modelConfig->getParamSpace("seekurl", $id_space);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "seekUrl" => $seekUrl));
    }
}
