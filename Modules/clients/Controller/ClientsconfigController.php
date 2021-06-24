<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/clients/Model/ClientsInstall.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ClientsconfigController extends CoresecureController {

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

        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "clients", "clients", "glyphicon-credit-card", 
                    $this->request->getParameter("clientsmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("colorMenu")
                    );
            
            $modelAccess = new CoreSpaceAccessOptions();
            $modelAccess->set($id_space, "clientsuseraccounts", "clients", "clientsuseraccounts");
                
            $this->redirect("clientsconfig/".$id_space);
            return;
        }
        
        // menu name
        $menuNameForm = $this->menuName($lang, $id_space);
        if ($menuNameForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("clientsMenuName", $this->request->getParameter("clientsMenuName"), $id_space);
            $this->redirect("clientsconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $menuNameForm->getHtml($lang)
            );
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "clients");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "clients");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "clients");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("clientsmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "clientsconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function menuName($lang, $id_space) {
        $modelConfig = new CoreConfig();
        $menuName = $modelConfig->getParamSpace("clientsMenuName", $id_space);

        $form = new Form($this->request, "clientsMenuNameForm");
        $form->addSeparator(ClientsTranslator::MenuName($lang));

        $form->addText("clientsMenuName", CoreTranslator::Name($lang), false, $menuName);

        $form->setValidationButton(CoreTranslator::Save($lang), "clientsconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
