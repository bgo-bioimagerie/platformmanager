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

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CatalogviewController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace, $idCategory = 0)
    {
        $this->checkAuthorizationMenuSpace("catalog", $idSpace, $_SESSION["id_user"]);

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
        $useResources = $modelCoreConfig->getParamSpace("ca_use_resources", $idSpace);
        if ($useResources == 1) {
            $categories[count($categories)]["id"] = -13;
            $categories[count($categories) - 1]["name"] = CatalogTranslator::Resources($lang);
        }

        if ($idCategory == -12 || ($idCategory == 0 && $categories[0]["id"] == -12)) {
            $this->antibodiesAction($idSpace, $categories);
            return;
        }
        if ($idCategory == -13 || ($idCategory == 0 && $categories[0]["id"] == -13)) {
            $this->resourcesAction($idSpace, $categories);
            return;
        }

        // view
        $this->render(array("id_space" => $idSpace, "lang" => $lang,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $this->getLanguage(),
            'activeCategory' => $idCategory));
    }

    public function antibodiesAction($idSpace, $categories)
    {
        $this->checkAuthorizationMenuSpace("catalog", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelAntibody = new Anticorps();
        $entries = $modelAntibody->getAnticorpsInfoCatalog($idSpace);

        $statusModel = new Status();
        $status = $statusModel->getBySpace($idSpace);
        //print_r();
        // view
        $this->render(array(
            'id_space' => $idSpace,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $lang,
            'activeCategory' => -12,
            'status' => $status
                ), "antibodies");
    }

    public function resourcesAction($idSpace, $categories)
    {
        $this->checkAuthorizationMenuSpace("catalog", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelResources = new ResourceInfo();
        $resources = $modelResources->getBySpace($idSpace);

        $this->render(array(
            'id_space' => $idSpace,
            'categories' => $categories,
            'entries' => $resources,
            'lang' => $lang,
            'activeCategory' => -13,
        ), "resourcesAction");
    }
}
