<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/quote/Model/QuoteTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class QuoteController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("quote");
    }

    public function navbar($id_space) {
        $html = file_get_contents('Modules/quote/View/Quote/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Quotes}}', QuoteTranslator::Quotes($lang), $html);
        $html = str_replace('{{CreateExistingUserQuote}}', QuoteTranslator::CreateExistingUserQuote($lang), $html);
        $html = str_replace('{{CreateNewUserQuote}}', QuoteTranslator::CreateNewUserQuote($lang), $html);
        return $html;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }

}
