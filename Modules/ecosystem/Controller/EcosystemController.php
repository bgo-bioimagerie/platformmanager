<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class EcosystemController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("users/institutions");
    }

    public function navbar($id_space){
        $html = file_get_contents('Modules/ecosystem/View/Ecosystem/navbar.php');
        
        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Neww}}', CoreTranslator::Neww($lang), $html);
        $html = str_replace('{{Belongings}}', CoreTranslator::Belongings($lang), $html);
        $html = str_replace('{{Units}}', CoreTranslator::Units($lang), $html);
        $html = str_replace('{{Users}}', CoreTranslator::Users($lang), $html);
        $html = str_replace('{{Active}}', CoreTranslator::Active($lang), $html);
        $html = str_replace('{{Unactive}}', CoreTranslator::Unactive($lang), $html);
        $html = str_replace('{{Export}}', CoreTranslator::Export($lang), $html);
        $html = str_replace('{{Responsible}}', CoreTranslator::Responsible($lang), $html);
        $html = str_replace('{{ExportAll}}', CoreTranslator::ExportAll($lang), $html);
        
        return $html;

    }
}
