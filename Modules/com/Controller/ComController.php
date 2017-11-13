<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("com");
        $_SESSION["openedNav"] = "com";
    }

    public function indexAction($id_space) {
        $this->redirect('comtileedit/' . $id_space);
    }

    public function navbar($id_space) {
        $html = file_get_contents('Modules/com/View/Com/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Tilemessage}}', ComTranslator::Tilemessage($lang), $html);
        $html = str_replace('{{News}}', ComTranslator::News($lang), $html);

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("com", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ComTranslator::Com($lang), $html);

        return $html;
    }

}
