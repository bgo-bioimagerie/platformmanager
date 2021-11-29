<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/quote/Model/QuoteTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class QuoteController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("quote");
    }

    public function mainMenu() {
        $id_space = isset($this->args['id_space']) ? $this->args['id_space'] : null;
        if ($id_space) {
            $csc = new CoreSpaceController($this->request);
            return $csc->navbar($id_space);
        }
        return null;
    }

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("quote", $id_space);
        
        $dataView = [
            'id_space' => $id_space,
            'title' =>  QuoteTranslator::Quote($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',
            'Quotes' => QuoteTranslator::Quotes($lang),
            'CreateExistingUserQuote' => QuoteTranslator::CreateExistingUserQuote($lang),
            'CreateNewUserQuote}}' => QuoteTranslator::CreateNewUserQuote($lang)

        ];
        return $this->twig->render("Modules/quote/View/Quote/navbar.twig", $dataView);
    }

    public function navbar($id_space) {
        $html = file_get_contents('Modules/quote/View/Quote/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Quotes}}', QuoteTranslator::Quotes($lang), $html);
        $html = str_replace('{{CreateExistingUserQuote}}', QuoteTranslator::CreateExistingUserQuote($lang), $html);
        $html = str_replace('{{CreateNewUserQuote}}', QuoteTranslator::CreateNewUserQuote($lang), $html);

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("quote", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', QuoteTranslator::Quote($lang), $html);
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
