<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/template/Model/TemplateInstall.php';
require_once 'Modules/template/Model/TemplateTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class TemplateconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
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

            
            $modelSpace->setSpaceMenu($id_space, "template", "template", "glyphicon-user", 
                    $this->request->getParameter("templatemenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("colorMenu")
                    );
            
            $this->redirect("templateconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "template");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "template");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "template");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("templatemenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "templateconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
