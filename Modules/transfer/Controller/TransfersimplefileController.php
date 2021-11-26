<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/transfer/Model/TransferTranslator.php';
require_once 'Framework/Errors.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of transfer module
 */
class TransfersimplefileController extends CoresecureController {
    
    /**
     * User model object
     */
    private $providerModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function downloadAction() {
        throw new PfmException('Transfer disabled!!', 403);
    }

}
