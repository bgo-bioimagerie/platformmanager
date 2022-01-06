<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 * Controller for the rating config page
 */
class RatingconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        
        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }

    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            
            $modelSpace->setSpaceMenu($id_space, "rating", "rating", "glyphicon-star", 
                    $this->request->getParameter("ratingmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    0,
                    $this->request->getParameter("colorMenu"),
                    $this->request->getParameter("colorTxtMenu")
                    );
            
            $this->redirect("ratingconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "rating");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "rating");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "rating");
        $colorTxtMenu = $modelSpace->getSpaceMenusTxtColor($id_space, "rating");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("ratingmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);
        $form->addColor("colorTxtMenu", CoreTranslator::color($lang), false, $colorTxtMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "ratingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang){
        $menucolor = $modelCoreConfig->getParamSpace("ratingmenucolor", $id_space);
        $menucolortxt = $modelCoreConfig->getParamSpace("ratingmenucolortxt", $id_space);
        
        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("ratingmenucolor", CoreTranslator::menu_color($lang), false, $menucolor);
        $form->addColor("ratingmenucolortxt", CoreTranslator::text_color($lang), false, $menucolortxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "ratingconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
}

?>