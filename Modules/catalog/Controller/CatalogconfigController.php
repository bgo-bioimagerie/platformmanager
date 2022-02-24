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

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // menu activation form
        $formMenusactivation = $this->menusactivationForm($id_space, 'catalog', $lang);
        
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'catalog', 'th-list');
            return $this->redirect("catalogconfig/" . $id_space);
        }

        $formSettingsMenusactivation = $this->menusactivationForm($id_space, 'catalogsettings', $lang);
        if ($formSettingsMenusactivation->check()) {
            $this->menusactivation($id_space, 'catalogsettings', 'th-list', 'catalog');
            return $this->redirect("catalogconfig/" . $id_space);
        }


        $formMenuName = $this->menuNameForm($id_space, 'catalog', $lang);
        if($formMenuName->check()){
            $this->setMenuName($id_space, 'catalog');
            return $this->redirect("catalogconfig/".$id_space);
        }
        $formSettingsMenuName = $this->menuNameForm($id_space, 'catalogsettings', $lang);
        if($formSettingsMenuName->check()){
            $this->setMenuName($id_space, 'catalogsettings');
            return $this->redirect("catalogconfig/".$id_space);
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
        $forms = array(
            $formMenusactivation->getHtml($lang),
            $formMenuName->getHtml($lang),
            $formSettingsMenusactivation->getHtml($lang),
            $formSettingsMenuName->getHtml($lang),
            $formUseAntibodies->getHtml($lang),
            $formUseResources->getHtml($lang),
            $formPublicPageHeader->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function antibodiesForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $ca_use_antibodies = $modelCoreConfig->getParamSpace("ca_use_antibodies", $id_space, 0);

        $form = new Form($this->request, "ca_use_antibodiesForm");
        $form->addSeparator(CatalogTranslator::Antibody($lang));

        $form->addSelect("ca_use_antibodies", CatalogTranslator::Antibody_plugin($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $ca_use_antibodies);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function resourcesForm($id_space, $lang) {
        $modelCoreConfig = new CoreConfig();
        $ca_use_resources = $modelCoreConfig->getParamSpace("ca_use_resources", $id_space, 0);

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
