<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/ecosystem/Model/EcInstall.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class EcosystemconfigController extends CoresecureController {

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
        $modelCoreConfig = new CoreConfig();
        $formMenuColor = $this->menuColorForm($modelCoreConfig, $id_space, $lang);
        if ($formMenuColor->check()) {
            $modelCoreConfig->setParam("ecosystemmenucolor", $this->request->getParameter("ecosystemmenucolor"), $id_space);
            $modelCoreConfig->setParam("ecosystemmenucolortxt", $this->request->getParameter("ecosystemmenucolortxt"), $id_space);
            
            $this->redirect("ecosystemconfig/".$id_space);
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            
            $modelSpace->setSpaceMenu($id_space, "ecosystem", "ecusers", "glyphicon-user", $this->request->getParameter("usermenustatus"));
            
            $this->redirect("ecosystemconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formMenuColor->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "ecusers");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("usermenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "ecosystemconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $ecmenucolor = $modelCoreConfig->getParamSpace("ecosystemmenucolor", $id_space);
        $ecmenucolortxt = $modelCoreConfig->getParamSpace("ecosystemmenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(EcosystemTranslator::color($lang));
        $form->addColor("ecosystemmenucolor", EcosystemTranslator::menu_color($lang), false, $ecmenucolor);
        $form->addColor("ecosystemmenucolortxt", EcosystemTranslator::text_color($lang), false, $ecmenucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "ecosystemconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
}
