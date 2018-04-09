<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/estore/Model/EstoreInstall.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class EstoreconfigController extends CoresecureController {

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


            $modelSpace->setSpaceMenu($id_space, "estore", "estore", "glyphicon-shopping-cart", $this->request->getParameter("estoremenustatus"), $this->request->getParameter("displayMenu"), 1, $this->request->getParameter("colorMenu")
            );

            $this->redirect("estoreconfig/" . $id_space);
            return;
        }

        // menu name
        $menuNameForm = $this->menuName($lang, $id_space);
        if ($menuNameForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("estoreMenuName", $this->request->getParameter("estoreMenuName"), $id_space);
            $this->redirect("estoreconfig/" . $id_space);
            return;
        }
        
        //
        $productForm = $this->menusproductclassForm($lang, $id_space);
        if ($productForm->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("estoreProductClass", $this->request->getParameter("estoreProductClass"), $id_space);
            $modelConfig->setParam("estoreProductCategoryClass", $this->request->getParameter("estoreProductCategoryClass"), $id_space);
            $this->redirect("estoreconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $menuNameForm->getHtml($lang),
            $productForm->getHtml($lang)
        );

        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "estore");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "estore");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "estore");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("estoremenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);

        $form->setValidationButton(CoreTranslator::Save($lang), "estoreconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menuName($lang, $id_space) {
        $modelConfig = new CoreConfig();
        $menuName = $modelConfig->getParamSpace("estoreMenuName", $id_space);

        $form = new Form($this->request, "estoreMenuNameForm");
        $form->addSeparator(EstoreTranslator::MenuName($lang));

        $form->addText("estoreMenuName", CoreTranslator::Name($lang), false, $menuName);

        $form->setValidationButton(CoreTranslator::Save($lang), "estoreconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menusproductclassForm($lang, $id_space) {

        $productList = Configuration::get("estoreproductclass");
        $productCategoryList = Configuration::get("estoreproductCategoryclass");

        $modelConfig = new CoreConfig();
        $estoreProductClass = $modelConfig->getParamSpace("estoreProductClass", $id_space);
        $estoreProductCategoryClass = $modelConfig->getParamSpace("estoreProductCategoryClass", $id_space);

        $form = new Form($this->request, "menusproductclassForm");
        $form->addSelect("estoreProductClass", EstoreTranslator::Product($lang), $productList, $productList, $estoreProductClass);
        $form->addSelect("estoreProductCategoryClass", EstoreTranslator::ProductCategories($lang), $productCategoryList, $productCategoryList, $estoreProductCategoryClass);

        $form->setValidationButton(CoreTranslator::Save($lang), "estoreconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
