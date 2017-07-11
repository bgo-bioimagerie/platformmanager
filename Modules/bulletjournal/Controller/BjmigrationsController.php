<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BjmigrationsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $year, $month) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($month == "" || $month == 0) {
            $month = date('m', time());
        }
        if ($year == "" || $year == 0) {
            $year = date('Y', time());
        }
        
        $modelTask = new BjTask();
        $notes = $modelTask->openedForMigration($id_space, $year, $month);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "month" => $month,
            "year" => $year, "notes" => $notes,
                ), "indexAction");
    }

    public function monthbeforeAction($id_space, $year, $month){
        if( $month == 1){
            $year = $year-1;
            $month = 12;
        }
        else{
            $month = $month -1;
        }
        
        $this->indexAction($id_space, $year, $month);
    }
    
    public function monthafterAction($id_space, $year, $month){
        if( $month == 12){
            $year = $year+1;
            $month = 1;
        }
        else{
            $month = $month + 1;
        }
        
        $this->indexAction($id_space, $year, $month);
    }
}
