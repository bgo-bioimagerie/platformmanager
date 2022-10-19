<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';

require_once 'Modules/catalog/Model/CaCategory.php';
require_once 'Modules/catalog/Model/CaEntry.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';

require_once 'Modules/antibodies/Model/Status.php';
require_once 'Modules/antibodies/Model/Anticorps.php';
/**
 * @author sprigent
 * Controller for the public catalog
 */
class CatalogpublicController extends Controller
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace, $idCategory = 0)
    {
        $lang = $this->getLanguage();

        // get all the categories
        $modelCategory = new CaCategory();
        $categories = $modelCategory->getAll($idSpace);

        // get the entries
        if ($idCategory == 0 && count($categories) > 0) {
            $idCategory = $categories[0]["id"];
        }

        $modelEntry = new CaEntry();
        $entries = $modelEntry->getCategoryEntries($idSpace, $idCategory);

        $modelCoreConfig = new CoreConfig();


        $useAntibodies = $modelCoreConfig->getParamSpace("ca_use_antibodies", $idSpace);
        if ($useAntibodies == 1) {
            $categories[count($categories)]["id"] = -12;
            $categories[count($categories) - 1]["name"] = CatalogTranslator::Antibodies($lang);
        }

        if ($idCategory == -12 || ($idCategory == 0 && $categories[0]["id"] == -12)) {
            return $this->antibodiesAction($idSpace, $categories);
        }
        // header
        $pageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $idSpace);
        $pageLogo = $modelCoreConfig->getParamSpace("CaPublicPageLogo", $idSpace);

        // view
        $this->render(array("id_space" => $idSpace, "lang" => $lang,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $this->getLanguage(),
            'activeCategory' => $idCategory,
            'pageTitle' => $pageTitle,
            'pageLogo' => $pageLogo
         ));
    }

    public function antibodiesAction($idSpace, $categories)
    {
        $lang = $this->getLanguage();

        $modelAntibody = new Anticorps();
        $entries = $modelAntibody->getAnticorpsInfoCatalog($idSpace);

        $statusModel = new Status();
        $status = $statusModel->getBySpace($idSpace);

        $modelCoreConfig = new CoreConfig();
        $pageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $idSpace);
        $pageLogo = $modelCoreConfig->getParamSpace("CaPublicPageLogo", $idSpace);

        // view
        return $this->render(array(
            'id_space' => $idSpace,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $lang,
            'activeCategory' => -12,
            'status' => $status,
            'pageTitle' => $pageTitle,
            'pageLogo' => $pageLogo
                ), "antibodies");
    }
}
