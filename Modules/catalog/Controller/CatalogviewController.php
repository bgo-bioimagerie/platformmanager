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
 * 
 * @author sprigent
 * Controller for the home page
 */
class CatalogviewController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("catalog");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $idCategory = 0) {
        $this->checkAuthorizationMenuSpace("catalog", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get all the categories
        $modelCategory = new CaCategory();
        $categories = $modelCategory->getAll($id_space);

        // get the entries
        if ($idCategory == 0 && count($categories) > 0) {
            $idCategory = $categories[0]["id"];
        }

        $modelEntry = new CaEntry();
        $entries = $modelEntry->getCategoryEntries($idCategory);

        $modelCoreConfig = new CoreConfig();

        
        $useAntibodies = $modelCoreConfig->getParamSpace("ca_use_antibodies", $id_space);
        if ($useAntibodies == 1) {
            $categories[count($categories)]["id"] = -12;
            $categories[count($categories) - 1]["name"] = CatalogTranslator::Antibodies($lang);
        }

        if ($idCategory == -12 || ( $idCategory == 0 && $categories[0]["id"] == -12)) {
            $this->antibodiesAction($id_space, $categories);
            return;
        }
        // view
        $this->render(array("id_space" => $id_space, "lang" => $lang,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $this->getLanguage(),
            'activeCategory' => $idCategory));
    }

    public function antibodiesAction($id_space, $categories) {

        $lang = $this->getLanguage();

        $modelAntibody = new Anticorps();
        $entries = $modelAntibody->getAnticorpsInfoCatalog();

        $statusModel = new Status();
        $status = $statusModel->getStatus();
        //print_r();
        // view
        $this->render(array(
            'id_space' => $id_space,
            'categories' => $categories,
            'entries' => $entries,
            'lang' => $lang,
            'activeCategory' => -12,
            'status' => $status
                ), "antibodies");
    }

}
