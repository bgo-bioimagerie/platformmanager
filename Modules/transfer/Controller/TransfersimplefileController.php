<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/transfer/Model/TransferTranslator.php';

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
        
        $file = $this->request->getParameter("filetransferurl");
        if (file_exists($file)) {
            
            $fileNameArray = explode("/", $file);
            $fileName = $fileNameArray[ count($fileNameArray) -1];
            
            header("Content-Type: application/json");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Length: " . filesize("$file"));
            $fp = fopen("$file", "r");
            fpassthru($fp);
        } else {
            echo "file ".$file." does not exists";
        }
        
    }

}
