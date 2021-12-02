<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Errors.php';


require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/catalog/Model/CatalogInstall.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';

require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CatalogconfigController extends CoresecureController {

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

        // menu activation form
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace = new CoreSpace();
            $modelSpace->setSpaceMenu($id_space, "catalog", "catalog", "glyphicon glyphicon-th-list", $this->request->getParameter("catalogmenustatus"), $this->request->getParameter("displayCatalogMenu"), 0, $this->request->getParameter("colorCatalogMenu"), $this->request->getParameter("colorTxtCatalogMenu")
            );
            $modelSpace->setSpaceMenu($id_space, "catalog", "catalogsettings", "glyphicon glyphicon-th-list", $this->request->getParameter("catalogsettingsmenustatus"), $this->request->getParameter("displaySettingsMenu"), 1, $this->request->getParameter("colorSettingsMenu"),$this->request->getParameter("colorTxtSettingsMenu")
            );

            $this->redirect("catalogconfig/" . $id_space);
            return;
        }

        $formMenuName = $this->menuNameForm($id_space, $lang);
        if ($formMenuName->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("catalogmenuname", $this->request->getParameter("catalogmenuname"), $id_space);

            $this->redirect("catalogconfig/" . $id_space);
            return;
        }

        $formUseAntibodies = $this->antibodiesForm($id_space, $lang);
        if ($formUseAntibodies->check()) {
            $modelConfig = new CoreConfig();
            $antibody_pluginR = $this->request->getParameterNoException("ca_use_antibodies");
            $modelConfig->setParam("ca_use_antibodies", $antibody_pluginR, $id_space);
        }
        $formUseResources = $this->resourcesForm($id_space, $lang);
        if ($formUseResources->check()) {
            $modelConfig = new CoreConfig();
            $resources_pluginR = $this->request->getParameterNoException("ca_use_resources");
            $modelConfig->setParam("ca_use_resources", $resources_pluginR, $id_space);
        }

        $formPublicPageHeader = $this->publicPageHeaderForm($id_space, $lang);
        if ($formPublicPageHeader->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("CaPublicPageTitle", $this->request->getParameter("CaPublicPageTitle"), $id_space);

            $target_dir = "data/catalog/logos/";
            if ($_FILES["CaPublicPageLogo"]["name"] != "") {
                $ext = pathinfo($_FILES["CaPublicPageLogo"]["name"], PATHINFO_EXTENSION);
                FileUpload::uploadFile($target_dir, "CaPublicPageLogo", $id_space . "." . $ext);
                $modelConfig->setParam("CaPublicPageLogo", $target_dir . $id_space . "." . $ext, $id_space);
            }  
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formUseAntibodies->getHtml($lang),
            $formUseResources->getHtml($lang),
            $formPublicPageHeader->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($id_space, $lang) {

        $modelMenu = new CoreSpace();
        $statusCatalogMenu = $modelMenu->getSpaceMenusRole($id_space, "catalog");
        $displayCatalogMenu = $modelMenu->getSpaceMenusDisplay($id_space, "catalog");
        $colorCatalogMenu = $modelMenu->getSpaceMenusColor($id_space, "catalog");
        $colorTxtCatalogMenu = $modelMenu->getSpaceMenusTxtColor($id_space, "catalog");

        $statusSettingsMenu = $modelMenu->getSpaceMenusRole($id_space, "catalogsettings");
        $displaySettingsMenu = $modelMenu->getSpaceMenusDisplay($id_space, "catalogsettings");
        $colorSettingsMenu = $modelMenu->getSpaceMenusColor($id_space, "catalogsettings");
        $colorTxtSettingsMenu = $modelMenu->getSpaceMenusTxtColor($id_space, "catalogsettings");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_modules($lang));

        $modelStatus = new CoreSpace();
        $status = $modelStatus->roles($lang);

        $status["names"][] = CoreTranslator::Unactive($lang);
        $status["ids"][] = 0;

        $form->addSelect("catalogmenustatus", CatalogTranslator::Catalog($lang), $status["names"], $status["ids"], $statusCatalogMenu);
        $form->addNumber("displayCatalogMenu", CoreTranslator::Display_order($lang), false, $displayCatalogMenu);
        $form->addColor("colorCatalogMenu", CoreTranslator::color($lang), false, $colorCatalogMenu);
        $form->addColor("colorTxtCatalogMenu", CoreTranslator::text_color($lang), false, $colorTxtCatalogMenu);

        $form->addSelect("catalogsettingsmenustatus", CatalogTranslator::Catalog_settings($lang), $status["names"], $status["ids"], $statusSettingsMenu);
        $form->addNumber("displaySettingsMenu", CoreTranslator::Display_order($lang), false, $displaySettingsMenu);
        $form->addColor("colorSettingsMenu", CoreTranslator::color($lang), false, $colorSettingsMenu);
        $form->addColor("colorTxtSettingsMenu", CoreTranslator::text_color($lang), false, $colorTxtSettingsMenu);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function menuNameForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $catalogmenuname = $modelCoreConfig->getParam("catalogmenuname", $id_space);

        $form = new Form($this->request, "catalogmenunameForm");
        $form->addSeparator(CoreTranslator::ModuleName($lang));

        $form->addText("catalogmenuname", CoreTranslator::Name($lang), false, $catalogmenuname);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function antibodiesForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $ca_use_antibodies = $modelCoreConfig->getParam("ca_use_antibodies", $id_space);

        $form = new Form($this->request, "ca_use_antibodiesForm");
        $form->addSeparator(CatalogTranslator::Antibody($lang));

        $form->addSelect("ca_use_antibodies", CatalogTranslator::Antibody_plugin($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $ca_use_antibodies);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function resourcesForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $ca_use_resources = $modelCoreConfig->getParam("ca_use_resources", $id_space);

        $form = new Form($this->request, "ca_use_resourcesForm");
        $form->addSeparator(CatalogTranslator::Resources($lang));

        $form->addSelect("ca_use_resources", CatalogTranslator::Resources_plugin($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $ca_use_resources);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    protected function publicPageHeaderForm($id_space, $lang) {

        $modelCoreConfig = new CoreConfig();
        $CaPublicPageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $id_space);

        $form = new Form($this->request, "publicPageHeaderForm");
        $form->addSeparator(CatalogTranslator::PublicPageHeader($lang));

        $form->addText("CaPublicPageTitle", CatalogTranslator::Title($lang), false, $CaPublicPageTitle);
        $form->addUpload("CaPublicPageLogo", CatalogTranslator::Logo($lang));

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
