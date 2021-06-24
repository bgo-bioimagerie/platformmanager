<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesInstall.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ResourcesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
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

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "resources", "resources", "glyphicon-registration-mark", 
                    $this->request->getParameter("resourcesmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("displayColor")
                    );
            
            $this->redirect("resourcesconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang)
                );
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "resources");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "resources");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "resources");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("resourcesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("displayColor", CoreTranslator::color($lang), false, $displayColor);
        
        
        $form->setValidationButton(CoreTranslator::Save($lang), "resourcesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }


}