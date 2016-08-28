<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/services/Model/ServicesInstall.php';
require_once 'Modules/services/Model/ServicesTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesconfigController extends CoresecureController {

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
        $modelCoreConfig = new CoreConfig();
        
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "services", "services", "glyphicon glyphicon-plus", $this->request->getParameter("servicesmenustatus"));
            
            $this->redirect("servicesconfig/".$id_space);
            
            return;
        }
        
        // color menu form
        $formMenuColor = $this->menuColorForm($modelCoreConfig, $id_space, $lang);
        if ($formMenuColor->check()) {
            $modelCoreConfig->setParam("servicesmenucolor", $this->request->getParameter("servicesmenucolor"), $id_space);
            $modelCoreConfig->setParam("servicesmenucolortxt", $this->request->getParameter("servicesmenucolortxt"), $id_space);
            
            $this->redirect("servicesconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formMenuColor->getHtml($lang), 
                        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($id_space, $lang) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "services");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("servicesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $ecmenucolor = $modelCoreConfig->getParamSpace("servicesmenucolor", $id_space);
        $ecmenucolortxt = $modelCoreConfig->getParamSpace("servicesmenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("servicesmenucolor", CoreTranslator::menu_color($lang), false, $ecmenucolor);
        $form->addColor("servicesmenucolortxt", CoreTranslator::text_color($lang), false, $ecmenucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }

}
