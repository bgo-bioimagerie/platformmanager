<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/documents/Model/DocumentsInstall.php';
require_once 'Modules/documents/Model/DocumentsTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DocumentsconfigController extends CoresecureController {

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
        // color menu form
        /*
        $modelCoreConfig = new CoreConfig();
        $formMenuColor = $this->menuColorForm($modelCoreConfig, $id_space, $lang);
        if ($formMenuColor->check()) {
            $modelCoreConfig->setParam("documentsmenucolor", $this->request->getParameter("documentsmenucolor"), $id_space);
            $modelCoreConfig->setParam("documentsmenucolortxt", $this->request->getParameter("documentsmenucolortxt"), $id_space);
            
            $this->redirect("documentsconfig/".$id_space);
            return;
        }
         */

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            
            $modelSpace->setSpaceMenu($id_space, "documents", "documents", "glyphicon-user", 
                    $this->request->getParameter("documentsmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    0,
                    $this->request->getParameter("colorMenu")
                    );
            
            $this->redirect("documentsconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "documents");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "documents");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "documents");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("documentsmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "documentsconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $menucolor = $modelCoreConfig->getParamSpace("documentsmenucolor", $id_space);
        $menucolortxt = $modelCoreConfig->getParamSpace("documentsmenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("documentsmenucolor", CoreTranslator::menu_color($lang), false, $menucolor);
        $form->addColor("documentsmenucolortxt", CoreTranslator::text_color($lang), false, $menucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "documentsconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
}
