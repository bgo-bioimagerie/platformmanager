<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/breeding/Model/BreedingInstall.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BreedingconfigController extends CoresecureController {

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


            $modelSpace->setSpaceMenu($id_space, "breeding", "breeding", "glyphicon-leaf", $this->request->getParameter("breedingmenustatus"), $this->request->getParameter("displayMenu"), 1, $this->request->getParameter("colorMenu")
            );

            $this->redirect("breedingconfig/" . $id_space);
            return;
        }

        // menu name
        $menuNameForm = $this->menuName($lang, $id_space);
        if ($menuNameForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("breedingMenuName", $this->request->getParameter("breedingMenuName"), $id_space);
            $this->redirect("breedingconfig/" . $id_space);
            return;
        }

        // show navbar
        $showNavBarForm = $this->showNavBar($lang, $id_space);
        if ($showNavBarForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("showNavBarBreeding", $this->request->getParameter("showNavBar"), $id_space);
            $this->redirect("breedingconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $menuNameForm->getHtml($lang),
            $showNavBarForm->getHtml($lang)
        );

        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "breeding");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "breeding");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "breeding");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("breedingmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);

        $form->setValidationButton(CoreTranslator::Save($lang), "breedingconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menuName($lang, $id_space) {
        $modelConfig = new CoreConfig();
        $breedingMenuName = $modelConfig->getParamSpace("breedingMenuName", $id_space);

        $form = new Form($this->request, "breedingMenuNameForm");
        $form->addSeparator(BreedingTranslator::MenuName($lang));

        $form->addText("breedingMenuName", CoreTranslator::Name($lang), false, $breedingMenuName);

        $form->setValidationButton(CoreTranslator::Save($lang), "breedingconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function showNavBar($lang, $id_space) {
        $modelConfig = new CoreConfig();
        $showNavBar = $modelConfig->getParamSpace("showNavBarBreeding", $id_space);

        $form = new Form($this->request, "showNavBarForm");
        $form->addSeparator(BreedingTranslator::ShowNavBar($lang));

        $form->addSelect("showNavBar", CoreTranslator::Choice($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $showNavBar);

        $form->setValidationButton(CoreTranslator::Save($lang), "breedingconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
