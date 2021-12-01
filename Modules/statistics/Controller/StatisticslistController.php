<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';
require_once 'Modules/statistics/Controller/StatisticsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class StatisticslistController extends StatisticsController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("statistics");
        $_SESSION["openedNav"] = "statistics";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("statistics", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }

}
