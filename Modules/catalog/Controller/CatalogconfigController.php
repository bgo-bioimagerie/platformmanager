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
class CatalogconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // menu activation form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'catalog', $lang);

        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'catalog', 'list');
            return $this->redirect("catalogconfig/" . $idSpace);
        }

        $formSettingsMenusactivation = $this->menusactivationForm($idSpace, 'catalogsettings', $lang);
        if ($formSettingsMenusactivation->check()) {
            $this->menusactivation($idSpace, 'catalogsettings', 'list', 'catalog');
            return $this->redirect("catalogconfig/" . $idSpace);
        }


        $formMenuName = $this->menuNameForm($idSpace, 'catalog', $lang);
        if ($formMenuName->check()) {
            $this->setMenuName($idSpace, 'catalog');
            return $this->redirect("catalogconfig/".$idSpace);
        }
        $formSettingsMenuName = $this->menuNameForm($idSpace, 'catalogsettings', $lang);
        if ($formSettingsMenuName->check()) {
            $this->setMenuName($idSpace, 'catalogsettings');
            return $this->redirect("catalogconfig/".$idSpace);
        }



        $formUseAntibodies = $this->antibodiesForm($idSpace, $lang);
        if ($formUseAntibodies->check()) {
            $modelConfig = new CoreConfig();
            $antibody_pluginR = $this->request->getParameterNoException("ca_use_antibodies");
            $modelConfig->setParam("ca_use_antibodies", $antibody_pluginR, $idSpace);
        }
        $formUseResources = $this->resourcesForm($idSpace, $lang);
        if ($formUseResources->check()) {
            $modelConfig = new CoreConfig();
            $resources_pluginR = $this->request->getParameterNoException("ca_use_resources");
            $modelConfig->setParam("ca_use_resources", $resources_pluginR, $idSpace);
        }

        $formPublicPageHeader = $this->publicPageHeaderForm($idSpace, $lang);
        if ($formPublicPageHeader->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("CaPublicPageTitle", $this->request->getParameter("CaPublicPageTitle"), $idSpace);

            $target_dir = "data/catalog/logos/";
            if ($_FILES["CaPublicPageLogo"]["name"] != "") {
                $ext = pathinfo($_FILES["CaPublicPageLogo"]["name"], PATHINFO_EXTENSION);
                FileUpload::uploadFile($target_dir, "CaPublicPageLogo", $idSpace . "." . $ext);
                $modelConfig->setParam("CaPublicPageLogo", $target_dir . $idSpace . "." . $ext, $idSpace);
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
        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }

    protected function antibodiesForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $ca_use_antibodies = $modelCoreConfig->getParamSpace("ca_use_antibodies", $idSpace, 0);

        $form = new Form($this->request, "ca_use_antibodiesForm");
        $form->addSeparator(CatalogTranslator::Antibody($lang));

        $form->addSelect("ca_use_antibodies", CatalogTranslator::Antibody_plugin($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $ca_use_antibodies);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $idSpace);


        return $form;
    }

    protected function resourcesForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $ca_use_resources = $modelCoreConfig->getParamSpace("ca_use_resources", $idSpace, 0);

        $form = new Form($this->request, "ca_use_resourcesForm");
        $form->addSeparator(CatalogTranslator::Resources($lang));

        $form->addSelect("ca_use_resources", CatalogTranslator::Resources_plugin($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $ca_use_resources);

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $idSpace);


        return $form;
    }

    protected function publicPageHeaderForm($idSpace, $lang)
    {
        $modelCoreConfig = new CoreConfig();
        $CaPublicPageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $idSpace);

        $form = new Form($this->request, "publicPageHeaderForm");
        $form->addSeparator(CatalogTranslator::PublicPageHeader($lang));

        $form->addText("CaPublicPageTitle", CatalogTranslator::Title($lang), false, $CaPublicPageTitle);
        $form->addUpload("CaPublicPageLogo", CatalogTranslator::Logo($lang));

        $form->setValidationButton(CoreTranslator::Save($lang), "catalogconfig/" . $idSpace);


        return $form;
    }
}
