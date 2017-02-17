<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesInstall.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ResourcesconfigController extends CoresecureController {

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
        //$modelCoreConfig = new CoreConfig();

        $modelSpace = new CoreSpace();
        // color menu form
        $modelCoreConfig = new CoreConfig();
        $formMenuColor = $this->menuColorForm($modelCoreConfig, $id_space, $lang);
        if ($formMenuColor->check()) {
            $modelCoreConfig->setParam("resourcesmenucolor", $this->request->getParameter("resourcesmenucolor"), $id_space);
            $modelCoreConfig->setParam("resourcesmenucolortxt", $this->request->getParameter("resourcesmenucolortxt"), $id_space);
            
            $this->redirect("resourcesconfig/".$id_space);
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "resources", "resources", "glyphicon-registration-mark", 
                    $this->request->getParameter("resourcesmenustatus"),
                    $this->request->getParameter("displayMenu"));
            
            $this->redirect("resourcesconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formMenuColor->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "resources");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "resources");
        
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("resourcesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $ecmenucolor = $modelCoreConfig->getParamSpace("resourcesmenucolor", $id_space);
        $ecmenucolortxt = $modelCoreConfig->getParamSpace("resourcesmenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("resourcesmenucolor", CoreTranslator::menu_color($lang), false, $ecmenucolor);
        $form->addColor("resourcesmenucolortxt", CoreTranslator::text_color($lang), false, $ecmenucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
}