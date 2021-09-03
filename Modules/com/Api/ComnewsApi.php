<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/com/Model/ComNews.php';
require_once 'Modules/com/Model/ComTranslator.php';



/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComnewsApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getnewsAction($id_space) {
        $modelNews = new ComNews();
        $news = $modelNews->getByDate($id_space, 10);
        echo json_encode($news);
    }

}
