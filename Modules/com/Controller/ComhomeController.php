<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/com/Model/ComNews.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComhomeController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("com");
    }

    public function indexAction($id_space){
        
        $modelParam = new CoreConfig();
        $message = $modelParam->getParamSpace("tilemessage", $id_space);
        
        $modelNews = new ComNews();
        $news = $modelNews->getByDate($id_space, 10); /// \todo add a parameter here to get the limit number
        
        
        $this->render(array("id_space" => $id_space, "message" => $message, "news" => $news));
    }
    
}
