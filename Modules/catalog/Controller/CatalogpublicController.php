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
 * @deprecated ??? see no related usage
 * @author sprigent
 * Controller for the home page
 */
class CatalogpublicController extends Controller {

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $idCategory = 0) {

        $lang = $this->getLanguage();

        // get all the categories
        $modelCategory = new CaCategory();
        $categories = $modelCategory->getAll($id_space);

        // get the entries
        if ($idCategory == 0 && count($categories) > 0) {
            $idCategory = $categories[0]["id"];
        }

        $modelEntry = new CaEntry();
        $entries = $modelEntry->getCategoryEntries($id_space, $idCategory);

        $modelCoreConfig = new CoreConfig();

        
        $useAntibodies = $modelCoreConfig->getParamSpace("ca_use_antibodies", $id_space);
        if ($useAntibodies == 1) {
            $categories[count($categories)]["id"] = -12;
            $categories[count($categories) - 1]["name"] = CatalogTranslator::Antibodies($lang);
        }
        
        if ($idCategory == -12 || ( $idCategory == 0 && $categories[0]["id"] == -12)) {
            return $this->antibodiesAction($id_space, $categories);
        }
        // header
        $pageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $id_space);
        $pageLogo = $modelCoreConfig->getParamSpace("CaPublicPageLogo", $id_space);
        
        // view
        $this->render(array("id_space" => $id_space, "lang" => $lang,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $this->getLanguage(),
            'activeCategory' => $idCategory,
            'pageTitle' => $pageTitle,    
            'pageLogo' => $pageLogo
         ));
    }

    public function antibodiesAction($id_space, $categories) {

        $lang = $this->getLanguage();

        $modelAntibody = new Anticorps();
        $entries = $modelAntibody->getAnticorpsInfoCatalog($id_space);

        $statusModel = new Status();
        $status = $statusModel->getBySpace($id_space);
        
        $modelCoreConfig = new CoreConfig();
        $pageTitle = $modelCoreConfig->getParamSpace("CaPublicPageTitle", $id_space);
        $pageLogo = $modelCoreConfig->getParamSpace("CaPublicPageLogo", $id_space);
        
        // view
        return $this->render(array(
            'id_space' => $id_space,
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
