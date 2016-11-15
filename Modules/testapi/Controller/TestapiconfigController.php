<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/testapi/Model/TestapiInstall.php';
require_once 'Modules/testapi/Model/TestapiTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class TestapiconfigController extends CoresecureController {

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
            $modelCoreConfig->setParam("testapimenucolor", $this->request->getParameter("testapimenucolor"), $id_space);
            $modelCoreConfig->setParam("testapimenucolortxt", $this->request->getParameter("testapimenucolortxt"), $id_space);
            
            $this->redirect("testapiconfig/".$id_space);
            return;
        }

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            
            $modelSpace->setSpaceMenu($id_space, "testapi", "testapi", "glyphicon-user", $this->request->getParameter("testapimenustatus"));
            
            $this->redirect("testapiconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formMenuColor->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "testapi");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("testapimenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "testapiconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $menucolor = $modelCoreConfig->getParamSpace("testapimenucolor", $id_space);
        $menucolortxt = $modelCoreConfig->getParamSpace("testapimenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("testapimenucolor", CoreTranslator::menu_color($lang), false, $menucolor);
        $form->addColor("testapimenucolortxt", CoreTranslator::text_color($lang), false, $menucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "testapiconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
}
