<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CatalogController extends CoresecureController {

    public function sideMenu() {

        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("catalogsettings", $id_space);
       
        $dataView = [
            'id_space' => $id_space,
            'title' => CatalogTranslator::Catalog_settings($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',
            'Categories' => CatalogTranslator::Categories($lang),
            'Prestations' => CatalogTranslator::Prestations($lang)
        ];
        return $this->twig->render("Modules/catalog/View/Catalog/navbar.twig", $dataView);
        
    }

    public function navbar($id_space) {
        $html = file_get_contents('Modules/catalog/View/Catalog/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Categories}}', CatalogTranslator::Categories($lang), $html);
        $html = str_replace('{{Prestations}}', CatalogTranslator::Prestations($lang), $html);

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("catalogsettings", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', CatalogTranslator::Catalog_settings($lang), $html);
        
        return $html;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("catalog", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }

}
