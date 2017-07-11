<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';
require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/antibodies/Model/Espece.php';
require_once 'Modules/antibodies/Model/Status.php';
require_once 'Modules/antibodies/Model/Organe.php';
require_once 'Modules/antibodies/Model/Prelevement.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';
require_once 'Modules/antibodies/Model/AcOwner.php';

require_once 'Modules/antibodies/Form/TissusForm.php';
require_once 'Modules/antibodies/Form/OwnerForm.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesController extends CoresecureController {

    private $antibody;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->antibody = new Anticorps();
        //$this->checkAuthorizationMenu("antibodies");
    }

    public function navbar($id_space) {
        $html = file_get_contents('Modules/antibodies/View/Antibodies/navbar.php');
        
        $html = str_replace('{{id_space}}', $id_space, $html);
        return $html;
    }

}
