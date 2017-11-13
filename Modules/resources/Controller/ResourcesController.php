<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReEvent.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Model/ReEventData.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';


require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ResourcesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("resources");
    }

    public function navbar($id_space) {
        
        $html = file_get_contents('Modules/resources/View/Resources/navbar.php');
        
        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Resources}}', ResourcesTranslator::Resources($lang), $html);
        $html = str_replace('{{Sorting}}', ResourcesTranslator::Sorting($lang), $html);
        $html = str_replace('{{Areas}}', ResourcesTranslator::Areas($lang), $html);
        $html = str_replace('{{Categories}}', ResourcesTranslator::Categories($lang), $html);
        $html = str_replace('{{Responsible}}', ResourcesTranslator::Responsible($lang), $html);
        $html = str_replace('{{Resps_Status}}', ResourcesTranslator::Resps_Status($lang), $html);
        $html = str_replace('{{Visas}}', ResourcesTranslator::Visas($lang), $html);
        $html = str_replace('{{Suivi}}', ResourcesTranslator::Suivi($lang), $html);
        $html = str_replace('{{States}}', ResourcesTranslator::States($lang), $html);
        $html = str_replace('{{Event_Types}}', ResourcesTranslator::Event_Types($lang), $html);
        
                $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("resources", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ResourcesTranslator::Resources($lang), $html);
        return $html;
    }
}
