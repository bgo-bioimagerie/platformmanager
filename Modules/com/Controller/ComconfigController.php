<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/com/Model/ComInstall.php';
require_once 'Modules/com/Model/ComTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {
            
            $modelSpace->setSpaceMenu($id_space, "com", "com", "glyphicon-info-sign", 
                    $this->request->getParameter("commenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("commenucolor")
            );
            
            $this->redirect("comconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusComMenu = $modelSpace->getSpaceMenusRole($id_space, "com");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "com");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "com");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("commenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusComMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("commenucolor", CoreTranslator::color($lang), false, $displayColor);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "comconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
}
